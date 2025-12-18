<?php
session_start();
include './include/db_connect.php';

header('Content-Type: application/json');

if(!isset($_SESSION['userid'])) {
    echo json_encode(['success'=>false, 'message'=>'로그인이 필요합니다.']);
    exit;
}

// Get JSON Input
$input = json_decode(file_get_contents('php://input'), true);

if(!$input) {
    echo json_encode(['success'=>false, 'message'=>'No data received']);
    exit;
}

$orderId = $input['orderId'];
$amount = $input['amount'];
$items = $input['items'];
$receiver = $input['receiver']; // array(name, phone, address)
$userid = $_SESSION['userid'];

// 1. Insert Order
$stmt = $conn->prepare("INSERT INTO orders (order_uid, userid, total_amount, status, receiver_name, receiver_phone, receiver_address) VALUES (?, ?, ?, 'PENDING', ?, ?, ?)");
$stmt->bind_param("ssisss", $orderId, $userid, $amount, $receiver['name'], $receiver['phone'], $receiver['address']);

if(!$stmt->execute()) {
    echo json_encode(['success'=>false, 'message'=>'주문 저장 실패: ' . $conn->error]);
    exit;
}

// 2. Insert Items
$stmt_item = $conn->prepare("INSERT INTO order_items (order_uid, product_id, product_name, price, qty, image) VALUES (?, ?, ?, ?, ?, ?)");

foreach($items as $item) {
    // Check if ID is set, otherwise default to 0
    $pid = isset($item['id']) ? $item['id'] : 0;
    
    $stmt_item->bind_param("sisiis", 
        $orderId, 
        $pid, 
        $item['name'], 
        $item['price'], 
        $item['qty'], 
        $item['image']
    );
    $stmt_item->execute();
    
    // 3. Decrease Stock
    $update_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
    $update_stock->bind_param("ii", $item['qty'], $pid);
    $update_stock->execute();
}

echo json_encode(['success'=>true]);
?>
