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

// Get all community posts
$posts = $conn->query("SELECT p.*, u.name, u.nickname, 
                       (SELECT COUNT(*) FROM community_comments WHERE post_id = p.id) as comment_count
                       FROM community_posts p 
                       LEFT JOIN users u ON p.userid = u.userid 
                       ORDER BY p.created_at DESC");
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>커뮤니티 관리</title>
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
                    <li><a href="/dokju/admin/users.php"> 회원 관리</a></li>
                    <li><a href="/dokju/admin/orders.php"> 배송 관리</a></li>
                    <li><a href="/dokju/admin/posts.php" class="active"> 커뮤니티 관리</a></li>
                    <li><a href="/dokju/index.php" style="margin-top:20px; border-top:1px solid rgba(255,255,255,0.1); padding-top:20px;"> 사이트로 이동</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h2>커뮤니티 관리</h2>
                <div class="admin-user">
                    <span><?php echo htmlspecialchars($_SESSION['nickname'] ?? '관리자'); ?>님 환영합니다</span>
                    <a href="/dokju/logout.php" class="btn-logout">로그아웃</a>
                </div>
            </div>

            <div class="content-box">
                <h3>전체 게시글 목록</h3>
                
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>카테고리</th>
                            <th>제목</th>
                            <th>작성자</th>
                            <th>조회</th>
                            <th>추천</th>
                            <th>댓글</th>
                            <th>작성일</th>
                            <th>관리</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($post = $posts->fetch_assoc()): 
                            $cat_text = ['review'=>'리뷰', 'recommend'=>'추천', 'question'=>'질문'][$post['category']] ?? $post['category'];
                            $display_name = !empty($post['nickname']) ? $post['nickname'] : $post['name'];
                        ?>
                        <tr>
                            <td data-label="ID"><?php echo $post['id']; ?></td>
                            <td data-label="카테고리"><span class="badge badge-warning"><?php echo $cat_text; ?></span></td>
                            <td data-label="제목">
                                <div style="max-width: 400px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <a href="/dokju/community_view.php?id=<?php echo $post['id']; ?>" 
                                       target="_blank" 
                                       style="color:#333; text-decoration:none;" title="<?php echo htmlspecialchars($post['title']); ?>">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </a>
                                </div>
                            </td>
                            <td data-label="작성자"><?php echo htmlspecialchars($display_name); ?></td>
                            <td data-label="조회"><?php echo $post['views']; ?></td>
                            <td data-label="추천"><?php echo $post['likes']; ?></td>
                            <td data-label="댓글"><?php echo $post['comment_count']; ?></td>
                            <td data-label="작성일"><?php echo date('Y-m-d', strtotime($post['created_at'])); ?></td>
                            <td data-label="관리">
                                <a href="/dokju/admin/post_process.php?mode=delete&id=<?php echo $post['id']; ?>" 
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
