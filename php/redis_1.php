<?php
/**
 * redis.php
 * Redis-backed login sessions (replaces native PHP file sessions).
 * Uses Predis (pure PHP client - no C extension required), so it works
 * on hosts that don't allow custom PHP extensions.
 *
 * Session record: key "session:{token}" -> user_id, with a sliding TTL.
 * The token itself is stored in an httpOnly cookie on the visitor's browser.
 */

require_once __DIR__ . '/config.php';

const SESSION_COOKIE_NAME  = 'nura_session';
const SESSION_TTL_SECONDS  = 60 * 60 * 24; // 24 hours

function getRedisClient(): \Predis\Client
{
    static $redis = null;
    if ($redis !== null) {
        return $redis;
    }

    try {
        $redis = new \Predis\Client([
            'scheme'   => env('REDIS_SCHEME', 'tls'), // 'tls' for cloud Redis, 'tcp' for local
            'host'     => env('REDIS_HOST', '127.0.0.1'),
            'port'     => (int) env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD', null) ?: null,
        ]);
        $redis->connect();
    } catch (\Exception $e) {
        error_log('Redis connection failed: ' . $e->getMessage());
        jsonResponse(['success' => false, 'message' => 'Session store unavailable. Please try again later.'], 500);
    }

    return $redis;
}

function isHttps(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

function createSession(int $userId): string
{
    $redis = getRedisClient();
    $token = bin2hex(random_bytes(32));
    $redis->setex("session:{$token}", SESSION_TTL_SECONDS, (string) $userId);

    setcookie(SESSION_COOKIE_NAME, $token, [
        'expires'  => time() + SESSION_TTL_SECONDS,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => isHttps(),
    ]);

    return $token;
}

function getAuthenticatedUserId(): ?int
{
    $token = $_COOKIE[SESSION_COOKIE_NAME] ?? null;
    if (!$token) {
        return null;
    }

    $redis  = getRedisClient();
    $userId = $redis->get("session:{$token}");
    if ($userId === null) {
        return null;
    }

    // Sliding expiration: staying active keeps the session alive.
    $redis->expire("session:{$token}", SESSION_TTL_SECONDS);
    return (int) $userId;
}

function destroySession(): void
{
    $token = $_COOKIE[SESSION_COOKIE_NAME] ?? null;
    if ($token) {
        getRedisClient()->del(["session:{$token}"]);
    }
    setcookie(SESSION_COOKIE_NAME, '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function requireAuth(): int
{
    $userId = getAuthenticatedUserId();
    if ($userId === null) {
        jsonResponse(['success' => false, 'message' => 'Not logged in'], 401);
    }
    return $userId;
}
