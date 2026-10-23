<?php
// Lightweight "am I signed in?" check. Always 200 so signed-out visits don't log 401 errors.
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/redis.php';

header('Content-Type: application/json');
echo json_encode(['success' => true, 'authenticated' => getAuthenticatedUserId() !== null]);
