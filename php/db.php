<?php
/**
 * db.php
 * MySQL connection (mysqli) for account credentials: users(id, username, email, password).
 * Set MYSQL_SSL=true for cloud MySQL providers that require TLS (Aiven, TiDB Cloud, ...).
 */

require_once __DIR__ . '/config.php';

$servername = env('MYSQL_HOST', 'localhost');
$dbUsername = env('MYSQL_USER', 'root');
$dbPassword = env('MYSQL_PASSWORD', '');
$database   = env('MYSQL_DATABASE', 'authentication');
$dbPort     = (int) env('MYSQL_PORT', 3306);
$useSsl     = in_array(strtolower((string) env('MYSQL_SSL', 'false')), ['1', 'true', 'yes'], true);

try {
    $conn = mysqli_init();
    $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 8);
    $flags = 0;
    if ($useSsl) {
        $conn->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, false);
        $flags = MYSQLI_CLIENT_SSL;
    }
    $conn->real_connect($servername, $dbUsername, $dbPassword, $database, $dbPort, null, $flags);
} catch (\Throwable $e) {
    error_log('MySQL connection failed: ' . $e->getMessage());
    jsonResponse(['success' => false, 'message' => 'Database unavailable. Please try again later.'], 500);
}
