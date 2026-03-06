<?php
// Fix: Correct path to vendor folder
require_once __DIR__ . '/../vendor/autoload.php'; 

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$age = $_POST['age'] ?? '';
$dob = $_POST['dob'] ?? '';
$contact = $_POST['contact'] ?? '';
$message = "";

try {
    $redis = new Predis\Client();
    $username = $redis->get("session:$token");

    if (!$username) {
        header("Location: login.php");
        exit;
    }

    $manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $bulk = new MongoDB\Driver\BulkWrite;
        $bulk->update(
            ['username' => $username],
            ['$set' => ['age' => $age, 'dob' => $dob, 'contact' => $contact]],
            ['upsert' => true]
        );
        $manager->executeBulkWrite('internship_db.profiles', $bulk);
        $message = "Profile updated successfully!";
    }

} catch (Exception $e) {
    $message = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Profile</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f4; display: flex; justify-content: center; padding-top: 50px; }
        .card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); width: 400px; text-align: center; }
        input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
        <?php if($message) echo "<p style='color:green'>$message</p>"; ?>
        <form method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            <input type="number" name="age" placeholder="Age" required>
            <input type="date" name="dob" required>
            <input type="text" name="contact" placeholder="Contact" required>
            <button type="submit">Save Profile</button>
        </form>
        <br><a href="login.php">Logout</a>
    </div>
</body>
</html>