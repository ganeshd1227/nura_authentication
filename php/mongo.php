<?php
/**
 * mongo.php
 * MongoDB connection for the "profiles" collection: user_id, name, age, bio, interests.
 * Requires: the mongodb PECL extension + the mongodb/mongodb composer package (see README).
 */

require_once __DIR__ . '/config.php';

function getMongoDatabase(): \MongoDB\Database
{
    static $db = null;
    if ($db !== null) {
        return $db;
    }

    $uri    = env('MONGO_URI', 'mongodb://127.0.0.1:27017');
    $dbName = env('MONGO_DATABASE', 'authentication');

    try {
        // Fail fast (3s) if the MongoDB server isn't running, instead of hanging.
        $client = new \MongoDB\Client($uri, ['serverSelectionTimeoutMS' => 3000, 'connectTimeoutMS' => 3000]);
        $db     = $client->selectDatabase($dbName);
    } catch (\Exception $e) {
        error_log('MongoDB connection failed: ' . $e->getMessage());
        jsonResponse(['success' => false, 'message' => 'Profile store unavailable. Please try again later.'], 500);
    }

    return $db;
}

function getProfilesCollection(): \MongoDB\Collection
{
    return getMongoDatabase()->selectCollection('profiles');
}
