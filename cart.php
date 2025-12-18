<?php include './include/header.php'; ?>
<link rel="stylesheet" href="/dokju/css/cart.css?v=<?php echo time(); ?>">

<main class="cart-container">
  <h2 class="cart-title">장바구니</h2>

  <div id="cart-content-wrapper" class="cart-content">
    <!-- Left Section -->
    <div class="cart-left-section">
        <!-- Cart Items -->
        <div class="cart-list" id="cart-list">
          <!-- JS Render -->
        </div>
        
        <!-- Actions -->
        <div class="cart-actions-row">
            <button onclick="clearCart()" class="btn-clear-cart">장바구니 비우기</button>
        </div>
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
  let productStockInfo = {};

  function loadCart() {
      const cart = JSON.parse(localStorage.getItem(CART_KEY)) || [];
      if(cart.length === 0) {
          renderCartHtml([], {}); 
          return;
      }
      
      const ids = cart.map(c => c.id);
      
      // Fetch latest info (Stock, Price)
      fetch('/dokju/ajax_get_cart_info.php', {
          method: 'POST',
          body: JSON.stringify({ ids: ids })
      })
      .then(res => res.json())
      .then(data => {
          productStockInfo = data.items;
          renderCartHtml(cart, productStockInfo);
      });
  }
  
  function renderCartHtml(cart, dbItems) {
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
      let needsUpdate = false;
      
      cart.forEach((item, index) => {
          const dbItem = dbItems[item.id];
          if(!dbItem) return; // Product might be deleted
          
          // Sync Price & Update Stock check
          item.price = dbItem.price; // Update price just in case
          const stock = dbItem.stock;
          let isSoldOut = stock <= 0;
          let stockAlert = '';
          
          if(!isSoldOut && item.qty > stock) {
              item.qty = stock;
              needsUpdate = true;
              stockAlert = `재고 부족으로 ${stock}개로 조정되었습니다.`;
          }
          
          let itemTotal = item.price * item.qty;
          if(!isSoldOut) totalPrice += itemTotal;
          
          html += `
            <div class="cart-item ${isSoldOut ? 'sold-out' : ''}">
              <div class="cart-img">
                <a href="/dokju/product_view.php?id=${item.id}">
                    <img src="${dbItem.image}" alt="${dbItem.product_name}">
                </a>
              </div>
              <div class="cart-info">
                <a href="/dokju/product_view.php?id=${item.id}" class="cart-name">${dbItem.product_name}</a>
                <span class="cart-price">${item.price.toLocaleString()}원</span>
                ${isSoldOut ? '<span class="sold-out-badge">(품절)</span>' : ''}
              </div>
              <div class="cart-qty">
                 <button class="qty-btn" onclick="changeQty(${index}, -1)" ${isSoldOut?'disabled':''}>-</button>
                 <div class="qty-val">${item.qty}</div>
                 <button class="qty-btn" onclick="changeQty(${index}, 1)" ${isSoldOut?'disabled':''}>+</button>
              </div>
              <div class="cart-total-price">${isSoldOut ? '0원' : itemTotal.toLocaleString()+'원'}</div>
              <button class="btn-delete" onclick="removeItem(${index})">&times;</button>
              ${stockAlert ? `<div class="stock-alert">${stockAlert}</div>` : ''}
            </div>
          `;
      });
      
      if(needsUpdate) {
         localStorage.setItem(CART_KEY, JSON.stringify(cart));
      }
      
      listEl.innerHTML = html;
      
      // Update Summary
      const shipping = (totalPrice >= 50000 || totalPrice === 0) ? 0 : 3000;
      document.getElementById('sum-price').innerText = totalPrice.toLocaleString() + '원';
      document.getElementById('shipping-cost').innerText = shipping.toLocaleString() + '원';
      document.getElementById('total-price').innerText = (totalPrice + shipping).toLocaleString() + '원';
   }
  
  function changeQty(index, delta) {
      let cart = JSON.parse(localStorage.getItem(CART_KEY)) || [];
      if(cart[index]) {
          const item = cart[index];
          const dbItem = productStockInfo[item.id];
          const stock = dbItem ? dbItem.stock : 999;
          
          let newVal = item.qty + delta;
          if(newVal < 1) newVal = 1;
          
          if(newVal > stock) {
              alert('재고가 부족합니다. (최대 '+stock+'개)');
              newVal = stock;
          }
          
          cart[index].qty = newVal;
          localStorage.setItem(CART_KEY, JSON.stringify(cart));
          renderCartHtml(cart, productStockInfo);
      }
  }
  
  function removeItem(index) {
      if(!confirm('정말 삭제하시겠습니까?')) return;
      let cart = JSON.parse(localStorage.getItem(CART_KEY)) || [];
      cart.splice(index, 1);
      localStorage.setItem(CART_KEY, JSON.stringify(cart));
      loadCart(); // Reload to re-check
  }
  
  function clearCart() {
      if(!confirm('장바구니를 모두 비우시겠습니까?')) return;
      localStorage.removeItem(CART_KEY);
      loadCart();
  }
  
  function processOrder() {
      location.href = '/dokju/order.php';
  }

  // Load on init
  loadCart();
</script>

<?php include './include/footer.php'; ?>
