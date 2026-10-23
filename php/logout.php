<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/redis.php';

header('Content-Type: application/json');

destroySession();

echo json_encode(["success" => true, "message" => "Logged out"]);
