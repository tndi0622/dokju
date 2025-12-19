<?php
include_once __DIR__ . '/env_loader.php';

$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pw = getenv('DB_PASS');
$dbName = getenv('DB_NAME');

$conn = new mysqli($host, $user, $pw, $dbName);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set Charset
$conn->set_charset("utf8mb4");
