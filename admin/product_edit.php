<?php
session_start();
include '../include/db_connect.php';

// Check if admin
if (!isset($_SESSION['userid']) || $_SESSION['userid'] !== 'admin') {
    echo "<script>alert('관리자 권한이 필요합니다.'); location.href='/dokju/login.php';</script>";
    exit;
}

$id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    echo "<script>alert('상품을 찾을 수 없습니다.'); location.href='/dokju/admin/products.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>상품 수정 - 獨酒 Admin</title>
    <link rel="stylesheet" href="/dokju/css/admin.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <h1>獨酒 Admin</h1>
                <p>관리자 패널</p>
            </div>
            <nav class="admin-nav">
                <ul>
                    <li><a href="/dokju/admin/dashboard.php">📊 대시보드</a></li>
                    <li><a href="/dokju/admin/products.php" class="active">📦 상품 관리</a></li>
                    <li><a href="/dokju/admin/users.php">👥 회원 관리</a></li>
                    <li><a href="/dokju/admin/posts.php">💬 커뮤니티 관리</a></li>
                    <li><a href="/dokju/index.php" style="margin-top:20px; border-top:1px solid rgba(255,255,255,0.1); padding-top:20px;">🏠 사이트로 이동</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h2>상품 수정</h2>
                <div class="admin-user">
                    <span>관리자님 환영합니다</span>
                    <a href="/dokju/logout.php" class="btn-logout">로그아웃</a>
                </div>
            </div>

            <div class="content-box">
                <form class="admin-form" method="POST" action="/dokju/admin/product_process.php">
                    <input type="hidden" name="mode" value="edit">
                    <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                    
                    <div class="form-group">
                        <label>상품명 *</label>
                        <input type="text" name="product_name" class="form-control" value="<?php echo htmlspecialchars($product['product_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>지역</label>
                        <input type="text" name="region" class="form-control" value="<?php echo htmlspecialchars($product['region']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>종류</label>
                        <input type="text" name="type" class="form-control" value="<?php echo htmlspecialchars($product['type']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>정미율</label>
                        <input type="text" name="rice_polish" class="form-control" value="<?php echo htmlspecialchars($product['rice_polish']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>알코올 도수</label>
                        <input type="text" name="abv" class="form-control" value="<?php echo htmlspecialchars($product['abv']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>가격 *</label>
                        <input type="number" name="price" class="form-control" value="<?php echo $product['price']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>이미지 URL *</label>
                        <input type="text" name="image" class="form-control" value="<?php echo htmlspecialchars($product['image']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>뱃지 (쉼표로 구분)</label>
                        <input type="text" name="badges" class="form-control" value="<?php echo htmlspecialchars($product['badges']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>상품 설명</label>
                        <textarea name="description" class="form-control"><?php echo htmlspecialchars($product['description']); ?></textarea>
                    </div>
                    
                    <div style="margin-top:30px; display:flex; gap:10px;">
                        <a href="/dokju/admin/products.php" class="btn-primary" style="background:#999;">취소</a>
                        <button type="submit" class="btn-primary">수정</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
