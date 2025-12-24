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

// Add stock column check
$check_col = $conn->query("SHOW COLUMNS FROM products LIKE 'stock'");
if($check_col->num_rows == 0) {
    $conn->query("ALTER TABLE products ADD COLUMN stock INT DEFAULT 50");
}

// Get all products
$products = $conn->query("SELECT * FROM products ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>상품 관리</title>
    <link rel="stylesheet" href="/dokju/css/admin.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <h1>Admin</h1>
                <p>관리자 패널</p>
            </div>
            <nav class="admin-nav">
                <ul>
                    <li><a href="/dokju/admin/dashboard.php"> 대시보드</a></li>
                    <li><a href="/dokju/admin/products.php" class="active"> 상품 관리</a></li>
                    <li><a href="/dokju/admin/users.php"> 회원 관리</a></li>
                    <li><a href="/dokju/admin/orders.php"> 배송 관리</a></li>
                    <li><a href="/dokju/admin/posts.php"> 커뮤니티 관리</a></li>
                    <li><a href="/dokju/index.php" style="margin-top:20px; border-top:1px solid rgba(255,255,255,0.1); padding-top:20px;"> 사이트로 이동</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h2>상품 관리</h2>
                <div class="admin-user">
                    <span><?php echo htmlspecialchars($_SESSION['nickname'] ?? '관리자'); ?>님 환영합니다</span>
                    <a href="/dokju/logout.php" class="btn-logout">로그아웃</a>
                </div>
            </div>

            <div class="content-box">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <h3 style="margin:0; border:none; padding:0;">전체 상품 목록</h3>
                    <a href="/dokju/admin/product_add.php" class="btn-primary">+ 상품 추가</a>
                </div>
                
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>이미지</th>
                            <th>상품명</th>
                            <th>지역</th>
                            <th>종류</th>
                            <th>재고</th>
                            <th>가격</th>
                            <th>관리</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($product = $products->fetch_assoc()): ?>
                        <tr>
                            <td data-label="ID"><?php echo $product['id']; ?></td>
                            <td data-label="이미지">
                                <img src="<?php echo $product['image']; ?>" 
                                     alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                                     style="width:50px; height:auto; border-radius:4px;">
                            </td>
                            <td data-label="상품명"><?php echo htmlspecialchars($product['product_name']); ?></td>
                            <td data-label="지역"><?php echo htmlspecialchars($product['region']); ?></td>
                            <td data-label="종류"><?php echo htmlspecialchars($product['type']); ?></td>
                            <td data-label="재고" style="<?php echo $product['stock'] <= 0 ? 'color:red; font-weight:bold;' : ''; ?>">
                                <?php echo number_format($product['stock']); ?>개
                                <?php if($product['stock'] <= 0) echo '(품절)'; ?>
                            </td>
                            <td data-label="가격"><?php echo number_format($product['price']); ?>원</td>
                            <td data-label="관리">
                                <a href="/dokju/admin/product_edit.php?id=<?php echo $product['id']; ?>" 
                                   class="btn-primary btn-sm btn-edit">수정</a>
                                <a href="/dokju/admin/product_process.php?mode=delete&id=<?php echo $product['id']; ?>" 
                                   class="btn-primary btn-sm btn-delete" 
                                   onclick="return confirm('정말 삭제하시겠습니까?')">삭제</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <!-- Floating Action Button -->
    <a href="/dokju/index.php" class="fab-site-link" title="사이트로 이동">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
    </a>
    <style>
        .fab-site-link {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: #2b2b2b;
            color: #fff;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            z-index: 9999;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .fab-site-link:hover {
            transform: translateY(-5px);
            background: #444;
            box-shadow: 0 6px 16px rgba(0,0,0,0.4);
        }
    </style>
</body>
</html>
