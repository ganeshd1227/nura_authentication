<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;
use MongoDB\Client;

try {
    $dotenv = Dotenv::createImmutable(__DIR__);
    $dotenv->load();

    $uri = $_ENV['MONGO_URI'] ?? null;

    if (!$uri) {
        throw new RuntimeException('MONGO_URI is missing.');
    }

    echo "1. MONGO_URI loaded successfully.<br>";

    $client = new Client($uri, [], [
        'serverSelectionTimeoutMS' => 10000,
    ]);

    echo "2. MongoDB client created.<br>";

    $result = $client->selectDatabase('admin')->command([
        'ping' => 1
    ]);

    echo "3. MongoDB ping successful!<br>";

    echo "<strong>MongoDB connection successful!</strong>";

} catch (Throwable $e) {

    echo "<h3>MongoDB connection failed</h3>";

    echo "<strong>Error:</strong><br>";
    echo htmlspecialchars($e->getMessage());

    echo "<br><br>";

    echo "<strong>Exception:</strong><br>";
    echo get_class($e);
}