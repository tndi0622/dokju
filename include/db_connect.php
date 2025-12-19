<?php
include_once __DIR__ . '/env_loader.php';

$is_local = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']);

if ($is_local) {
    // Local Environment Configuration (defaults for XAMPP if env vars not set)
    $host = getenv('DB_HOST_LOCAL') ?: 'localhost';
    $user = getenv('DB_USER_LOCAL') ?: 'root';
    $pw = getenv('DB_PASS_LOCAL') ?: '';
    $dbName = getenv('DB_NAME_LOCAL') ?: 'dokju'; // Default to 'dokju' or use production name
} else {
    // Production Environment Configuration
    $host = getenv('DB_HOST');
    $user = getenv('DB_USER');
    $pw = getenv('DB_PASS');
    $dbName = getenv('DB_NAME');
}

$conn = new mysqli($host, $user, $pw, $dbName);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set Charset
$conn->set_charset("utf8mb4");
