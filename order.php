<?php
include './include/db_connect.php';
include './include/header.php';

// Login Check
if(!isset($_SESSION['userid'])) {
    echo "<script>alert('로그인이 필요한 서비스입니다.'); location.href='/dokju/login.php';</script>";
    exit;
}

// Get User Info
$userid = $_SESSION['userid'];
$user = null;
$stmt = $conn->prepare("SELECT * FROM users WHERE userid = ?");
$stmt->bind_param("s", $userid);
$stmt->execute();
$result = $stmt->get_result();
if($result->num_rows > 0) {
    $user = $result->fetch_assoc();
}
?>

<style>
.order-container { max-width: 800px; margin: 80px auto; padding: 0 20px; }
.order-title { font-size: 32px; font-weight: 700; margin-bottom: 40px; text-align: center; }
.section-title { font-size: 20px; font-weight: 600; margin-bottom: 15px; border-bottom: 2px solid #2b2b2b; padding-bottom: 10px; margin-top: 40px; }

.order-items { border: 1px solid #ddd; border-top: none; }
.order-item { display: flex; align-items: center; padding: 15px; border-bottom: 1px solid #eee; }
.order-item img { width: 60px; height: 60px; object-fit: contain; margin-right: 15px; background: #f9f9f9; }
.order-item .info { flex: 1; }
.order-item .name { font-weight: 500; margin-bottom: 5px; display: block; }
.order-item .meta { font-size: 14px; color: #888; }
.order-item .price { font-weight: 600; }

.form-group { margin-bottom: 15px; }
.form-group label { display: block; font-size: 14px; color: #666; margin-bottom: 5px; }
.form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 15px; box-sizing: border-box; }

.total-summary { background: #f9f9f9; padding: 20px; border-radius: 4px; margin-top: 40px; }
.summary-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 15px; color: #555; }
.summary-row.final { font-size: 20px; font-weight: 700; color: #2b2b2b; margin-top: 20px; border-top: 1px solid #ddd; padding-top: 20px; }

.btn-pay { width: 100%; background: #2b2b2b; color: #fff; padding: 18px; font-size: 18px; font-weight: 600; border: none; border-radius: 4px; margin-top: 30px; cursor: pointer; transition: background 0.3s; }
.btn-pay:hover { background: #444; }
</style>

<main class="order-container">
    <h2 class="order-title">주문/결제</h2>

    <!-- 1. Product List -->
    <h3 class="section-title">주문 상품</h3>
    <div class="order-items" id="order-list">
        <!-- JS Render -->
    </div>

    <!-- 2. Shipping Info -->
    <h3 class="section-title">배송 정보</h3>
    <div class="shipping-form">
        <div class="form-group">
            <label>받는 분</label>
            <input type="text" id="order-name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" placeholder="이름을 입력하세요">
        </div>
        <div class="form-group">
            <label>연락처</label>
            <input type="text" id="order-phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="010-0000-0000">
        </div>
        <div class="form-group">
            <label>주소</label>
            <input type="text" id="order-address" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" placeholder="배송지 주소를 입력하세요">
        </div>
    </div>

    <!-- 3. Payment Summary -->
    <div class="total-summary">
        <div class="summary-row">
            <span>총 상품금액</span>
            <span id="sum-price">0원</span>
        </div>
        <div class="summary-row">
            <span>배송비</span>
            <span id="shipping-cost">0원</span>
        </div>
        <div class="summary-row final">
            <span>최종 결제금액</span>
            <span id="total-price">0원</span>
        </div>
        <button class="btn-pay" onclick="requestPayment()">결제하기</button>
    </div>
</main>

<!-- Toss Payments SDK -->
<script src="https://js.tosspayments.com/v1/payment"></script>

<script>
    const clientKey = '<?php echo getenv('TOSS_CLIENT_KEY'); ?>'; // Toss Client Key
    const tossPayments = TossPayments(clientKey);
    const CART_KEY = 'dokju_cart';
    
    let cart = [];
    let totalPrice = 0;
    
    // Load Cart
    function loadOrder() {
        cart = JSON.parse(localStorage.getItem(CART_KEY)) || [];
        const listEl = document.getElementById('order-list');
        
        if(cart.length === 0) {
            alert('장바구니가 비어있습니다.');
            location.href = '/dokju/shop.php';
            return;
        }
        
        let html = '';
        let sumPrice = 0;
        
        cart.forEach(item => {
            let itemTotal = item.price * item.qty;
            sumPrice += itemTotal;
            
            html += `
                <div class="order-item">
                    <img src="${item.image}" alt="${item.name}">
                    <div class="info">
                        <span class="name">${item.name}</span>
                        <span class="meta">${item.price.toLocaleString()}원 / ${item.qty}개</span>
                    </div>
                    <span class="price">${itemTotal.toLocaleString()}원</span>
                </div>
            `;
        });
        
        listEl.innerHTML = html;
        
        // Cost calc
        const shipping = sumPrice >= 50000 ? 0 : 3000;
        totalPrice = sumPrice + shipping;
        
        document.getElementById('sum-price').innerText = sumPrice.toLocaleString() + '원';
        document.getElementById('shipping-cost').innerText = shipping.toLocaleString() + '원';
        document.getElementById('total-price').innerText = totalPrice.toLocaleString() + '원';
    }
    
    async function requestPayment() {
        // Validation
        const name = document.getElementById('order-name').value;
        const phone = document.getElementById('order-phone').value;
        const addr = document.getElementById('order-address').value;
        
        if(!name || !phone || !addr) {
            alert('배송 정보를 모두 입력해주세요.');
            return;
        }
        
        // Order Name (e.g. "닷사이 23 외 2건")
        let orderName = cart[0].name;
        if(cart.length > 1) {
            orderName += ' 외 ' + (cart.length - 1) + '건';
        }
        
        // Unique Order ID (Timestamp + Random)
        const orderId = 'ORDER_' + new Date().getTime() + '_' + Math.random().toString(36).substr(2, 9);
        
        // 1. Save Order to DB
        try {
            const response = await fetch('/dokju/save_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    orderId: orderId,
                    amount: totalPrice,
                    items: cart,
                    receiver: { name, phone, address: addr }
                })
            });
            
            const result = await response.json();
            
            if(!result.success) {
                alert('주문 생성 중 오류가 발생했습니다: ' + result.message);
                return;
            }
            
        } catch(e) {
            console.error(e);
            alert('서버 통신 중 오류가 발생했습니다.');
            return;
        }
        
        // 2. Request Toss Payment
        tossPayments.requestPayment('카드', {
            amount: totalPrice,
            orderId: orderId,
            orderName: orderName,
            customerName: name,
            successUrl: window.location.origin + '/dokju/payment_success.php',
            failUrl: window.location.origin + '/dokju/payment_fail.php',
        })
        .catch(function (error) {
            if (error.code === 'USER_CANCEL') {
                // User canceled
            } else if (error.code === 'INVALID_CARD_COMPANY') {
                // Invalid card
            }
            alert(error.message);
        });
    }
    
    loadOrder();
</script>

<?php include './include/footer.php'; ?>
