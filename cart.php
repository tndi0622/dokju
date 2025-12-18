<?php include './include/header.php'; ?>
<link rel="stylesheet" href="/dokju/css/cart.css?v=<?php echo time(); ?>">

<main class="cart-container">
  <h2 class="cart-title">장바구니</h2>

  <div id="cart-content-wrapper" class="cart-content">
    <!-- Actions -->
    <div style="text-align:right; margin-bottom:15px;">
        <button onclick="clearCart()" style="padding:8px 14px; background:#fff; border:1px solid #ccc; color:#555; cursor:pointer; font-size:13px; border-radius:4px;">장바구니 비우기</button>
    </div>

    <!-- Cart Items -->
    <div class="cart-list" id="cart-list">
      <!-- JS will render items here -->
    </div>

    <!-- Summary -->
    <div class="cart-summary">
      <h3 class="summary-title">결제 예정 금액</h3>
      <div class="summary-row">
        <span>총 상품금액</span>
        <span class="val" id="sum-price">0원</span>
      </div>
      <div class="summary-row">
        <span>배송비</span>
        <span class="val" id="shipping-cost">0원</span>
      </div>
      <div class="summary-row total">
        <span>총 결제금액</span>
        <span class="val" id="total-price">0원</span>
      </div>
      <button class="btn-order" onclick="processOrder()">주문하기</button>
    </div>
  </div>
  
  <div id="empty-cart-msg" class="cart-empty" style="display:none;">
     <p>장바구니가 비어있습니다.</p>
     <a href="/dokju/shop.php" class="btn-go-shop">쇼핑하러 가기</a>
  </div>

</main>

<script>
  // Key for localStorage
  const CART_KEY = 'dokju_cart';

  function loadCart() {
      const cart = JSON.parse(localStorage.getItem(CART_KEY)) || [];
      const listEl = document.getElementById('cart-list');
      const wrapEl = document.getElementById('cart-content-wrapper');
      const emptyEl = document.getElementById('empty-cart-msg');
      
      if(cart.length === 0) {
          wrapEl.style.display = 'none';
          emptyEl.style.display = 'block';
          return;
      }
      
      wrapEl.style.display = 'flex';
      emptyEl.style.display = 'none';
      
      let html = '';
      let totalPrice = 0;
      
      cart.forEach((item, index) => {
          let itemTotal = item.price * item.qty;
          totalPrice += itemTotal;
          
          html += `
            <div class="cart-item">
              <div class="cart-img">
                <img src="${item.image}" alt="${item.name}">
              </div>
              <div class="cart-info">
                <a href="/dokju/product_view.php?id=${item.id}" class="cart-name">${item.name}</a>
                <span class="cart-price">${item.price.toLocaleString()}원</span>
              </div>
              <div class="cart-qty">
                 <button class="qty-btn" onclick="changeQty(${index}, -1)">-</button>
                 <div class="qty-val">${item.qty}</div>
                 <button class="qty-btn" onclick="changeQty(${index}, 1)">+</button>
              </div>
              <div class="cart-total-price">${itemTotal.toLocaleString()}원</div>
              <button class="btn-delete" onclick="removeItem(${index})">&times;</button>
            </div>
          `;
      });
      
      listEl.innerHTML = html;
      
      // Update Summary
      const shipping = totalPrice >= 50000 ? 0 : 3000;
      document.getElementById('sum-price').innerText = totalPrice.toLocaleString() + '원';
      document.getElementById('shipping-cost').innerText = shipping.toLocaleString() + '원';
      document.getElementById('total-price').innerText = (totalPrice + shipping).toLocaleString() + '원';
  }
  
  function changeQty(index, delta) {
      let cart = JSON.parse(localStorage.getItem(CART_KEY)) || [];
      if(cart[index]) {
          cart[index].qty += delta;
          if(cart[index].qty < 1) cart[index].qty = 1;
          localStorage.setItem(CART_KEY, JSON.stringify(cart));
          loadCart();
      }
  }
  
  function removeItem(index) {
      if(!confirm('정말 삭제하시겠습니까?')) return;
      let cart = JSON.parse(localStorage.getItem(CART_KEY)) || [];
      cart.splice(index, 1);
      localStorage.setItem(CART_KEY, JSON.stringify(cart));
      loadCart();
  }
  
  function clearCart() {
      if(!confirm('장바구니를 모두 비우시겠습니까?')) return;
      localStorage.removeItem(CART_KEY);
      loadCart();
  }
  
  function processOrder() {
      // Check login via PHP session check is tricky in purely JS func without ajax?
      // Actually we'll let order.php handle the login check logic.
      location.href = '/dokju/order.php';
  }

  // Load on init
  loadCart();
</script>

<?php include './include/footer.php'; ?>
