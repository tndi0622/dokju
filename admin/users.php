<?php
session_start();
include '../include/db_connect.php';

// Check if admin
if (!isset($_SESSION['userid']) || $_SESSION['userid'] !== 'admin') {
    echo "<script>alert('관리자 권한이 필요합니다.'); location.href='/dokju/login.php';</script>";
    exit;
}

// Get all users except admin
$users = $conn->query("SELECT * FROM users WHERE userid != 'admin' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원 관리</title>
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
                    <li><a href="/dokju/admin/products.php"> 상품 관리</a></li>
                    <li><a href="/dokju/admin/users.php" class="active"> 회원 관리</a></li>
                    <li><a href="/dokju/admin/orders.php"> 배송 관리</a></li>
                    <li><a href="/dokju/admin/posts.php"> 커뮤니티 관리</a></li>
                    <li><a href="/dokju/index.php" style="margin-top:20px; border-top:1px solid rgba(255,255,255,0.1); padding-top:20px;"> 사이트로 이동</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h2>회원 관리</h2>
                <div class="admin-user">
                    <span>관리자님 환영합니다</span>
                    <a href="/dokju/logout.php" class="btn-logout">로그아웃</a>
                </div>
            </div>

            <div class="content-box">
                <h3>전체 회원 목록</h3>
                
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>아이디</th>
                            <th>이름</th>
                            <th>닉네임</th>
                            <th>전화번호</th>
                            <th>소셜타입</th>
                            <th>가입일</th>
                            <th>관리</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($user = $users->fetch_assoc()): ?>
                        <tr>
                            <td data-label="ID"><?php echo $user['id']; ?></td>
                            <td data-label="아이디"><?php echo htmlspecialchars($user['userid']); ?></td>
                            <td data-label="이름"><?php echo htmlspecialchars($user['name']); ?></td>
                            <td data-label="닉네임"><?php echo htmlspecialchars($user['nickname'] ?? '-'); ?></td>
                            <td data-label="전화번호"><?php echo htmlspecialchars($user['phone'] ?? '-'); ?></td>
                            <td data-label="소셜타입">
                                <?php 
                                $social = $user['social_type'] ?? 'none';
                                if ($social === 'none' || empty($social)) {
                                    echo '<span class="badge badge-success">일반</span>';
                                } elseif ($social === 'kakao') {
                                    echo '<span class="badge badge-warning">카카오</span>';
                                } elseif ($social === 'naver') {
                                    echo '<span class="badge" style="background:#e8f5e9; color:#2e7d32;">네이버</span>';
                                }
                                ?>
                            </td>
                            <td data-label="가입일"><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                            <td data-label="관리">
                                <a href="/dokju/admin/user_process.php?mode=delete&id=<?php echo $user['id']; ?>" 
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
