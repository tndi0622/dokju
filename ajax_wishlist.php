<?php
session_start();
include './include/db_connect.php';

// Response Array
$response = ['success' => false, 'message' => '', 'action' => ''];

if(!isset($_SESSION['userid'])) {
    $response['message'] = '로그인이 필요합니다.';
    echo json_encode($response);
    exit;
}

$userid = $_SESSION['userid'];
$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

if($product_id == 0) {
    $response['message'] = '상품 정보가 올바르지 않습니다.';
    echo json_encode($response);
    exit;
}

// Get User ID (PK) from users table using userid string
$u_stmt = $conn->prepare("SELECT id FROM users WHERE userid = ?");
$u_stmt->bind_param("s", $userid);
$u_stmt->execute();
$u_res = $u_stmt->get_result();
$u_row = $u_res->fetch_assoc();
$user_pk = $u_row['id'];

// Check if already wished
$check = $conn->prepare("SELECT id FROM wishlists WHERE user_id = ? AND product_id = ?");
$check->bind_param("ii", $user_pk, $product_id);
$check->execute();
$check_res = $check->get_result();

if($check_res->num_rows > 0) {
    // Delete (Toggle Off)
    $del = $conn->prepare("DELETE FROM wishlists WHERE user_id = ? AND product_id = ?");
    $del->bind_param("ii", $user_pk, $product_id);
    if($del->execute()) {
        $response['success'] = true;
        $response['message'] = '찜 목록에서 삭제되었습니다.';
        $response['action'] = 'deleted';
    }
} else {
    // Insert (Toggle On)
    $ins = $conn->prepare("INSERT INTO wishlists (user_id, product_id) VALUES (?, ?)");
    $ins->bind_param("ii", $user_pk, $product_id);
    if($ins->execute()) {
        $response['success'] = true;
        $response['message'] = '찜 목록에 추가되었습니다.';
        $response['action'] = 'added';
    }
}

echo json_encode($response);
?>
