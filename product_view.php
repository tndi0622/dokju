<?php 
include './include/db_connect.php';
include './include/header.php'; 
?>
<link rel="stylesheet" href="/dokju/css/shop.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="/dokju/css/view.css?v=<?php echo time(); ?>">

<main class="view-container">
  <?php
    // Get ID from URL
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    // Get product from database
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();
    
    // If not found, redirect to shop
    if (!$item) {
        echo "<script>alert('상품을 찾을 수 없습니다.'); location.href='/dokju/shop.php';</script>";
        exit;
    }
    
    // Get related products (same region or type, excluding current)
    $related_sql = "SELECT * FROM products WHERE id != ? AND (region = ? OR type = ?) LIMIT 4";
    $stmt_related = $conn->prepare($related_sql);
    $stmt_related->bind_param("iss", $id, $item['region'], $item['type']);
    $stmt_related->execute();
    $related_result = $stmt_related->get_result();
    $related_items = [];
    while ($row = $related_result->fetch_assoc()) {
        $related_items[] = $row;
    }
    
    // If not enough related items, get random ones
    if (count($related_items) < 4) {
        $remaining = 4 - count($related_items);
        $random_sql = "SELECT * FROM products WHERE id != ? ORDER BY RAND() LIMIT ?";
        $stmt_random = $conn->prepare($random_sql);
        $stmt_random->bind_param("ii", $id, $remaining);
        $stmt_random->execute();
        $random_result = $stmt_random->get_result();
        while ($row = $random_result->fetch_assoc()) {
            $related_items[] = $row;
        }
    }
    
    // Check if restock notification is already applied
    $is_restock_applied = false;
    if(isset($_SESSION['userid'])) {
        $check_table = $conn->query("SHOW TABLES LIKE 'restock_notifications'");
        if($check_table && $check_table->num_rows > 0) {
           $chk_stmt = $conn->prepare("SELECT count(*) as cnt FROM restock_notifications WHERE product_id = ? AND userid = ?");
           $chk_stmt->bind_param("is", $id, $_SESSION['userid']);
           $chk_stmt->execute();
           if($chk_stmt->get_result()->fetch_assoc()['cnt'] > 0) {
               $is_restock_applied = true;
           }
        }
    }
  ?>

  <!-- Breadcrumb -->
  <div class="view-nav">
    <a href="/dokju/shop.php">SHOP</a> &gt; <span><?php echo htmlspecialchars($item['product_name']); ?></span>
  </div>

  <!-- Product Detail Top -->
  <div class="product-detail">
    <!-- Left Image -->
    <div class="pd-image">
      <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
    </div>

    <!-- Right Info -->
    <div class="pd-info">
      <div class="pd-brand">Premium Sake</div>
      <h1 class="pd-title"><?php echo htmlspecialchars($item['product_name']); ?></h1>
      <div class="pd-price"><?php echo number_format($item['price']); ?>원</div>

      <div class="pd-specs">
        <div class="spec-row">
          <span class="spec-label">주종</span>
          <span class="spec-val"><?php echo htmlspecialchars($item['type']); ?></span>
        </div>
        <div class="spec-row">
          <span class="spec-label">생산지</span>
          <span class="spec-val"><?php echo htmlspecialchars($item['region']); ?></span>
        </div>
        <div class="spec-row">
          <span class="spec-label">도수</span>
          <span class="spec-val"><?php echo htmlspecialchars($item['abv']); ?></span>
        </div>
        <div class="spec-row">
          <span class="spec-label">정미율</span>
          <span class="spec-val"><?php echo htmlspecialchars($item['rice_polish']); ?></span>
        </div>
      </div>

      <!-- Quantity -->
      <div class="pd-qty">
         <button class="qty-btn" onclick="updateQty(-1)">-</button>
         <input type="number" id="qty" class="qty-input" value="1" min="1" readonly>
         <button class="qty-btn" onclick="updateQty(1)">+</button>
      </div>

      <!-- Actions -->
      <div class="pd-actions">
        <?php if(isset($item['stock']) && $item['stock'] <= 0): ?>
            <div style="grid-column: 1 / -1; display:flex; flex-direction:column; gap:10px; width:100%;">
                <button disabled style="width:100%; padding:15px; background:#f5f5f5; color:#999; border:1px solid #ddd; font-weight:600; cursor:not-allowed;">품절된 상품입니다</button>
                <?php if($is_restock_applied): ?>
                    <button onclick="requestRestock(<?php echo $item['id']; ?>, 'cancel')" style="width:100%; padding:15px; background:#fff; border:1px solid #e74c3c; color:#e74c3c; font-weight:600; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;">
                        🔕 재입고 알림 취소
                    </button>
                <?php else: ?>
                    <button onclick="requestRestock(<?php echo $item['id']; ?>, 'apply')" style="width:100%; padding:15px; background:#fff; border:1px solid #2b2b2b; color:#2b2b2b; font-weight:600; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;">
                        🔔 재입고 알림 신청
                    </button>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <button class="btn-cart" onclick="addToCart()">장바구니</button>
            <button class="btn-buy" onclick="buyNow()">구매하기</button>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Detailed Description Section -->
  <div class="detail-section">
     <h3 class="detail-title">상품 상세 설명</h3>
     <div class="detail-content">
        <p style="font-size:18px; color:#2b2b2b; margin-bottom:20px;">
           <strong>"<?php echo htmlspecialchars($item['description']); ?>"</strong>
        </p>
        <p>
           오랜 전통의 양조 기법으로 빚어낸 <?php echo htmlspecialchars($item['product_name']); ?>입니다.<br>
           엄선된 쌀과 맑은 물을 사용하여 깊고 풍부한 맛을 자랑합니다.<br>
           특별한 날, 소중한 사람과 함께 즐기기에 완벽한 선택이 될 것입니다.
        </p>
        <br>
        <ul style="color:#666; padding-left:20px; line-height:1.8;">
           <li>보관 방법: 직사광선을 피하고 서늘한 곳에 보관해 주세요. (개봉 후 냉장 보관 권장)</li>
           <li>음용 방법: 차게 해서 드시면 깔끔한 맛을, 데워서 드시면 깊은 풍미를 느낄 수 있습니다.</li>
        </ul>
     </div>
  </div>

  <!-- Related Products -->
  <?php if (count($related_items) > 0): ?>
  <div class="related-section">
     <h3 class="detail-title">함께 보면 좋은 상품</h3>
     <div class="related-grid">
       <?php foreach($related_items as $r): ?>
         <a href="/dokju/product_view.php?id=<?php echo $r['id']; ?>" class="product-card" style="text-decoration:none; border:none; background:transparent;">
           <div class="img-box" style="aspect-ratio:4/5; background:#f4f0e6; border-radius:4px; margin-bottom:16px;">
             <img src="<?php echo htmlspecialchars($r['image']); ?>" alt="<?php echo htmlspecialchars($r['product_name']); ?>" style="width:100%; height:100%; object-fit:contain; padding:20px;">
           </div>
           <div class="product-info" style="text-align:center;">
             <h3 class="name" style="font-size:16px; margin-bottom:6px; color:#2b2b2b;"><?php echo htmlspecialchars($r['product_name']); ?></h3>
             <p class="price" style="font-size:16px; color:#2b2b2b;"><strong><?php echo number_format($r['price']); ?></strong>원</p>
           </div>
         </a>
       <?php endforeach; ?>
     </div>
  </div>
  <?php endif; ?>

  <script>
    const MAX_STOCK = <?php echo isset($item['stock']) ? $item['stock'] : 999; ?>;

    function updateQty(change) {
       var input = document.getElementById('qty');
       var val = parseInt(input.value);
       val += change;
       if(val < 1) val = 1;
       if(val > MAX_STOCK) {
           alert('남은 재고가 부족합니다. (최대 '+MAX_STOCK+'개)');
           val = MAX_STOCK;
       }
       input.value = val;
    }
    
    function addToCart() {
        var qty = parseInt(document.getElementById('qty').value);
        var item = {
            id: <?php echo $item['id']; ?>,
            name: "<?php echo addslashes($item['product_name']); ?>",
            price: <?php echo $item['price']; ?>,
            image: "<?php echo addslashes($item['image']); ?>",
            qty: qty
        };
        
        // LocalStorage Cart Logic
        const CART_KEY = 'dokju_cart';
        let cart = JSON.parse(localStorage.getItem(CART_KEY)) || [];
        
        // Check if exists
        let existing = cart.find(c => c.id === item.id);
        if(existing) {
            existing.qty += item.qty;
        } else {
            cart.push(item);
        }
        
        localStorage.setItem(CART_KEY, JSON.stringify(cart));
        
        alert('장바구니에 [ ' + item.name + ' ] ' + qty + '개를 담았습니다.\n장바구니 페이지에서 확인하세요.');
    }
    
    function buyNow() {
        var qty = parseInt(document.getElementById('qty').value);
        if(confirm('바로 구매하시겠습니까? (장바구니에 담고 이동합니다)')) {
            addToCart();
            location.href = '/dokju/cart.php';
        }
    }


    function requestRestock(pid, mode) {
        mode = mode || 'apply';
        const msg = (mode === 'cancel') ? '알림 신청을 취소하시겠습니까?' : '재입고 시 알림을 받으시겠습니까?';
        
        if(!confirm(msg)) return;
        
        const formData = new FormData();
        formData.append('product_id', pid);
        formData.append('mode', mode);
        
        fetch('/dokju/ajax_restock.php', { method: 'POST', body: formData })
        .then(res => res.text())
        .then(res => {
            const r = res.trim();
            if(r === 'success') {
                alert('알림이 신청되었습니다.');
                location.reload();
            } else if(r === 'cancelled') {
                alert('알림 신청이 취소되었습니다.');
                location.reload();
            } else if(r === 'duplicate') {
                alert('이미 신청하셨습니다.');
                location.reload();
            } else if(r === 'login_required') { 
                alert('로그인이 필요합니다.'); 
                location.href='/dokju/login.php'; 
            } else {
                alert('오류가 발생했습니다.');
            }
        });
    }
  </script>

</main>

<?php include './include/footer.php'; ?>
