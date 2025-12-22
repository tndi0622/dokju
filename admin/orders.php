<?php
session_start();
include '../include/db_connect.php';

// --- Admin Check (Simple) ---
if (!isset($_SESSION['userid']) || $_SESSION['userid'] !== 'admin') {
    echo "<script>alert('관리자만 접근 가능합니다.'); location.href='/dokju/index.php';</script>";
    exit;
}

// --- Status Update Logic ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_uid = $_POST['order_uid'];
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_uid = ?");
    $stmt->bind_param("ss", $status, $order_uid);
    if ($stmt->execute()) {
        echo "<script>alert('주문 상태가 변경되었습니다.'); location.href='orders.php';</script>";
        exit;
    } else {
        echo "<script>alert('오류 발생: " . $conn->error . "');</script>";
    }
}

// --- Fetch Orders ---
// Joining with users table for more info could be good, but orders table has receiver info.
$sql = "SELECT * FROM orders ORDER BY created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - 배송 관리</title>
    <link rel="stylesheet" href="/dokju/css/admin.css?v=<?php echo time(); ?>">
    <style>
        /* Default Desktop Style */
        .admin-table th, .admin-table td { white-space: nowrap; }

        .status-select {
            padding: 6px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #fff;
            color: #333;
            cursor: pointer;
        }
        .status-form {
            display: inline-block;
        }
        .order-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #fff;
            margin: 10% auto;
            padding: 30px;
            border-radius: 8px;
            width: 80%;
            max-width: 600px;
            position: relative;
        }
        .close-btn {
            position: absolute;
            top: 20px;
            right: 25px;
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close-btn:hover {
            color: #000;
        }
        .modal-item-row {
            display: flex;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding: 10px 0;
        }
        .modal-item-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            margin-right: 15px;
            background: #f9f9f9;
        }
        .badge-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            color: #fff;
            font-weight: bold;
        }
        .st-PENDING { background: #999; }
        .st-PAID { background: #27ae60; }
        .st-PREPARING { background: #f39c12; }
        .st-SHIPPING { background: #3498db; }
        .st-DELIVERED { background: #2c3e50; }
        .st-CANCELLED { background: #e74c3c; }

        /* Mobile Responsive Styles */
        @media (max-width: 900px) {
            /* Hide Sidebar Site Link */
            .nav-link-site { display: none !important; }

            /* Card Layout for Table */
            .admin-table { min-width: 0 !important; width: 100%; display: block; }
            .admin-table thead { display: none; }
            .admin-table tbody { display: block; width: 100%; }
            .admin-table tr {
                display: block;
                margin-bottom: 20px;
                border: 1px solid #ddd;
                border-radius: 8px;
                background: #fff;
                padding: 15px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            }
            .admin-table td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                border-bottom: 1px solid #f5f5f5;
                padding: 10px 0;
                text-align: right;
                font-size: 14px;
            }
            .admin-table td:last-child {
                border-bottom: none;
                flex-direction: column;
                align-items: flex-end;
                gap: 10px;
            }
            .admin-table td::before {
                content: attr(data-label);
                font-weight: bold;
                color: #555;
                text-align: left;
                margin-right: 15px;
                white-space: nowrap;
                flex-shrink: 0;
            }
            /* Adjust status form in card */
            .status-form { width: 100%; text-align: right; }
            .status-select { width: auto; min-width: 100px; }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <h1>Admin</h1>
                <p>관리자 패널</p>
            </div>
            <nav class="admin-nav">
                <ul>
                    <li><a href="/dokju/admin/dashboard.php"> 대시보드</a></li>
                    <li><a href="/dokju/admin/products.php"> 상품 관리</a></li>
                    <li><a href="/dokju/admin/users.php"> 회원 관리</a></li>
                    <li><a href="/dokju/admin/orders.php" class="active"> 배송 관리</a></li>
                    <li><a href="/dokju/admin/posts.php"> 커뮤니티 관리</a></li>
                    <li><a href="/dokju/index.php" class="nav-link-site" style="margin-top:20px; border-top:1px solid rgba(255,255,255,0.1); padding-top:20px;"> 사이트로 이동</a></li>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <h2>배송 관리</h2>
                <div class="admin-user">
                    <span>관리자님</span>
                </div>
            </header>

            <div class="content-box">
                <h3>주문 내역</h3>
                <div>
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>주문 번호</th>
                                <th>주문자 ID</th>
                                <th>수령인</th>
                                <th>결제 금액</th>
                                <th>주문 일시</th>
                                <th>상태</th>
                                <th>관리</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): 
                                    $d = new DateTime($row['created_at']);
                                    $fmt_date = $d->format('Y-m-d H:i');
                                    
                                    $st_map = [
                                        'PENDING' => '결제대기',
                                        'PAID' => '결제완료',
                                        'PREPARING' => '배송준비',
                                        'SHIPPING' => '배송중',
                                        'DELIVERED' => '배송완료',
                                        'CANCELLED' => '주문취소'
                                    ];
                                    $st_text = isset($st_map[$row['status']]) ? $st_map[$row['status']] : $row['status'];
                                ?>
                                <tr>
                                    <td data-label="주문 번호"><?php echo $row['order_uid']; ?></td>
                                    <td data-label="주문자 ID"><?php echo $row['userid']; ?></td>
                                    <td data-label="수령인"><?php echo $row['receiver_name']; ?></td>
                                    <td data-label="결제 금액"><?php echo number_format($row['total_amount']); ?>원</td>
                                    <td data-label="주문 일시"><?php echo $fmt_date; ?></td>
                                    <td data-label="상태">
                                        <span class="badge-status st-<?php echo $row['status']; ?>"><?php echo $st_text; ?></span>
                                    </td>
                                    <td data-label="관리">
                                        <div style="display:flex; gap:5px; align-items:center; justify-content: flex-end;">
                                            <button onclick="viewDetails('<?php echo $row['order_uid']; ?>')" style="padding:6px 12px; border:1px solid #ddd; background:#eee; cursor:pointer; margin-right:5px; white-space:nowrap;">상세</button>
                                            
                                            <form method="POST" action="" class="status-form" style="margin:0;">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="order_uid" value="<?php echo $row['order_uid']; ?>">
                                                <select name="status" class="status-select" onchange="if(confirm('상태를 변경하시겠습니까?')) this.form.submit(); else this.value='<?php echo $row['status']; ?>';">
                                                    <option value="PENDING" <?php if($row['status']=='PENDING') echo 'selected'; ?>>대기</option>
                                                    <option value="PAID" <?php if($row['status']=='PAID') echo 'selected'; ?>>완료</option>
                                                    <option value="PREPARING" <?php if($row['status']=='PREPARING') echo 'selected'; ?>>배송준비</option>
                                                    <option value="SHIPPING" <?php if($row['status']=='SHIPPING') echo 'selected'; ?>>배송중</option>
                                                    <option value="DELIVERED" <?php if($row['status']=='DELIVERED') echo 'selected'; ?>>배송완료</option>
                                                    <option value="CANCELLED" <?php if($row['status']=='CANCELLED') echo 'selected'; ?>>취소</option>
                                                </select>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align:center; padding:30px;">주문 내역이 없습니다.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Order Detail Modal -->
    <div id="orderModal" class="order-modal">
        <div class="modal-content">
            <span class="close-btn" onclick="closeModal()">&times;</span>
            <h3 style="margin-bottom:20px;">주문 상세 정보</h3>
            <div id="modalBody">
                <!-- Ajax Content will be loaded here -->
                로딩중...
            </div>
        </div>
    </div>

    <!-- Floating Action Button -->
    <a href="/dokju/index.php" class="fab-site-link" title="사이트로 이동">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
    </a>
    <style>
        .fab-site-link {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: #2b2b2b;
            color: #fff;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            z-index: 9999;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .fab-site-link:hover {
            transform: translateY(-5px);
            background: #444;
            box-shadow: 0 6px 16px rgba(0,0,0,0.4);
        }

    </style>
    
    <script>
    function viewDetails(orderUid) {
        document.getElementById('orderModal').style.display = 'block';
        document.getElementById('modalBody').innerHTML = '로딩중...';
        
        // Fetch Items
        fetch('/dokju/admin/ajax_order_details.php?uid=' + orderUid)
            .then(res => res.text())
            .then(html => {
                document.getElementById('modalBody').innerHTML = html;
            });
    }

    function closeModal() {
        document.getElementById('orderModal').style.display = 'none';
    }

    // Close on outside click
    window.onclick = function(event) {
        if (event.target == document.getElementById('orderModal')) {
            closeModal();
        }
    }
    </script>
</body>
</html>
