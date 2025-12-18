<?php
session_start();
include '../include/db_connect.php';

// Check if admin
if (!isset($_SESSION['userid']) || $_SESSION['userid'] !== 'admin') {
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
                    <span>관리자님 환영합니다</span>
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
                            <td><?php echo $post['id']; ?></td>
                            <td><span class="badge badge-warning"><?php echo $cat_text; ?></span></td>
                            <td>
                                <div style="max-width: 400px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <a href="/dokju/community_view.php?id=<?php echo $post['id']; ?>" 
                                       target="_blank" 
                                       style="color:#333; text-decoration:none;" title="<?php echo htmlspecialchars($post['title']); ?>">
                                        <?php echo htmlspecialchars($post['title']); ?>
                                    </a>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($display_name); ?></td>
                            <td><?php echo $post['views']; ?></td>
                            <td><?php echo $post['likes']; ?></td>
                            <td><?php echo $post['comment_count']; ?></td>
                            <td><?php echo date('Y-m-d', strtotime($post['created_at'])); ?></td>
                            <td>
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
</body>
</html>
