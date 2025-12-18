<?php
include './include/db_connect.php';
session_start();

if(!isset($_SESSION['userid'])) {
    echo json_encode(['success'=>false, 'message'=>'Login required']);
    exit;
}

$userid = $_SESSION['userid'];
$mode = $_POST['mode'] ?? '';

if($mode == 'delete_all') {
    $conn->query("DELETE FROM notifications WHERE userid='$userid'");
    echo json_encode(['success'=>true]);
} elseif($mode == 'delete' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $conn->query("DELETE FROM notifications WHERE id=$id AND userid='$userid'");
    echo json_encode(['success'=>true]);
} else {
    echo json_encode(['success'=>false]);
}
?>
