<?php
session_start();
include './include/db_connect.php';

$id = $_GET['id'] ?? 0;

// Get post
$stmt = $conn->prepare("SELECT p.*, u.name, u.nickname FROM community_posts p LEFT JOIN users u ON p.userid = u.userid WHERE p.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    echo "<script>alert('존재하지 않는 게시글입니다.'); location.href='/dokju/community.php';</script>";
    exit;
}

// Increase view count
$conn->query("UPDATE community_posts SET views = views + 1 WHERE id = $id");

// Get comments
$comments = $conn->query("SELECT c.*, u.name, u.nickname FROM community_comments c LEFT JOIN users u ON c.userid = u.userid WHERE c.post_id = $id ORDER BY c.created_at ASC");

// Check if current user liked this post
$user_liked = false;
if (isset($_SESSION['userid'])) {
    $check = $conn->prepare("SELECT id FROM community_likes WHERE post_id = ? AND userid = ?");
    $check->bind_param("is", $id, $_SESSION['userid']);
    $check->execute();
    $user_liked = $check->get_result()->num_rows > 0;
}

$display_name = !empty($post['nickname']) ? $post['nickname'] : $post['name'];
$cat_text = [
    'review' => '리뷰',
    'recommend' => '추천',
    'question' => '질문'
][$post['category']] ?? $post['category'];

include './include/header.php';
?>
<link rel="stylesheet" href="/dokju/css/community.css?v=<?php echo time(); ?>">

<main class="community-container">
  <!-- Post Header -->
  <div class="view-header">
    <span class="view-cat">[<?php echo $cat_text; ?>]</span>
    <h1 class="view-title"><?php echo htmlspecialchars($post['title']); ?></h1>
    <div class="view-meta">
      <div>
        <span><?php echo $display_name; ?></span>
        <span style="margin-left:15px; color:#ccc;">|</span>
        <span style="margin-left:15px;"><?php echo date('Y-m-d H:i', strtotime($post['created_at'])); ?></span>
      </div>
      <div>
        <span>조회 <?php echo $post['views']; ?></span>
        <span style="margin-left:15px;">추천 <?php echo $post['likes']; ?></span>
      </div>
    </div>
  </div>

  <!-- Post Content -->
  <div class="view-content">
    <?php echo $post['content']; // Allow HTML ?>
  </div>

  <!-- Post Image -->
  <?php if (!empty($post['image'])): ?>
  <div style="text-align:center; margin-bottom:40px;">
    <img src="<?php echo htmlspecialchars($post['image']); ?>" 
         alt="post image" 
         class="view-image">
  </div>
  <?php endif; ?>

  <!-- Like Button -->
  <div class="view-actions">
    <button class="btn-like <?php echo $user_liked ? 'active' : ''; ?>" 
            onclick="toggleLike()" id="likeBtn">
      👍 추천 <span id="likeCount"><?php echo $post['likes']; ?></span>
    </button>
  </div>

  <!-- Edit/Delete Buttons (only for author) -->
  <?php if (isset($_SESSION['userid']) && $_SESSION['userid'] === $post['userid']): ?>
  <div class="view-btns">
    <a href="/dokju/community_write.php?id=<?php echo $id; ?>" class="btn-sm">수정</a>
    <a href="/dokju/community_process.php?mode=delete&id=<?php echo $id; ?>" 
       class="btn-sm delete" 
       onclick="return confirm('정말 삭제하시겠습니까?')">삭제</a>
  </div>
  <?php endif; ?>

  <!-- Comment Section -->
  <div class="comment-section">
    <div class="comment-count">댓글 <?php echo $comments->num_rows; ?>개</div>

    <!-- Comment Form -->
    <?php if (isset($_SESSION['userid'])): ?>
    <form class="comment-form" method="POST" action="/dokju/community_process.php">
      <input type="hidden" name="mode" value="comment">
      <input type="hidden" name="post_id" value="<?php echo $id; ?>">
      <textarea name="content" class="comment-input" placeholder="댓글을 입력하세요" required></textarea>
      <button type="submit" class="btn-comment">등록</button>
    </form>
    <?php else: ?>
    <div style="text-align:center; padding:20px; background:#fff; border:1px solid #ddd; border-radius:4px; color:#999;">
      댓글을 작성하려면 <a href="/dokju/login.php" style="color:#2b2b2b; font-weight:700;">로그인</a>이 필요합니다.
    </div>
    <?php endif; ?>

    <!-- Comment List -->
    <ul class="comment-list">
      <?php if ($comments->num_rows > 0): ?>
        <?php while($comment = $comments->fetch_assoc()): ?>
          <?php $comment_user = !empty($comment['nickname']) ? $comment['nickname'] : $comment['name']; ?>
          <li class="comment-item">
            <div class="comment-header">
              <span class="comment-user"><?php echo $comment_user; ?></span>
              <span class="comment-date">
                <?php echo date('Y-m-d H:i', strtotime($comment['created_at'])); ?>
                <?php if (isset($_SESSION['userid']) && ($_SESSION['userid'] === $comment['userid'] || $_SESSION['userid'] === $post['userid'])): ?>
                  <a href="/dokju/community_process.php?mode=delete_comment&id=<?php echo $comment['id']; ?>&post_id=<?php echo $id; ?>" 
                     class="comment-del" 
                     onclick="return confirm('댓글을 삭제하시겠습니까?')">삭제</a>
                <?php endif; ?>
              </span>
            </div>
            <div class="comment-body"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></div>
          </li>
        <?php endwhile; ?>
      <?php else: ?>
        <li style="text-align:center; padding:40px; color:#999;">첫 댓글을 작성해보세요!</li>
      <?php endif; ?>
    </ul>
  </div>

  <!-- Back Button -->
  <div style="text-align:center; margin-top:40px;">
    <a href="/dokju/community.php" class="btn-cancel" style="display:inline-block; padding:12px 40px;">목록으로</a>
  </div>
</main>

<script>
function toggleLike() {
    <?php if (!isset($_SESSION['userid'])): ?>
    alert('로그인이 필요합니다.');
    location.href = '/dokju/login.php';
    return;
    <?php else: ?>
    const formData = new FormData();
    formData.append('mode', 'like');
    formData.append('post_id', <?php echo $id; ?>);
    
    fetch('/dokju/community_process.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        const btn = document.getElementById('likeBtn');
        const count = document.getElementById('likeCount');
        
        if (data.trim() === 'liked') {
            btn.classList.add('active');
            count.textContent = parseInt(count.textContent) + 1;
        } else if (data.trim() === 'unliked') {
            btn.classList.remove('active');
            count.textContent = parseInt(count.textContent) - 1;
        }
    });
    <?php endif; ?>
}
</script>

<?php include './include/footer.php'; ?>
