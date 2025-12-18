<?php
include './include/db_connect.php';
session_start();

if(!isset($_SESSION['userid'])) {
    echo "login_required";
    exit;
}

if(!isset($_POST['product_id'])) {
    echo "error";
    exit;
}

$pid = $_POST['product_id'];
$uid = $_SESSION['userid'];
$mode = $_POST['mode'] ?? 'apply';

// Table Check (Create if not exists)
$conn->query("CREATE TABLE IF NOT EXISTS restock_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    userid VARCHAR(50) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_req (product_id, userid)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

if ($mode == 'cancel') {
    $stmt = $conn->prepare("DELETE FROM restock_notifications WHERE product_id = ? AND userid = ?");
    $stmt->bind_param("is", $pid, $uid);
    if($stmt->execute()) {
        echo "cancelled";
    } else {
        echo "error";
    }
} else {
    $stmt = $conn->prepare("INSERT INTO restock_notifications (product_id, userid) VALUES (?, ?)");
    $stmt->bind_param("is", $pid, $uid);
    
    try {
        if($stmt->execute()) {
            echo "success";
        } else {
            echo "duplicate";
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) { // Duplicate entry
            echo "duplicate";
        } else {
            echo "error";
        }
    }
}
?>
