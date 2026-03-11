<?php
// 1. Force errors to show up (Fixes the "Blank Screen" mystery)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Setup Paths
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    die("Error: vendor/autoload.php not found. Run 'composer install' on AWS.");
}
require_once $autoloadPath;

$mysql = new mysqli("localhost", "root", "", "internship_db");
if ($mysql->connect_error) {
    die("MySQL Connection failed: " . $mysql->connect_error);
}

$message = ""; 
$msg_type = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'] ?? '';
    $pass = $_POST['password'] ?? '';

    if (!empty($user) && !empty($pass)) {
        // 3. Check MySQL for duplicates
        $check = $mysql->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $user);
        $check->execute();
        
        if ($check->get_result()->num_rows > 0) {
            $message = "User already exists!";
            $msg_type = "danger";
        } else {
            // 4. Save to MySQL (Hashed)
            $hashed_pass = password_hash($pass, PASSWORD_BCRYPT);
            $stmt = $mysql->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $user, $hashed_pass);

            if ($stmt->execute()) {
                // 5. Save to MongoDB
                try {
                    $manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");
                    $bulk = new MongoDB\Driver\BulkWrite;
                    $bulk->insert([
                        'username' => $user, 
                        'password' => $pass, // Matching your login logic
                        'age'      => 0, 
                        'dob'      => '', 
                        'contact'  => ''
                    ]);
                    $manager->executeBulkWrite('internship_db.profiles', $bulk);
                    
                    // Redirect to login after 2 seconds or show a link
                    $message = "Registration successful! <a href='../login.html' class='btn btn-sm btn-primary'>Login Now</a>";
                    $msg_type = "success";
                } catch (Exception $e) {
                    $message = "MongoDB Error: " . $e->getMessage();
                    $msg_type = "danger";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registration Status</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $msg_type; ?> shadow text-center">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                <div class="text-center">
                    <a href="../register.html" class="btn btn-secondary">Back to Register</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>