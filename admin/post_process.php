<?php
session_start();
include '../include/db_connect.php';

// Check if admin
if (!isset($_SESSION['userid']) || $_SESSION['userid'] !== 'admin') {
    echo "<script>alert('관리자 권한이 필요합니다.'); location.href='/dokju/login.php';</script>";
    exit;
}

$mode = $_GET['mode'] ?? '';

if ($mode === 'delete') {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM community_posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo "<script>alert('게시글이 삭제되었습니다.'); location.href='/dokju/admin/posts.php';</script>";
    } else {
        echo "<script>alert('삭제 실패'); history.back();</script>";
    }
}
?>
