<?php
include './include/db_connect.php';
include './include/header.php';

$paymentKey = $_GET['paymentKey'];
$orderId = $_GET['orderId'];
$amount = $_GET['amount'];

$secretKey = getenv('TOSS_SECRET_KEY');
$credential = base64_encode($secretKey . ":");

// Call Toss API
$url = "https://api.tosspayments.com/v1/payments/confirm";
$data = json_encode([
    'paymentKey' => $paymentKey,
    'orderId' => $orderId,
    'amount' => $amount
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Basic $credential",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$json = json_decode($response, true);

if ($httpCode == 200) {
    // Success! Update Order Status to COMPLETED
    $stmt = $conn->prepare("UPDATE orders SET status = 'COMPLETED' WHERE order_uid = ?");
    $stmt->bind_param("s", $orderId);
    $stmt->execute();
    
    $success = true;
} else {
    // Fail
    $success = false;
    $message = $json['message'] ?? '결제 승인 중 오류가 발생했습니다.';
    $code = $json['code'] ?? '';
}
?>

<div style="max-width:600px; margin:100px auto; text-align:center; padding:0 20px;">
    <?php if($success): ?>
        <div style="margin-bottom:30px;">
            <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#4CAF50" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            </svg>
        </div>
        <h2 style="font-size:32px; font-weight:700; color:#2b2b2b; margin-bottom:20px;">주문이 완료되었습니다!</h2>
        <p style="color:#666; margin-bottom:40px; font-size:16px;">
            주문번호: <?php echo $json['orderId']; ?><br>
            결제금액: <strong><?php echo number_format($json['totalAmount']); ?>원</strong>
        </p>
        
        <a href="/dokju/shop.php" class="btn-home">쇼핑 계속하기</a>
        
        <script>
            // Clear Cart after successful payment
            localStorage.removeItem('dokju_cart');
        </script>
    <?php else: ?>
        <div style="margin-bottom:30px;">
             <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="#ff4d4f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="15" y1="9" x2="9" y2="15"></line>
                <line x1="9" y1="9" x2="15" y2="15"></line>
            </svg>
        </div>
        <h2 style="font-size:28px; font-weight:700; color:#2b2b2b; margin-bottom:20px;">결제에 실패했습니다</h2>
        <p style="color:#666; margin-bottom:10px;">
            <?php echo $message; ?>
        </p>
        <p style="color:#999; font-size:14px; margin-bottom:40px;">(Code: <?php echo $code; ?>)</p>
        
        <a href="/dokju/cart.php" class="btn-home" style="background:#666;">장바구니로 돌아가기</a>
    <?php endif; ?>
</div>

<style>
.btn-home {
    display: inline-block; 
    padding: 16px 40px; 
    background: #2b2b2b; 
    color: #fff; 
    text-decoration: none; 
    border-radius: 4px; 
    font-weight: 600;
    font-size: 16px;
    transition: background 0.3s;
}
.btn-home:hover {
    background: #444;
}
</style>

<?php include './include/footer.php'; ?>
