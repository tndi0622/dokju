<?php
session_start();
include '../include/db_connect.php';

// Check if admin
if (!isset($_SESSION['userid']) || $_SESSION['userid'] !== 'admin') {
    echo "<script>alert('관리자 권한이 필요합니다.'); location.href='/dokju/login.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>상품 추가</title>
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
                <h2>상품 추가</h2>
                <div class="admin-user">
                    <span>관리자님 환영합니다</span>
                    <a href="/dokju/logout.php" class="btn-logout">로그아웃</a>
                </div>
            </div>

            <div class="content-box">
                <form class="admin-form" method="POST" action="/dokju/admin/product_process.php">
                    <input type="hidden" name="mode" value="add">
                    
                    <div class="form-group">
                        <label>상품명 *</label>
                        <input type="text" name="product_name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>지역</label>
                        <input type="text" name="region" class="form-control" placeholder="예: 니가타">
                    </div>
                    
                    <div class="form-group">
                        <label>종류</label>
                        <input type="text" name="type" class="form-control" placeholder="예: 준마이 다이긴조">
                    </div>
                    
                    <div class="form-group">
                        <label>정미율</label>
                        <input type="text" name="rice_polish" class="form-control" placeholder="예: 40%">
                    </div>
                    
                    <div class="form-group">
                        <label>알코올 도수</label>
                        <input type="text" name="abv" class="form-control" placeholder="예: 16%">
                    </div>
                    
                    <div class="form-group">
                        <label>가격 *</label>
                        <input type="number" name="price" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label>이미지 URL *</label>
                        <input type="text" name="image" class="form-control" placeholder="/dokju/images/..." required>
                    </div>
                    
                    <div class="form-group">
                        <label>뱃지 (쉼표로 구분)</label>
                        <input type="text" name="badges" class="form-control" placeholder="예: 베스트,신상품">
                    </div>
                    
                    <div class="form-group">
                        <label>상품 설명</label>
                        <textarea name="description" class="form-control"></textarea>
                    </div>
                    
                    <div style="margin-top:30px; display:flex; gap:10px;">
                        <a href="/dokju/admin/products.php" class="btn-primary" style="background:#999;">취소</a>
                        <button type="submit" class="btn-primary">추가</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>
