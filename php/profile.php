<?php
// 1. Force HTTP and set Headers
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    header("Location: http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit();
}

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');

ob_clean();

// 2. Setup Paths
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    echo json_encode(["status" => "error", "message" => "Autoload missing"]);
    exit;
}
require_once $autoloadPath;

// 3. Connect to Redis & MongoDB
try {
    $redis = new \Redis(); 
    $redis->connect('127.0.0.1', 6379);
    
    $manager = new \MongoDB\Driver\Manager("mongodb://localhost:27017");

    // Get token from either POST (saving) or GET (fetching)
    $token = $_REQUEST['token'] ?? '';

    if (empty($token)) {
        echo json_encode(["status" => "error", "message" => "Token is missing."]);
        exit;
    }

    $username = $redis->get("session:" . $token);

    if (!$username) {
        echo json_encode(["status" => "error", "message" => "Session expired. Please login again."]);
        exit;
    }

    // --- SCENARIO A: FETCH DATA (GET REQUEST) ---
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $query = new \MongoDB\Driver\Query(['username' => $username]);
        $cursor = $manager->executeQuery('internship_db.profiles', $query);
        $profile = current($cursor->toArray());

        if ($profile) {
            echo json_encode([
                "status" => "success",
                "data" => [
                    "age" => $profile->age ?? '',
                    "dob" => $profile->dob ?? '',
                    "contact" => $profile->contact ?? ''
                ]
            ]);
        } else {
            echo json_encode(["status" => "success", "data" => null]);
        }
        exit;
    }

    // --- SCENARIO B: SAVE DATA (POST REQUEST) ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $age     = $_POST['age'] ?? '';
        $dob     = $_POST['dob'] ?? '';
        $contact = $_POST['contact'] ?? '';

        $bulk = new \MongoDB\Driver\BulkWrite;
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
        exit;
    }

} catch (\Exception $e) {
    echo json_encode(["status" => "error", "message" => "Server Error: " . $e->getMessage()]);
}
?>