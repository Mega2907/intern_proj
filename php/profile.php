<?php
// 1. Allow other devices to connect and force JSON output
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

// Clear any accidental output/warnings
ob_clean();

// 2. Setup Paths
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    echo json_encode(["status" => "error", "message" => "Autoload missing"]);
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

    $bulk->update(
        ['username' => $username],
        ['$set' => [
            'age'     => $age, 
            'dob'     => $dob, 
            'contact' => $contact
        ]],
        ['upsert' => true]
    );

    $manager->executeBulkWrite('internship_db.profiles', $bulk);
    
    echo json_encode([
        "status" => "success", 
        "message" => "Profile updated successfully for " . $username
    ]);

} catch (\Exception $e) {
    echo json_encode(["status" => "error", "message" => "Server Error: " . $e->getMessage()]);
}
?>