<?php
session_start();
include '../include/db_connect.php';

// Check if admin
if (!isset($_SESSION['userid']) || $_SESSION['userid'] !== 'admin') {
    echo "<script>alert('관리자 권한이 필요합니다.'); location.href='/dokju/login.php';</script>";
    exit;
}

// Get statistics
$total_users = $conn->query("SELECT COUNT(*) as cnt FROM users WHERE userid != 'admin'")->fetch_assoc()['cnt'];
$total_products = $conn->query("SELECT COUNT(*) as cnt FROM products")->fetch_assoc()['cnt'];
$total_posts = $conn->query("SELECT COUNT(*) as cnt FROM community_posts")->fetch_assoc()['cnt'];
$total_comments = $conn->query("SELECT COUNT(*) as cnt FROM community_comments")->fetch_assoc()['cnt'];

// Recent users
$recent_users = $conn->query("SELECT userid, name, created_at FROM users WHERE userid != 'admin' ORDER BY created_at DESC LIMIT 5");

// Recent posts
$recent_posts = $conn->query("SELECT p.id, p.title, p.category, p.created_at, u.name 
                               FROM community_posts p 
                               LEFT JOIN users u ON p.userid = u.userid 
                               ORDER BY p.created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>관리자 대시보드</title>
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
                    <li><a href="/dokju/admin/dashboard.php" class="active"> 대시보드</a></li>
                    <li><a href="/dokju/admin/products.php"> 상품 관리</a></li>
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
                <h2>대시보드</h2>
                <div class="admin-user">
                    <span>관리자님 환영합니다</span>
                    <a href="/dokju/logout.php" class="btn-logout">로그아웃</a>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="dashboard-cards">
                <div class="stat-card orange">
                    <div class="stat-label">전체 회원</div>
                    <div class="stat-value"><?php echo number_format($total_users); ?></div>
                </div>
                <div class="stat-card blue">
                    <div class="stat-label">전체 상품</div>
                    <div class="stat-value"><?php echo number_format($total_products); ?></div>
                </div>
                <div class="stat-card green">
                    <div class="stat-label">커뮤니티 글</div>
                    <div class="stat-value"><?php echo number_format($total_posts); ?></div>
                </div>
                <div class="stat-card purple">
                    <div class="stat-label">전체 댓글</div>
                    <div class="stat-value"><?php echo number_format($total_comments); ?></div>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="content-box">
                <h3>최근 가입 회원</h3>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>아이디</th>
                            <th>이름</th>
                            <th>가입일</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($user = $recent_users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['userid']); ?></td>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Recent Posts -->
            <div class="content-box">
                <h3>최근 커뮤니티 글</h3>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>카테고리</th>
                            <th>제목</th>
                            <th>작성자</th>
                            <th>작성일</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($post = $recent_posts->fetch_assoc()): 
                            $cat_text = ['review'=>'리뷰', 'recommend'=>'추천', 'question'=>'질문', 'free'=>'자유'][$post['category']] ?? $post['category'];
                        ?>
                        <tr>
                            <td><span class="badge badge-warning"><?php echo $cat_text; ?></span></td>
                            <td>
                                <div style="max-width: 400px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?php echo htmlspecialchars($post['title']); ?>">
                                    <?php echo htmlspecialchars($post['title']); ?>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($post['name']); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($post['created_at'])); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
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
