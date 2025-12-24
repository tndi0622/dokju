<?php
session_start();
include '../include/db_connect.php';

// Check if super admin
$is_super_admin = (isset($_SESSION['userid']) && $_SESSION['userid'] === 'admin') || (isset($_SESSION['role']) && $_SESSION['role'] === 'admin');

if (!$is_super_admin) {
    echo "<script>alert('최고 관리자 권한이 필요합니다.'); location.href='/dokju/login.php';</script>";
    exit;
}

$mode = $_GET['mode'] ?? '';

if ($mode === 'delete') {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo "<script>alert('회원이 삭제되었습니다.'); location.href='/dokju/admin/users.php';</script>";
    } else {
        echo "<script>alert('삭제 실패'); history.back();</script>";
    }
} elseif ($mode === 'promote') {
    $id = $_GET['id'];
    $stmt = $conn->prepare("UPDATE users SET role = 'manager' WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo "<script>alert('관리자로 지정되었습니다.'); location.href='/dokju/admin/users.php';</script>";
    } else {
        echo "<script>alert('처리 실패'); history.back();</script>";
    }
} elseif ($mode === 'demote') {
    $id = $_GET['id'];
    $stmt = $conn->prepare("UPDATE users SET role = 'user' WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo "<script>alert('관리자 권한이 해제되었습니다.'); location.href='/dokju/admin/users.php';</script>";
    } else {
        echo "<script>alert('처리 실패'); history.back();</script>";
    }
}
?>

