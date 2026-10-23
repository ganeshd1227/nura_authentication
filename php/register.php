<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/mongo.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Only POST allowed"]);
    exit();
}

$username = trim($_POST["username"] ?? "");
$email    = trim($_POST["email"] ?? "");
$password = $_POST["password"] ?? "";

// Optional profile fields, stored in MongoDB
$name      = sanitizeString($_POST["name"] ?? "");
$age       = isset($_POST["age"]) && $_POST["age"] !== "" ? (int) $_POST["age"] : null;
$bio       = sanitizeString($_POST["bio"] ?? "");
$interests = array_values(array_filter(array_map('trim', explode(',', $_POST["interests"] ?? ''))));

if ($username === "" || strlen($username) < 3) {
    echo json_encode(["success" => false, "message" => "Username must be at least 3 characters"]);
    exit();
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Invalid email address"]);
    exit();
}
if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
    echo json_encode(["success" => false, "message" => "Password must be 8+ characters with an uppercase letter and a number"]);
    exit();
}
if ($age !== null && ($age < 13 || $age > 120)) {
    echo json_encode(["success" => false, "message" => "Age must be between 13 and 120"]);
    exit();
}

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
$stmt->bind_param("ss", $email, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Email or username already registered"]);
    exit();
}
$stmt->close();

$hashedPassword = password_hash($password, PASSWORD_BCRYPT, ["cost" => 12]);

$stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $username, $email, $hashedPassword);

if (!$stmt->execute()) {
    echo json_encode(["success" => false, "message" => "Registration failed: " . $stmt->error]);
    $stmt->close();
    $conn->close();
    exit();
}

$userId = $conn->insert_id;
$stmt->close();
$conn->close();

// Store the extended profile in MongoDB, keyed by the MySQL user id.
try {
    getProfilesCollection()->insertOne([
        'user_id'    => $userId,
        'name'       => $name,
        'age'        => $age,
        'bio'        => $bio,
        'interests'  => $interests,
        'created_at' => new \MongoDB\BSON\UTCDateTime(),
    ]);
} catch (\Exception $e) {
    // The account was created successfully; a profile-store hiccup shouldn't
    // block registration, but it is logged for follow-up.
    error_log('Failed to create Mongo profile for user ' . $userId . ': ' . $e->getMessage());
}

echo json_encode(["success" => true, "message" => "Registration successful"]);
