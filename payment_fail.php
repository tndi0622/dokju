<?php
$code = $_GET['code'] ?? '';
$message = $_GET['message'] ?? '사용자가 취소했거나 알 수 없는 오류입니다.';

include './include/header.php';
?>
<div style="max-width:600px; margin:100px auto; text-align:center; padding: 20px;">
    <div style="margin-bottom:30px;">
         <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#ff4d4f" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="15" y1="9" x2="9" y2="15"></line>
            <line x1="9" y1="9" x2="15" y2="15"></line>
        </svg>
    </div>
    <h2 style="font-size:28px; font-weight:700; color:#2b2b2b; margin-bottom:20px;">결제 요청 실패</h2>
    <p style="color:#666; margin-bottom:10px; font-size:16px;">
        <?php echo htmlspecialchars($message); ?>
    </p>
    <?php if($code): ?>
    <p style="color:#999; font-size:14px; margin-bottom:40px;">(Code: <?php echo htmlspecialchars($code); ?>)</p>
    <?php endif; ?>
    
    <a href="/dokju/cart.php" style="display:inline-block; padding:15px 30px; background:#2b2b2b; color:#fff; text-decoration:none; border-radius:4px; font-weight:bold;">장바구니로 돌아가기</a>
</div>
<?php include './include/footer.php'; ?>
