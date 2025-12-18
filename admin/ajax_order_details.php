<?php
include '../include/db_connect.php';

if(!isset($_GET['uid'])) exit('잘못된 요청');

$uid = $conn->real_escape_string($_GET['uid']);

// Get Order Info
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_uid = ?");
$stmt->bind_param("s", $uid);
$stmt->execute();
$ord = $stmt->get_result()->fetch_assoc();

if(!$ord) exit('주문 정보를 찾을 수 없습니다.');

// Get Items
$stmt_items = $conn->prepare("SELECT * FROM order_items WHERE order_uid = ?");
$stmt_items->bind_param("s", $uid);
$stmt_items->execute();
$items = $stmt_items->get_result();
?>
<div style="font-size:14px; line-height:1.6; color:#333;">
    <h4 style="margin-bottom:10px; font-size:16px; border-left:3px solid #333; padding-left:10px;">배송 정보</h4>
    <div style="background:#f9f9f9; padding:15px; border-radius:4px; margin-bottom:20px;">
        <strong>받는 분:</strong> <?php echo htmlspecialchars($ord['receiver_name']); ?><br>
        <strong>연락처:</strong> <?php echo htmlspecialchars($ord['receiver_phone']); ?><br>
        <strong>주소:</strong> <?php echo htmlspecialchars($ord['receiver_address']); ?>
    </div>

    <h4 style="margin-bottom:10px; font-size:16px; border-left:3px solid #333; padding-left:10px;">주문 상품</h4>
    <div style="border-top:1px solid #eee;">
        <?php while($item = $items->fetch_assoc()): ?>
        <div class="modal-item-row" style="display:flex; align-items:center; border-bottom:1px solid #eee; padding:15px 0;">
            <?php $img = !empty($item['image']) ? $item['image'] : '/dokju/images/sake_bottle.jpg'; ?>
            <img src="<?php echo htmlspecialchars($img); ?>" style="width:60px; height:60px; object-fit:contain; border:1px solid #eee; margin-right:15px; background:#fff;">
            <div>
                <div style="font-weight:bold; margin-bottom:4px;"><?php echo htmlspecialchars($item['product_name']); ?></div>
                <div style="color:#666;">
                    <?php echo number_format($item['price']); ?>원 &times; <?php echo $item['qty']; ?>개
                </div>
            </div>
            <div style="margin-left:auto; font-weight:bold;">
                <?php echo number_format($item['price'] * $item['qty']); ?>원
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    
    <div style="text-align:right; margin-top:20px; font-size:18px;">
        총 결제 금액: <strong style="color:#e74c3c;"><?php echo number_format($ord['total_amount']); ?>원</strong>
    </div>
</div>
