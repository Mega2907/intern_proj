<?php
header('Content-Type: application/json'); // Fixes the "undefined" error
require '../vendor/autoload.php'; 

try {
    // 1. Connect to all three services
    $redis = new Predis\Client();
    $mysql = new mysqli("localhost", "root", "", "internship_db");
    $mongoClient = new MongoDB\Client("mongodb://localhost:27017");
    $mongoDb = $mongoClient->internship_db; // Replace with your actual Mongo DB name

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $user = $_POST['username'] ?? '';
        $pass = $_POST['password'] ?? '';

        // 2. First, try finding the user in MySQL
        $stmt = $mysql->prepare("SELECT password FROM users WHERE username = ?");
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $res = $stmt->get_result();
        $userData = $res->fetch_assoc();

        // 3. If not in MySQL, try MongoDB
        if (!$userData) {
            $userData = $mongoDb->users->findOne(['username' => $user]);
        }

        if ($userData) {
            $dbPass = is_array($userData) ? $userData['password'] : $userData->password;
            
            if ($pass === $dbPass || password_verify($pass, $dbPass)) {
                // 4. Success! Create Redis Session
                $token = bin2hex(random_bytes(16)); 
                $redis->setex("session:$token", 3600, $user); 

                echo json_encode(["status" => "success", "token" => $token]);
                exit;
            }
        }
        echo json_encode(["status" => "error", "message" => "Invalid Credentials"]);
    }
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>