<?php
header('Content-Type: application/json'); // Fixes the "undefined" error
require '../vendor/autoload.php'; 

try {
    $redis = new Predis\Client();
    
    // Attempt MySQL connection, but don't let it crash the script if it fails
    $mysql = @new mysqli("localhost", "root", "", "internship_db");
    $userData = null;

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $user = $_POST['username'] ?? '';
        $pass = $_POST['password'] ?? '';

        // Try MySQL first if connection succeeded
        if ($mysql && !$mysql->connect_error) {
            $stmt = $mysql->prepare("SELECT password FROM users WHERE username = ?");
            $stmt->bind_param("s", $user);
            $stmt->execute();
            $res = $stmt->get_result();
            $userData = $res->fetch_assoc();
        }

        // If not found in MySQL (or MySQL failed), check MongoDB
        if (!$userData) {
            $mongoClient = new MongoDB\Client("mongodb://localhost:27017");
            $mongoDb = $mongoClient->internship_db; 
            $userData = $mongoDb->users->findOne(['username' => $user]);
        }

        if ($userData) {
            $dbPass = is_array($userData) ? $userData['password'] : $userData->password;
            if ($pass === $dbPass || password_verify($pass, $dbPass)) {
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