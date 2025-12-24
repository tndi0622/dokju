<?php
session_start();
include '../include/db_connect.php';

// Check if admin or manager
$is_admin = (isset($_SESSION['userid']) && $_SESSION['userid'] === 'admin');
$is_manager = (isset($_SESSION['role']) && ($_SESSION['role'] === 'manager' || $_SESSION['role'] === 'admin'));

if (!$is_admin && !$is_manager) {
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
    $stock = $_POST['stock'];
    $image = $_POST['image'];
    $badges = $_POST['badges'] ?? '';
    $description = $_POST['description'];
    
    $stmt = $conn->prepare("INSERT INTO products (product_name, region, type, rice_polish, abv, price, stock, image, badges, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssiisss", $name, $region, $type, $rice_polish, $abv, $price, $stock, $image, $badges, $description);
    
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
    $stock = $_POST['stock'];
    $image = $_POST['image'];
    $badges = $_POST['badges'] ?? '';
    $description = $_POST['description'];
    
    // Get Old Stock
    $old_res = $conn->query("SELECT stock, product_name FROM products WHERE id=$id");
    $old_stock = 0;
    $p_name = "";
    if($old_res && $row = $old_res->fetch_assoc()) {
        $old_stock = $row['stock'];
        $p_name = $row['product_name'];
    }

    $stmt = $conn->prepare("UPDATE products SET product_name=?, region=?, type=?, rice_polish=?, abv=?, price=?, stock=?, image=?, badges=?, description=? WHERE id=?");
    $stmt->bind_param("sssssiisssi", $name, $region, $type, $rice_polish, $abv, $price, $stock, $image, $badges, $description, $id);
    
    if ($stmt->execute()) {
        // Check Restock
        if ($old_stock <= 0 && $stock > 0) {
            // Check Table Exists
            $conn->query("CREATE TABLE IF NOT EXISTS restock_notifications (product_id INT, userid VARCHAR(50))");
            
            $req_res = $conn->query("SELECT userid FROM restock_notifications WHERE product_id=$id");
            if($req_res) {
                while($req = $req_res->fetch_assoc()) {
                    $uid = $req['userid'];
                    $msg = "신청하신 상품 [". $p_name ."]이(가) 재입고되었습니다!";
                    $link = "/dokju/product_view.php?id=$id";
                    
                    // Insert Notification
                    $conn->query("CREATE TABLE IF NOT EXISTS notifications (id INT AUTO_INCREMENT PRIMARY KEY, userid VARCHAR(50), message TEXT, link VARCHAR(255), is_read TINYINT(1) DEFAULT 0, created_at DATETIME DEFAULT CURRENT_TIMESTAMP)");
                    $noti = $conn->prepare("INSERT INTO notifications (userid, message, link) VALUES (?, ?, ?)");
                    $noti->bind_param("sss", $uid, $msg, $link);
                    $noti->execute();
                }
                // Clear Requests
                $conn->query("DELETE FROM restock_notifications WHERE product_id=$id");
            }
        }
        
        echo "<script>alert('상품이 수정되었습니다.'); location.href='/dokju/admin/products.php';</script>";
    } else {
        echo "<script>alert('수정 실패'); history.back();</script>";
    }
}
?>
