<?php
// 1. Force JSON header and prevent any stray HTML from breaking it
ob_clean(); 
header('Content-Type: application/json');

// 2. Correct path to vendor
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    echo json_encode(["status" => "error", "message" => "Autoload file missing"]);
    exit;
}
require_once $autoloadPath;

// 3. Get Input
$token   = $_POST['token'] ?? '';
$age     = $_POST['age'] ?? '';
$dob     = $_POST['dob'] ?? '';
$contact = $_POST['contact'] ?? '';

try {
    // REDIS SESSION CHECK
    $redis = new \Redis(); 
    $redis->connect('127.0.0.1', 6379);
    
    $username = $redis->get("session:" . $token);

    if (!$username) {
        echo json_encode(["status" => "error", "message" => "Session expired. Please login again."]);
        exit;
    }

    // MONGODB UPDATE
    $manager = new \MongoDB\Driver\Manager("mongodb://localhost:27017");
    $bulk    = new \MongoDB\Driver\BulkWrite;

    // We use $set to only update the profile fields without touching the password
    $bulk->update(
        ['username' => $username],
        ['$set' => [
            'age'     => $age, 
            'dob'     => $dob, 
            'contact' => $contact
        ]],
        ['upsert' => true]
    );

    $result = $manager->executeBulkWrite('internship_db.profiles', $bulk);
    
    echo json_encode([
        "status" => "success", 
        "message" => "Profile updated successfully for " . $username
    ]);

} catch (\Exception $e) {
    echo json_encode(["status" => "error", "message" => "Server Error: " . $e->getMessage()]);
}
?>