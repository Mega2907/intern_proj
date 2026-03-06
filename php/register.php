<?php
// 1. Critical: Adjust path to find your vendor folder
$autoloadPath = __DIR__ . '/../vendor/autoload.php';

if (!file_exists($autoloadPath)) {
    die("Error: Cannot find vendor/autoload.php. Check your folder structure!");
}
require_once $autoloadPath;

// 2. Database Connections
$mysql = new mysqli("localhost", "root", "", "internship_db");
if ($mysql->connect_error) { die("MySQL Connection failed: " . $mysql->connect_error); }

$message = "";
$msg_type = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if (!empty($user) && !empty($pass)) {
        // Prevent Duplicate Entry Crash
        $check = $mysql->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $user);
        $check->execute();
        
        if ($check->get_result()->num_rows > 0) {
            $message = "User '$user' already exists in MySQL!";
            $msg_type = "error";
        } else {
            // Save to MySQL
            $hashed_pass = password_hash($pass, PASSWORD_BCRYPT);
            $stmt = $mysql->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $user, $hashed_pass);

            if ($stmt->execute()) {
                // Now Save to MongoDB
                try {
                    $manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");
                    $bulk = new MongoDB\Driver\BulkWrite;
                    $bulk->insert(['username' => $user, 'age' => 0, 'dob' => '', 'contact' => '']);
                    $manager->executeBulkWrite('internship_db.profiles', $bulk);
                    
                    $message = "Registered successfully in MySQL & MongoDB! <a href='login.php'>Login</a>";
                    $msg_type = "success";
                } catch (Exception $e) {
                    $message = "MySQL Success, but MongoDB Failed: " . $e->getMessage();
                    $msg_type = "error";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <style>
        body { background: #f4f7f6; font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 320px; text-align: center; }
        input { width: 90%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; }
        button { width: 98%; padding: 10px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .success { color: green; } .error { color: red; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Create Account</h2>
        <p class="<?php echo $msg_type; ?>"><?php echo $message; ?></p>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>