<?php
include_once __DIR__ . '/env_loader.php';

$is_local = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']);

// Security: Error Reporting
if ($is_local) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0); // or E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT if logging is set up
}

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
