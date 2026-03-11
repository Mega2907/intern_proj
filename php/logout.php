<?php
header("Access-Control-Allow-Origin: *");
require_once __DIR__ . '/../vendor/autoload.php';

$token = $_GET['token'] ?? '';

try {
    $redis = new \Redis();
    $redis->connect('127.0.0.1', 6379);

    if (!empty($token)) {
        $redis->del("session:" . $token);
    }

    // Clean redirection to the main login page
    header("Location: ../login.html");
    exit;
} catch (Exception $e) {
    die("Logout Failed: " . $e->getMessage());
}
?>