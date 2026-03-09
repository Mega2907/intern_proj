<?php
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
require_once $autoloadPath;

$mysql = new mysqli("localhost", "root", "", "internship_db");
$message = ""; $msg_type = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if (!empty($user) && !empty($pass)) {
        // 1. Check MySQL for duplicates
        $check = $mysql->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $user);
        $check->execute();
        
        if ($check->get_result()->num_rows > 0) {
            $message = "User already exists!";
            $msg_type = "error";
        } else {
            // 2. Save to MySQL
            $hashed_pass = password_hash($pass, PASSWORD_BCRYPT);
            $stmt = $mysql->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $user, $hashed_pass);

            if ($stmt->execute()) {
                // 3. Save to MongoDB (CRITICAL: Added 'password' field)
                try {
                    $manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");
                    $bulk = new MongoDB\Driver\BulkWrite;
                    $bulk->insert([
                        'username' => $user, 
                        'password' => $pass, // Saving plain text to match your login logic
                        'age' => 0, 
                        'dob' => '', 
                        'contact' => ''
                    ]);
                    $manager->executeBulkWrite('internship_db.profiles', $bulk);
                    
                    $message = "Registered! <a href='../login.html'>Go to Login</a>";
                    $msg_type = "success";
                } catch (Exception $e) {
                    $message = "MongoDB Error: " . $e->getMessage();
                    $msg_type = "error";
                }
            }
        }
    }
}
?>