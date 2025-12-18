<?php
include './include/db_connect.php'; 

// Category Filter
$category = isset($_GET['category']) ? $_GET['category'] : 'ALL';
$where_clause = "";
if($category != 'ALL' && $category != '전체') {
    $cat_esc = $conn->real_escape_string($category);
    // Use LIKE to find type in badges or type column.
    // Assuming 'type' column holds "Junmai", "Ginjo" etc.
    $where_clause = "WHERE type LIKE '%$cat_esc%'";
}

// Sorting logic
// Sorting logic
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$order_clause = "ORDER BY id DESC"; // Default (newest)
switch($sort) {
    case 'newest':
        $order_clause = "ORDER BY id DESC"; 
        break;
    case 'popular': 
        $order_clause = "ORDER BY wish_count DESC, id DESC"; 
        break;
    case 'recommend':
        $order_clause = "ORDER BY id ASC"; 
        break;
    case 'price_asc': 
        $order_clause = "ORDER BY price ASC"; 
        break;
    case 'price_desc': 
        $order_clause = "ORDER BY price DESC"; 
        break;
    default: 
        $order_clause = "ORDER BY id DESC"; 
        break;
}

// Pagination
$page_num = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 12;
$offset = ($page_num - 1) * $limit;

// Count
$count_sql = "SELECT count(*) as cnt FROM products $where_clause";
$count_res = $conn->query($count_sql);
$total_items = $count_res->fetch_assoc()['cnt'];
$total_pages = ceil($total_items / $limit);

// Get User PK for Wishlist Check
$userid = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;
$user_pk = 0;
if($userid) {
    $start_sess = $conn->prepare("SELECT id FROM users WHERE userid=?");
    $start_sess->bind_param("s", $userid);
    $start_sess->execute();
    $u_res = $start_sess->get_result();
    if($u = $u_res->fetch_assoc()) $user_pk = $u['id'];
}

// Fetch Products
$sql = "SELECT p.*, 
       (SELECT count(*) FROM wishlists w WHERE w.product_id = p.id AND w.user_id = $user_pk) as is_wished,
       (SELECT count(*) FROM wishlists w WHERE w.product_id = p.id) as wish_count
        FROM products p 
        $where_clause
        $order_clause
        LIMIT $offset, $limit";
$res_products = $conn->query($sql);

include './include/header.php'; 
?>
<link rel="stylesheet" href="/dokju/css/shop.css?v=<?php echo time(); ?>">

<main class="shop-container">
  <div class="shop-header">
     <h2>SHOP</h2>
     <p style="color:#666; font-size:16px; margin-top:10px;">엄선된 프리미엄 사케 컬렉션을 만나보세요</p>
  </div>

  <div class="shop-controls">
     <div class="filters">
         <a href="?category=ALL" class="<?php echo ($category=='ALL' || $category=='전체')?'active':''; ?>">전체</a>
         <a href="?category=준마이" class="<?php echo ($category=='준마이')?'active':''; ?>">준마이슈</a>
         <a href="?category=혼조조" class="<?php echo ($category=='혼조조')?'active':''; ?>">혼조조</a>
         <a href="?category=긴조" class="<?php echo ($category=='긴조')?'active':''; ?>">긴조</a>
         <a href="?category=다이긴조" class="<?php echo ($category=='다이긴조')?'active':''; ?>">다이긴조</a>
         <a href="?category=후츠슈" class="<?php echo ($category=='후츠슈')?'active':''; ?>">후츠슈</a>
     </div>
      <div class="sort">
          <select onchange="location.href='?category=<?php echo $category; ?>&sort='+this.value">
              <option value="newest" <?php echo ($sort=='newest')?'selected':''; ?>>최신순</option>
              <option value="recommend" <?php echo ($sort=='recommend')?'selected':''; ?>>추천순</option>
              <option value="popular" <?php echo ($sort=='popular')?'selected':''; ?>>인기순</option>
              <option value="price_asc" <?php echo ($sort=='price_asc')?'selected':''; ?>>낮은가격순</option>
              <option value="price_desc" <?php echo ($sort=='price_desc')?'selected':''; ?>>높은가격순</option>
          </select>
      </div>
  </div>

  <div class="product-grid">
    <?php if($total_items > 0): ?>
        <?php while($item = $res_products->fetch_assoc()): 
             $badges = explode(',', $item['badges']); 
             $badge = !empty($badges[0]) ? $badges[0] : '';
             $img_src = !empty($item['image']) ? $item['image'] : '/dokju/images/sake_bottle.jpg';
             $wished_class = ($item['is_wished'] > 0) ? 'active' : '';
        ?>
        <div class="product-card" onclick="location.href='/dokju/product_view.php?id=<?php echo $item['id']; ?>'">
          <?php if($badge): ?><span class="badge <?php echo strtolower($badge); ?>"><?php echo $badge; ?></span><?php endif; ?>
          
          <button class="wishlist-btn <?php echo $wished_class; ?>" aria-label="찜하기" onclick="toggleWish(<?php echo $item['id']; ?>, event)">♥</button>
          
          <div class="img-box">
            <img src="<?php echo $img_src; ?>" alt="<?php echo $item['product_name']; ?>" style="width:100%; height:100%; object-fit:contain; padding:20px;">
            <div class="view-btn">자세히 보기</div>
          </div>
          
          <div class="product-info">
            <h3 class="name"><?php echo $item['product_name']; ?></h3>
            <p class="spec">
                <?php echo $item['type']; ?> · 정미율 <?php echo $item['rice_polish']; ?> · <?php echo $item['region']; ?>
            </p>
            <p class="price"><strong><?php echo number_format($item['price']); ?></strong>원</p>
          </div>
        </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p style="grid-column:1/-1; text-align:center; padding:50px;">등록된 상품이 없습니다.</p>
    <?php endif; ?>
  </div>

  <div class="pagination">
      <?php for($p=1; $p<=$total_pages; $p++): ?>
         <a href="?page=<?php echo $p; ?>&category=<?php echo $category; ?>&sort=<?php echo $sort; ?>" class="<?php echo ($p==$page_num)?'active':''; ?>"><?php echo $p; ?></a>
      <?php endfor; ?>
  </div>

</main>

<script>
function toggleWish(productId, e) {
    if(e) e.stopPropagation();
    
    // AJAX to toggle wishlist
    const formData = new FormData();
    formData.append('product_id', productId);
    
    fetch('/dokju/ajax_wishlist.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            alert(data.message);
            // Toggle visual class
            if(data.action == 'added') e.target.classList.add('active');
            else e.target.classList.remove('active');
        } else {
            alert(data.message);
            if(data.message.includes('로그인')) location.href='/dokju/login.php';
        }
    });
}
</script>

<style>
/* Active Heart Style */
.wishlist-btn.active {
    color: red;
    font-weight: bold;
}
</style>
<?php include './include/footer.php'; ?>
