<?php
session_start();
include '../include/db_connect.php';

// Check if admin
if (!isset($_SESSION['userid']) || $_SESSION['userid'] !== 'admin') {
    echo "<script>alert('관리자 권한이 필요합니다.'); location.href='/dokju/login.php';</script>";
    exit;
}

$mode = $_REQUEST['mode'] ?? '';

if ($mode === 'delete') {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo "<script>alert('상품이 삭제되었습니다.'); location.href='/dokju/admin/products.php';</script>";
    } else {
        echo "<script>alert('삭제 실패'); history.back();</script>";
    }
} elseif ($mode === 'add') {
    // Add product
    $name = $_POST['product_name'];
    $region = $_POST['region'];
    $type = $_POST['type'];
    $rice_polish = $_POST['rice_polish'];
    $abv = $_POST['abv'];
    $price = $_POST['price'];
    $image = $_POST['image'];
    $badges = $_POST['badges'] ?? '';
    $description = $_POST['description'];
    
    $stmt = $conn->prepare("INSERT INTO products (product_name, region, type, rice_polish, abv, price, image, badges, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssisss", $name, $region, $type, $rice_polish, $abv, $price, $image, $badges, $description);
    
    if ($stmt->execute()) {
        echo "<script>alert('상품이 추가되었습니다.'); location.href='/dokju/admin/products.php';</script>";
    } else {
        echo "<script>alert('추가 실패'); history.back();</script>";
    }
} elseif ($mode === 'edit') {
    // Edit product
    $id = $_POST['id'];
    $name = $_POST['product_name'];
    $region = $_POST['region'];
    $type = $_POST['type'];
    $rice_polish = $_POST['rice_polish'];
    $abv = $_POST['abv'];
    $price = $_POST['price'];
    $image = $_POST['image'];
    $badges = $_POST['badges'] ?? '';
    $description = $_POST['description'];
    
    $stmt = $conn->prepare("UPDATE products SET product_name=?, region=?, type=?, rice_polish=?, abv=?, price=?, image=?, badges=?, description=? WHERE id=?");
    $stmt->bind_param("sssssisssi", $name, $region, $type, $rice_polish, $abv, $price, $image, $badges, $description, $id);
    
    if ($stmt->execute()) {
        echo "<script>alert('상품이 수정되었습니다.'); location.href='/dokju/admin/products.php';</script>";
    } else {
        echo "<script>alert('수정 실패'); history.back();</script>";
    }
}
?>
