<?php
session_start();
include './include/db_connect.php';

// Get filters
$category = $_GET['category'] ?? 'all';
$sort = $_GET['sort'] ?? 'latest';
$page_no = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 5;
$offset = ($page_no - 1) * $per_page;

// Build query
$where = ($category !== 'all') ? "WHERE category = '$category'" : "";

// Order by
$order_map = [
    'latest' => 'created_at DESC',
    'likes' => 'likes DESC, created_at DESC',
    'views' => 'views DESC, created_at DESC'
];
$order = $order_map[$sort] ?? 'created_at DESC';

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM community_posts $where";
$total_result = $conn->query($count_sql);
$total_posts = $total_result->fetch_assoc()['total'];
$total_pages = (int)ceil($total_posts / $per_page);

// Get posts
$sql = "SELECT p.*, u.name, u.nickname 
        FROM community_posts p 
        LEFT JOIN users u ON p.userid = u.userid 
        $where 
        ORDER BY $order
        LIMIT $per_page OFFSET $offset";

$result = $conn->query($sql);

include './include/header.php';
?>
<link rel="stylesheet" href="/dokju/css/community.css?v=<?php echo time(); ?>">

<main class="community-container">
  <div class="community-header">
    <h1 class="community-title">COMMUNITY</h1>
    <p class="community-desc">독주 커뮤니티에서 다양한 이야기를 나눠보세요</p>
  </div>

  <!-- Navigation Bar (Tabs + Controls) -->
  <div class="community-nav">
    <!-- Left: Tabs -->
    <div class="nav-tabs">
      <a href="?category=all&sort=<?php echo $sort; ?>" class="<?php echo ($category === 'all') ? 'active' : ''; ?>">전체</a>
      <a href="?category=free&sort=<?php echo $sort; ?>" class="<?php echo ($category === 'free') ? 'active' : ''; ?>">자유</a>
      <a href="?category=review&sort=<?php echo $sort; ?>" class="<?php echo ($category === 'review') ? 'active' : ''; ?>">리뷰</a>
      <a href="?category=recommend&sort=<?php echo $sort; ?>" class="<?php echo ($category === 'recommend') ? 'active' : ''; ?>">추천</a>
      <a href="?category=question&sort=<?php echo $sort; ?>" class="<?php echo ($category === 'question') ? 'active' : ''; ?>">질문</a>
    </div>

    <!-- Right: Controls -->
    <div class="nav-controls">
      <div class="sort-select-wrapper">
        <select onchange="location.href='?category=<?php echo $category; ?>&sort='+this.value">
           <option value="latest" <?php echo ($sort === 'latest') ? 'selected' : ''; ?>>최신순</option>
           <option value="likes" <?php echo ($sort === 'likes') ? 'selected' : ''; ?>>추천순</option>
           <option value="views" <?php echo ($sort === 'views') ? 'selected' : ''; ?>>조회순</option>
        </select>
      </div>
      <a href="/dokju/community_write.php" class="btn-write">글쓰기</a>
    </div>
  </div>
  
  <div style="border-bottom: 1px solid #ddd; margin-bottom: 30px;"></div>

  <!-- Post List -->
  <div class="post-list">
    <?php if ($result->num_rows > 0): ?>
      <?php while($row = $result->fetch_assoc()): ?>
        <?php 
          $display_name = !empty($row['nickname']) ? $row['nickname'] : $row['name'];
          $cat_text = [
            'free' => '자유',
            'review' => '리뷰',
            'recommend' => '추천',
            'question' => '질문'
          ][$row['category']] ?? $row['category'];
          
          // Get comment count
          $comment_count = $conn->query("SELECT COUNT(*) as cnt FROM community_comments WHERE post_id = {$row['id']}")->fetch_assoc()['cnt'];
          
          // Extract thumbnail
          $thumbnail = !empty($row['image']) ? $row['image'] : '';
          if (empty($thumbnail)) {
              preg_match('/<img[^>]+src="([^">]+)"/', $row['content'], $match);
              if (isset($match[1])) {
                  $thumbnail = $match[1];
              }
          }
        ?>
        <div class="post-item" onclick="location.href='/dokju/community_view.php?id=<?php echo $row['id']; ?>'" style="cursor:pointer;">
          <!-- Thumbnail -->
          <div class="post-thumbnail <?php echo empty($thumbnail) ? 'no-image' : ''; ?>" style="display:flex; align-items:center; justify-content:center; overflow:hidden;">
            <?php if (!empty($thumbnail)): ?>
              <img src="<?php echo htmlspecialchars($thumbnail); ?>" alt="post image" style="width:100%; height:100%; object-fit:cover;">
            <?php else: ?>
               <svg viewBox="0 0 24 24" style="width:32px; height:32px; fill:#ccc;"><path d="M14.06 9.02l.92.92L5.92 19H5v-.92l9.06-9.06M17.66 3c-.25 0-.51.1-.7.29l-1.83 1.83 3.75 3.75 1.83-1.83c.39-.39.39-1.04 0-1.43l-2.34-2.34c-.2-.2-.45-.29-.71-.29zm-3.6 3.19L3 17.25V21h3.75L17.81 9.94l-3.75-3.75z"/></svg>
            <?php endif; ?>
          </div>
          
          <!-- Info -->
          <div class="post-info">
            <span class="post-cat <?php echo $row['category']; ?>"><?php echo $cat_text; ?></span>
            <a href="/dokju/community_view.php?id=<?php echo $row['id']; ?>" class="post-title">
              <?php echo htmlspecialchars($row['title']); ?>
              <?php if($comment_count > 0): ?>
                <span style="color:#ef6c00; font-size:14px; margin-left:5px;">[<?php echo $comment_count; ?>]</span>
              <?php endif; ?>
            </a>
            <div class="post-meta">
              <span><?php echo $display_name; ?></span>
              <span><?php echo date('Y-m-d H:i', strtotime($row['created_at'])); ?></span>
            </div>
            <div class="post-stats">
              <span class="stat-item">👁️ <span><?php echo $row['views']; ?></span></span>
              <span class="stat-item">👍 <span><?php echo $row['likes']; ?></span></span>
              <span class="stat-item">💬 <span><?php echo $comment_count; ?></span></span>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    <?php else: ?>
      <div style="text-align:center; padding:100px 0; color:#999; background:#fff; border-radius:8px;">
        <p style="font-size:18px;">작성된 글이 없습니다.</p>
      </div>
    <?php endif; ?>
  </div>

  <!-- Pagination -->
  <?php if ($total_pages > 1): ?>
  <div class="pagination" style="display:flex !important; justify-content: center; gap: 10px; margin-top: 40px;">
    <?php 
      $cat_safe = htmlspecialchars($category);
      $sort_safe = htmlspecialchars($sort);
      
      // Prev
      if ($page_no > 1) {
          $prev = $page_no - 1;
          echo '<a href="?category='.$cat_safe.'&sort='.$sort_safe.'&page='.$prev.'" class="page-link">이전</a>';
      } else {
          echo '<span class="page-link disabled">이전</span>';
      }
      
      // Pages
      for ($i = 1; $i <= $total_pages; $i++) {
          $active = ($i == $page_no) ? 'active' : '';
          echo '<a href="?category='.$cat_safe.'&sort='.$sort_safe.'&page='.$i.'" class="page-link '.$active.'">'.$i.'</a>';
      }
      
      // Next
      if ($page_no < $total_pages) {
          $next = $page_no + 1;
          echo '<a href="?category='.$cat_safe.'&sort='.$sort_safe.'&page='.$next.'" class="page-link">다음</a>';
      } else {
          echo '<span class="page-link disabled">다음</span>';
      }
    ?>
  </div>
  <?php endif; ?>
</main>

<?php include './include/footer.php'; ?>
