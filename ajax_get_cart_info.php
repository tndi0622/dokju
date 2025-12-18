<?php
include './include/db_connect.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$ids = $input['ids'] ?? [];

if(empty($ids)) {
    echo json_encode(['items' => []]);
    exit;
}

// Sanitize (integers)
$ids = array_map('intval', $ids);
$ids_str = implode(',', $ids);

$sql = "SELECT id, product_name, price, image, stock FROM products WHERE id IN ($ids_str)";
$res = $conn->query($sql);

$items = [];
while($row = $res->fetch_assoc()) {
    $items[$row['id']] = $row;
}
echo json_encode(['items' => $items]);
?>
