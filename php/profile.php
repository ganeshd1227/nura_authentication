<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/mongo.php';
require_once __DIR__ . '/redis.php';

header("Content-Type: application/json");

$userId = requireAuth();

if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $stmt = $conn->prepare("SELECT id, username, email, created_at FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "User not found"]);
        exit();
    }

    $user = $result->fetch_assoc();
    $stmt->close();
    $conn->close();

    try {
        $profileDoc = getProfilesCollection()->findOne(['user_id' => $userId]);
    } catch (\Exception $e) {
        error_log('Mongo read failed: ' . $e->getMessage());
        jsonResponse(['success' => false, 'message' => 'Profile store unavailable. Is MongoDB running?'], 500);
    }
    $profile = [
        "name"      => $profileDoc['name'] ?? '',
        "age"       => $profileDoc['age'] ?? null,
        "bio"       => $profileDoc['bio'] ?? '',
        "interests" => isset($profileDoc['interests']) ? array_values((array) $profileDoc['interests']) : [],
    ];

    echo json_encode([
        "success" => true,
        "user" => [
            "id"         => (int) $user["id"],
            "username"   => $user["username"],
            "email"      => $user["email"],
            "created_at" => $user["created_at"],
        ],
        "profile" => $profile,
    ]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name      = sanitizeString($_POST["name"] ?? "");
    $age       = isset($_POST["age"]) && $_POST["age"] !== "" ? (int) $_POST["age"] : null;
    $bio       = sanitizeString($_POST["bio"] ?? "");
    $interests = array_values(array_filter(array_map('trim', explode(',', $_POST["interests"] ?? ''))));

    if ($age !== null && ($age < 13 || $age > 120)) {
        echo json_encode(["success" => false, "message" => "Age must be between 13 and 120"]);
        exit();
    }

    try {
    getProfilesCollection()->updateOne(
        ['user_id' => $userId],
        ['$set' => [
            'name'       => $name,
            'age'        => $age,
            'bio'        => $bio,
            'interests'  => $interests,
            'updated_at' => new \MongoDB\BSON\UTCDateTime(),
        ]],
        ['upsert' => true]
    );
    } catch (\Exception $e) {
        error_log('Mongo write failed: ' . $e->getMessage());
        jsonResponse(['success' => false, 'message' => 'Profile store unavailable. Is MongoDB running?'], 500);
    }

    echo json_encode(["success" => true, "message" => "Profile updated successfully"]);
    exit();
}

http_response_code(405);
echo json_encode(["success" => false, "message" => "Method not allowed"]);
