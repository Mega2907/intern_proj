<?php
// Force JSON header to prevent the "undefined" error
header('Content-Type: application/json');

require_once __DIR__ . '/../vendor/autoload.php';

use MongoDB\Driver\Manager;
use MongoDB\Driver\BulkWrite;

$token = $_POST['token'] ?? '';
$age = $_POST['age'] ?? '';
$dob = $_POST['dob'] ?? '';
$contact = $_POST['contact'] ?? '';

try {
    $redis = new \Redis(); // Added backslash to help VS Code
    $redis->connect('127.0.0.1', 6379);
    $username = $redis->get("session:" . $token);

    if (!$username) {
        echo json_encode(["status" => "error", "message" => "Session expired"]);
        exit;
    }

    $manager = new \MongoDB\Driver\Manager("mongodb://localhost:27017");
    $bulk = new \MongoDB\Driver\BulkWrite;

    $bulk->update(
        ['username' => $username],
        ['$set' => ['age' => $age, 'dob' => $dob, 'contact' => $contact]],
        ['upsert' => true]
    );

    $manager->executeBulkWrite('internship_db.profiles', $bulk);
    echo json_encode(["status" => "success", "message" => "Profile updated for " . $username]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}