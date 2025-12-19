<?php
session_start();
include './include/db_connect.php'; 

// Check Auth
if(!isset($_SESSION['userid'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='/dokju/login.php';</script>";
    exit;
}

$userid = $_SESSION['userid'];

// Get User Info
$stmt = $conn->prepare("SELECT id, name, nickname, email, phone FROM users WHERE userid = ?");
$stmt->bind_param("s", $userid);
$stmt->execute();
$res = $stmt->get_result();
$u = $res->fetch_assoc();

// Tab Logic
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'order';

// Pagination Helper
$page_num = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

function renderPagination($total, $per_page, $curr, $tab) {
    if($total == 0) return;
    $total_pages = ceil($total / $per_page);
    if($total_pages <= 1) return;
    
    echo '<div class="pagination">';
    for($i=1; $i<=$total_pages; $i++) {
        $active = ($i == $curr) ? 'active' : '';
        echo "<a href='?tab=$tab&page=$i' class='$active'>$i</a>";
    }
    echo '</div>';
    echo '<style>.pagination{margin-top:40px; text-align:center;} .pagination a{display:inline-block; margin:0 4px; padding:8px 14px; background:#fff; color:#555; text-decoration:none; border:1px solid #ddd; border-radius:4px; font-size:14px; transition:all 0.2s;} .pagination a.active{background:#2b2b2b; color:#fff; border-color:#2b2b2b; font-weight:600;} .pagination a:hover:not(.active){background:#f5f5f5;}</style>';
}

// Fetch Wishlist if needed
$wish_items = [];
$total_wish = 0;
if($tab == 'wish') {
    $limit = 9;
    $offset = ($page_num - 1) * $limit;
    
    // Count
    $wc_sql = "SELECT count(*) as cnt FROM wishlists WHERE user_id = ?";
    $wc_stmt = $conn->prepare($wc_sql);
    $wc_stmt->bind_param("i", $u['id']);
    $wc_stmt->execute();
    $total_wish = $wc_stmt->get_result()->fetch_assoc()['cnt'];
    
    $w_sql = "SELECT p.* FROM wishlists w JOIN products p ON w.product_id = p.id WHERE w.user_id = ? ORDER BY w.created_at DESC LIMIT $offset, $limit";
    $w_stmt = $conn->prepare($w_sql);
    $w_stmt->bind_param("i", $u['id']);
    $w_stmt->execute();
    $wish_res = $w_stmt->get_result();
    while($row = $wish_res->fetch_assoc()) {
        $wish_items[] = $row;
    }
}

// Mark Alerts Read (Before Header Load)
if($tab == 'alerts' && isset($userid)) {
    $check_exists = $conn->query("SHOW TABLES LIKE 'notifications'");
    if($check_exists && $check_exists->num_rows > 0) {
         $conn->query("UPDATE notifications SET is_read = 1 WHERE userid = '$userid'");
    }
}

include './include/header.php'; 
?>
<link rel="stylesheet" href="/dokju/css/member.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="/dokju/css/shop.css?v=<?php echo time(); ?>"> <!-- Reuse shop styles for wishlist grid -->

<main class="mypage-container">
    <!-- Side Nav -->
    <nav class="mypage-nav">
       <h3 style="margin-bottom:20px;">MY PAGE</h3>
       <ul>
          <li><a href="?tab=order" class="<?php echo ($tab=='order')?'active':''; ?>">주문 내역</a></li>
          <li><a href="?tab=wish" class="<?php echo ($tab=='wish')?'active':''; ?>">관심 상품</a></li>
          <li><a href="?tab=posts" class="<?php echo ($tab=='posts')?'active':''; ?>">내가 쓴 글</a></li>
          <li><a href="?tab=comments" class="<?php echo ($tab=='comments')?'active':''; ?>">내가 쓴 댓글</a></li>
          <li><a href="?tab=alerts" class="<?php echo ($tab=='alerts')?'active':''; ?>">알림</a></li>
          <li><a href="?tab=info" class="<?php echo ($tab=='info')?'active':''; ?>">내 정보</a></li>
       </ul>
    </nav>
    
    <!-- Content -->
    <div class="mypage-content">
       <!-- Global Styles for MyPage -->
       <style>
       /* New Order Card Styles */
       .order-card-new {
           background-color: #f7f3eb; /* Beige Background */
           border-radius: 4px;
           padding: 25px;
           margin-bottom: 20px;
       }
       
       .card-header {
           font-size: 14px;
           color: #999;
           margin-bottom: 20px;
           font-family: 'Inter', sans-serif;
           border-bottom: 1px solid #e0dfd5; /* Subtle divider below header */
           padding-bottom: 10px;
       }
       
       .card-header .divider {
           margin: 0 8px;
           color: #ddd;
       }
       
       .card-body {
           display: flex;
           align-items: center;
           flex-direction: row; /* Force horizontal */
       }
       
       .img-wrapper {
           flex: 0 0 100px; /* Fixed width, don't grow or shrink */
           width: 100px;
           height: 100px;
           background: #fff;
           margin-right: 30px;
           display: flex;
           align-items: center;
           justify-content: center;
           border: 1px solid #eee;
           overflow: hidden; /* Cut off if too big */
       }
       
       .img-wrapper img {
           max-width: 100%;
           max-height: 100%;
           object-fit: contain;
           width: auto !important; /* Override potential global styles */
           height: auto !important;
       }
       
       .info-wrapper {
           flex: 1;
           display: flex;
           flex-direction: column;
           gap: 8px;
       }
       
       .p-name {
           font-size: 18px;
           font-weight: 600;
           color: #2b2b2b;
       }
       
       .p-price {
           font-size: 16px;
           font-weight: 500;
           color: #444;
       }
       
       .p-status {
           font-size: 14px;
           font-weight: 600;
       }
       
       .badge {
           position: absolute;
           bottom: 0;
           right: 0;
           background: rgba(0,0,0,0.6);
           color: #fff;
           font-size: 10px;
           padding: 2px 5px;
       }
       
       .action-wrapper {
           flex: 0 0 auto; /* Don't shrink */
           margin-left: 20px;
       }
       
       .btn-track {
           display: inline-block;
           padding: 10px 20px;
           border: 1px solid #ddd;
           background: #fff;
           color: #555;
           text-decoration: none;
           font-size: 14px;
           border-radius: 4px;
           transition: all 0.2s;
           white-space: nowrap; /* Prevent text wrap */
       }
       
       .btn-track:hover {
           border-color: #aaa;
           color: #2b2b2b;
       }
       
       .empty-msg {
           padding: 80px 0;
           text-align: center;
           color: #999;
           border: 1px solid #eee;
           background: #fff;
       }
       </style>

       <!-- Order History Tab -->
       <?php if($tab == 'order'): ?>
       <?php 
           $limit = 5;
           $offset = ($page_num - 1) * $limit;
           
           // Count
           $oc_sql = "SELECT count(*) as cnt FROM orders WHERE userid = ?";
           $oc_stmt = $conn->prepare($oc_sql);
           $oc_stmt->bind_param("s", $userid);
           $oc_stmt->execute();
           $total_orders = $oc_stmt->get_result()->fetch_assoc()['cnt'];

           // Fetch Orders
           $ord_sql = "SELECT * FROM orders WHERE userid = ? ORDER BY created_at DESC LIMIT $offset, $limit";
           $ord_stmt = $conn->prepare($ord_sql);
           $ord_stmt->bind_param("s", $userid);
           $ord_stmt->execute();
           $orders = $ord_stmt->get_result();
       ?>
       <section class="my-section">
          <!-- Fixed Header: Removed redundant div wrapper with border -->
          <h3 class="my-title">최근 주문 내역</h3>
          
          <div class="order-list">
             <?php if($orders->num_rows > 0): ?>
                 <?php while($row = $orders->fetch_assoc()): ?>
                     <?php
                        // Get first item info
                        $oid = $row['order_uid'];
                        $item_sql = "SELECT product_name, image, count(*) as cnt FROM order_items WHERE order_uid = '$oid'";
                        $item_row = $conn->query($item_sql)->fetch_assoc();
                        $title = $item_row['product_name'];
                        if($item_row['cnt'] > 1) {
                            $title .= ' 외 ' . ($item_row['cnt']-1) . '건';
                        }
                        $img = !empty($item_row['image']) ? $item_row['image'] : '/dokju/images/sake_bottle.jpg';
                        
                        $status_map = [
                            'PENDING' => '결제대기',
                            'PAID' => '결제완료',
                            'PREPARING' => '배송준비',
                            'SHIPPING' => '배송중',
                            'DELIVERED' => '배송완료',
                            'CANCELLED' => '주문취소'
                        ];
                        $status_text = $status_map[$row['status']] ?? $row['status'];
                        $status_color = ($row['status'] == 'PAID') ? '#4CAF50' : '#888';
                     ?>
                     
                     <!-- Order Card -->
                     <div class="order-card-new">
                         <!-- Header: Date & No -->
                         <div class="card-header">
                             <?php echo date('Y.m.d H:i', strtotime($row['created_at'])); ?> 
                             <span class="divider">|</span> 
                             주문번호 <?php echo $row['order_uid']; ?>
                         </div>
                         
                         <!-- Body -->
                         <div class="card-body">
                             <!-- Image -->
                             <div class="img-wrapper">
                                 <img src="<?php echo $img; ?>" alt="product">
                                 <?php if($item_row['cnt'] > 1): ?>
                                     <span class="badge">+<?php echo $item_row['cnt']-1; ?></span>
                                 <?php endif; ?>
                             </div>
                             
                             <!-- Info -->
                             <div class="info-wrapper">
                                 <div class="p-name"><?php echo $title; ?></div>
                                 <div class="p-price"><?php echo number_format($row['total_amount']); ?>원</div>
                                 <div class="p-status" style="color:<?php echo $status_color; ?>;">
                                     [<?php echo $status_text; ?>]
                                 </div>
                             </div>
                             
                             <!-- Action -->
                             <div class="action-wrapper">
                                 <a href="#" class="btn-track" onclick="alert('현재 배송 준비중입니다.'); return false;">배송조회</a>
                             </div>
                         </div>
                     </div>
                 <?php endwhile; ?>
             <?php else: ?>
                 <div class="empty-msg">
                    주문 내역이 없습니다.
                 </div>
             <?php endif; ?>
          </div>
          <?php renderPagination($total_orders, $limit, $page_num, 'order'); ?>
       </section>

       <?php elseif($tab == 'posts'): ?>
         <?php
           $limit = 5;
           $offset = ($page_num - 1) * $limit;
           
           // Count
           $pc_stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM community_posts WHERE userid = ?");
           $pc_stmt->bind_param("s", $userid);
           $pc_stmt->execute();
           $total_posts = $pc_stmt->get_result()->fetch_assoc()['cnt'];
           
           $post_stmt = $conn->prepare("
               SELECT p.*, 
               (SELECT COUNT(*) FROM community_comments WHERE post_id = p.id) as cmt_cnt 
               FROM community_posts p 
               WHERE userid = ? 
               ORDER BY created_at DESC
               LIMIT $offset, $limit
           ");
           $post_stmt->bind_param("s", $userid);
           $post_stmt->execute();
           $posts = $post_stmt->get_result();
         ?>
         <section class="my-section">
            <h3 class="my-title">내가 쓴 글 (<?php echo $total_posts; ?>)</h3>
            <?php if($posts->num_rows > 0): ?>
                <ul class="mypage-post-list" style="list-style:none; padding:0;">
                <?php while($p = $posts->fetch_assoc()): ?>
                    <li onclick="location.href='/dokju/community_view.php?id=<?php echo $p['id']; ?>'" style="cursor:pointer; border-bottom:1px solid #eee; padding:20px 0; transition:background 0.2s;">
                        <div style="font-size:16px; font-weight:600; margin-bottom:8px; color:#333; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                            <?php echo htmlspecialchars($p['title']); ?>
                            <?php if($p['cmt_cnt'] > 0): ?>
                                <span style="color:#ef6c00; font-size:14px; margin-left:5px;">[<?php echo $p['cmt_cnt']; ?>]</span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size:13px; color:#999;">
                            <span style="color:#ef6c00; margin-right:5px;">[<?php echo $p['category']=='free'?'자유':($p['category']=='review'?'리뷰':($p['category']=='question'?'질문':'추천')); ?>]</span>
                            <?php echo date('Y-m-d', strtotime($p['created_at'])); ?> 
                            <span style="margin:0 5px;">·</span> 조회 <?php echo $p['views']; ?> 
                            <span style="margin:0 5px;">·</span> 추천 <?php echo $p['likes']; ?>
                        </div>
                    </li>
                <?php endwhile; ?>
                </ul>
                <?php renderPagination($total_posts, $limit, $page_num, 'posts'); ?>
            <?php else: ?>
                <div class="empty-msg">작성한 게시글이 없습니다.</div>
            <?php endif; ?>
         </section>



       <?php elseif($tab == 'comments'): ?>
         <?php
           $cmt_stmt = $conn->prepare("SELECT c.*, p.title as post_title FROM community_comments c JOIN community_posts p ON c.post_id = p.id WHERE c.userid = ? ORDER BY c.created_at DESC");
           $cmt_stmt->bind_param("s", $userid);
           $cmt_stmt->execute();
           $comments = $cmt_stmt->get_result();
         ?>
         <section class="my-section">
            <h3 class="my-title">내가 쓴 댓글 (<?php echo $comments->num_rows; ?>)</h3>
            <?php if($comments->num_rows > 0): ?>
                <ul class="mypage-post-list" style="list-style:none; padding:0;">
                <?php while($c = $comments->fetch_assoc()): ?>
                    <li onclick="location.href='/dokju/community_view.php?id=<?php echo $c['post_id']; ?>'" style="cursor:pointer; border-bottom:1px solid #eee; padding:20px 0; transition:background 0.2s;">
                        <div style="font-size:15px; margin-bottom:8px; color:#444;">
                            <?php echo htmlspecialchars($c['content']); ?>
                        </div>
                        <div style="font-size:13px; color:#999;">
                            원문: <span style="color:#2b2b2b; font-weight:600;"><?php echo htmlspecialchars($c['post_title']); ?></span>
                            <span style="margin:0 5px;">·</span> <?php echo date('Y-m-d H:i', strtotime($c['created_at'])); ?>
                        </div>
                    </li>
                <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <div class="empty-msg">작성한 댓글이 없습니다.</div>
            <?php endif; ?>
         </section>

       <?php elseif($tab == 'alerts'): ?>
         <?php
           $limit = 5;
           $offset = ($page_num - 1) * $limit;
           
           $total_alerts = 0;
           $alerts = [];
           
           // Check table
           $alerts_exist = $conn->query("SHOW TABLES LIKE 'notifications'")->num_rows > 0;
           
           if($alerts_exist) {
               // Count
               $ac_stmt = $conn->prepare("SELECT count(*) as cnt FROM notifications WHERE userid = ?");
               $ac_stmt->bind_param("s", $userid);
               $ac_stmt->execute();
               $total_alerts = $ac_stmt->get_result()->fetch_assoc()['cnt'];
               
               $noti_stmt = $conn->prepare("SELECT * FROM notifications WHERE userid = ? ORDER BY created_at DESC LIMIT $offset, $limit");
               $noti_stmt->bind_param("s", $userid);
               $noti_stmt->execute();
               $res = $noti_stmt->get_result();
               while($row = $res->fetch_assoc()) $alerts[] = $row;
           }
         ?>
         <section class="my-section">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
               <h3 class="my-title" style="margin:0; border:none; padding:0;">알림 (<?php echo $total_alerts; ?>)</h3>
               <?php if($total_alerts > 0): ?>
                   <button onclick="deleteAllAlerts()" style="padding:6px 12px; background:#f5f5f5; border:1px solid #ddd; border-radius:4px; font-size:13px; cursor:pointer;">전체 삭제</button>
               <?php endif; ?>
            </div>
            
            <?php if(count($alerts) > 0): ?>
                <ul class="mypage-post-list" style="list-style:none; padding:0;">
                <?php foreach($alerts as $not): ?>
                    <li onclick="location.href='<?php echo $not['link']; ?>'" style="cursor:pointer; border-bottom:1px solid #eee; padding:20px 0; transition:background 0.2s;">
                        <div style="font-size:15px; margin-bottom:5px; color:#333;">
                            <?php echo htmlspecialchars($not['message']); ?>
                        </div>
                        <div style="font-size:12px; color:#999;">
                            <?php echo date('Y-m-d H:i', strtotime($not['created_at'])); ?>
                        </div>
                    </li>
                <?php endforeach; ?>
                </ul>
                <?php renderPagination($total_alerts, $limit, $page_num, 'alerts'); ?>
            <?php else: ?>
                <div class="empty-msg">새로운 알림이 없습니다.</div>
            <?php endif; ?>
         </section>
         
         <script>
           function deleteAllAlerts() {
               if(!confirm('모든 알림을 삭제하시겠습니까?')) return;
               
               const formData = new FormData();
               formData.append('mode', 'delete_all');
               
               fetch('/dokju/ajax_alert_process.php', { method:'POST', body:formData })
               .then(res => res.json())
               .then(data => {
                   if(data.success) location.reload();
                   else alert('삭제 실패');
               });
           }
         </script>

       <!-- Info Tab (New) -->
       <?php elseif($tab == 'info'): ?>
       <section class="my-section">
          <h3 class="my-title">내 정보</h3>
          
          <div class="info-card">
              <div class="info-row">
                  <label class="info-label">아이디</label>
                  <div class="info-value"><?php echo $userid; ?></div>
              </div>
              <div class="info-row">
                  <label class="info-label">이름</label>
                  <div class="info-value"><?php echo $u['name']; ?> (<?php echo $u['nickname']; ?>)</div>
              </div>
              <div class="info-row">
                  <label class="info-label">이메일</label>
                  <div class="info-value"><?php echo $u['email']; ?></div>
              </div>
              <div class="info-row-last">
                   <label class="info-label">휴대폰 번호</label>
                   <div class="info-value"><?php echo $u['phone']; ?></div>
              </div>
              
              <div class="info-action-wrapper">
                  <a href="/dokju/member_edit.php" class="btn-edit">정보 수정</a>
              </div>
          </div>
       </section>

       <style>
       /* Info Tab Styles */
       .info-card {
           border: 1px solid #eee;
           padding: 30px;
           border-radius: 4px;
       }

       .info-row {
           margin-bottom: 20px;
       }

       .info-row-last {
           margin-bottom: 30px;
       }
       
       .info-label {
           color: #888;
           font-size: 14px;
           display: block;
           margin-bottom: 5px;
       }

       .info-value {
           font-size: 16px;
           font-weight: 500;
       }

       .info-action-wrapper {
           padding-top: 20px;
           border-top: 1px solid #eee;
           display: flex;
           justify-content: flex-end;
       }

       .btn-edit {
           padding: 12px 24px;
           background: #2b2b2b;
           color: #fff;
           text-decoration: none;
           border-radius: 4px;
       }
       </style>
       <?php elseif($tab == 'wish'): ?>
       <section class="my-section">
          <h3 class="my-title">관심 상품 (<?php echo $total_wish; ?>)</h3>
          
          <?php if(count($wish_items) > 0): ?>
          <div class="product-grid" style="grid-template-columns: repeat(3, 1fr); gap:20px;">
            <?php foreach($wish_items as $item): 
                 $img_src = !empty($item['image']) ? $item['image'] : '/dokju/images/sake_bottle.jpg';
            ?>
            <div class="product-card" onclick="location.href='/dokju/product_view.php?id=<?php echo $item['id']; ?>'" style="box-shadow:none; border:1px solid #eee;">
              <div class="img-box" style="height:200px;">
                <img src="<?php echo $img_src; ?>" alt="<?php echo $item['product_name']; ?>" style="width:100%; height:100%; object-fit:contain; padding:10px;">
              </div>
              <div class="product-info" style="padding:15px;">
                <h3 class="name" style="font-size:16px; margin-bottom:5px;"><?php echo $item['product_name']; ?></h3>
                <p class="price"><strong><?php echo number_format($item['price']); ?></strong>원</p>
                <div style="margin-top:10px;">
                    <button onclick="deleteWish(<?php echo $item['id']; ?>, event)" style="width:100%; padding:8px; background:#eee; border:none; cursor:pointer;">삭제</button>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php renderPagination($total_wish, $limit, $page_num, 'wish'); ?>
          <?php else: ?>
          <div style="padding:40px; text-align:center; color:#999; border:1px solid #eee;">
             관심 등록한 상품이 없습니다.
          </div>
          <?php endif; ?>
       </section>
       <script>
       function deleteWish(pid, e) {
           e.stopPropagation();
           if(!confirm('정말 삭제하시겠습니까?')) return;
           
           const formData = new FormData();
           formData.append('product_id', pid);
           fetch('/dokju/ajax_wishlist.php', { method:'POST', body:formData })
           .then(res => res.json())
           .then(data => {
               if(data.success) location.reload();
               else alert(data.message);
           });
       }
       </script>
       <?php endif; ?>
    </div>
<style>
/* Mobile & Tablet Responsive for MyPage */
@media (max-width: 1200px) {
    .mypage-container {
        flex-direction: column;
        display: flex;
        padding: 20px 15px;
    }
    
    /* Nav as Horizontal Tabs (Scrollable) */
    .mypage-nav {
        width: 100%;
        margin-bottom: 30px;
        border-right: none;
        padding-right: 0;
        text-align: left;
    }
    .mypage-nav h3 {
        margin-bottom: 15px !important;
        display: block;
    }
    .mypage-nav ul {
        display: flex;
        overflow-x: auto;
        padding-bottom: 10px;
        gap: 20px;
        border-bottom: 1px solid #eee;
        justify-content: flex-start;
    }
    .mypage-nav li {
        margin-bottom: 0;
        flex-shrink: 0;
    }
    .mypage-nav a {
        display: block;
        padding: 8px 5px;
        font-size: 16px;
        color: #888;
        text-decoration: none;
    }
    .mypage-nav a.active {
        font-weight: bold;
        color: #2b2b2b;
        border-bottom: 2px solid #2b2b2b;
    }
    
    /* Content Area */
    .mypage-content {
        width: 100%;
        padding-left: 0;
    }
    
    /* Order Card Stack */
    .card-body {
        flex-direction: column;
        align-items: flex-start;
    }
    .img-wrapper {
        margin-right: 0;
        margin-bottom: 15px;
        width: 80px; height: 80px;
    }
    .info-wrapper {
        width: 100%;
        margin-bottom: 15px;
    }
    .action-wrapper {
        width: 100%;
        margin-left: 0;
        margin-top: 10px;
    }
    .btn-track {
        display: block;
        width: 100%;
        text-align: center;
        padding: 12px;
    }
    
    /* Wishlist Grid */
    .product-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 15px !important;
    }
    
    /* Info Tab */
    .info-card { padding: 20px; }
    .info-row { margin-bottom: 15px; }
}
@media (max-width: 480px) {
    .product-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>
</main>

<?php include './include/footer.php'; ?>
