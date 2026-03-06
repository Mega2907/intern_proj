<?php
// 1. Load dependencies and start services
require '../vendor/autoload.php'; 

try {
    $redis = new Predis\Client();
    $mysql = new mysqli("localhost", "root", "", "internship_db");

    if ($mysql->connect_error) {
        die("Connection failed: " . $mysql->connect_error);
    }

    $error = "";

    // 2. Handle the Login Request
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $user = $_POST['username'] ?? '';
        $pass = $_POST['password'] ?? '';

        $stmt = $mysql->prepare("SELECT password FROM users WHERE username = ?");
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Check for plain text OR hashed passwords to support all your test users
            if ($pass === $row['password'] || password_verify($pass, $row['password'])) {
                
                // 3. Create Redis Session
                $token = bin2hex(random_bytes(16)); 
                $redis->setex("session:$token", 3600, $user); 

                // 4. Redirect to Profile
                header("Location: profile.php?token=$token");
                exit;
            } else {
                $error = "Invalid Password!";
            }
        } else {
            $error = "User not found!";
        }
    }
} catch (Exception $e) {
    $error = "Server Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Page</title>
    <style>
        /* 5. Clean UI Design */
        body { 
            background-color: #f4f4f4; 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 15px rgba(0,0,0,0.1);
            width: 350px;
            text-align: center;
        }
        input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        button:hover { background-color: #0056b3; }
        .error-msg { color: #f02849; margin-bottom: 10px; font-size: 14px; }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login</h2>
        
        <?php if($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>