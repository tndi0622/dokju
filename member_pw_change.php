<?php
session_start();
include './include/db_connect.php';

// Check Auth
if(!isset($_SESSION['userid'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='/dokju/login.php';</script>";
    exit;
}

$userid = $_SESSION['userid'];

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $current_pw = $_POST['current_pw'];
    $new_pw = $_POST['new_pw'];
    $confirm_pw = $_POST['confirm_pw'];

    // 1. Validate inputs
    if (empty($current_pw) || empty($new_pw) || empty($confirm_pw)) {
        echo "<script>alert('모든 항목을 입력해주세요.');</script>";
    } elseif ($new_pw !== $confirm_pw) {
        echo "<script>alert('새 비밀번호가 일치하지 않습니다.');</script>";
    } else {
        // 2. Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE userid = ?");
        $stmt->bind_param("s", $userid);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($current_pw, $user['password'])) {
            // 3. Update to new password
            $hashed_pw = password_hash($new_pw, PASSWORD_DEFAULT);
            $update = $conn->prepare("UPDATE users SET password = ? WHERE userid = ?");
            $update->bind_param("ss", $hashed_pw, $userid);

            if ($update->execute()) {
                echo "<script>alert('비밀번호가 변경되었습니다. 다시 로그인해주세요.'); location.href='/dokju/logout.php';</script>";
                exit;
            } else {
                echo "<script>alert('비밀번호 변경 실패: " . $conn->error . "');</script>";
            }
        } else {
            echo "<script>alert('현재 비밀번호가 일치하지 않습니다.');</script>";
        }
    }
}

include './include/header.php';
?>
<link rel="stylesheet" href="/dokju/css/member.css?v=<?php echo time(); ?>">

<main class="member-container">
  <h2 class="member-title">비밀번호 변경</h2>
  
  <form class="member-form" method="POST" action="">
    <div class="input-group">
      <label class="input-label">현재 비밀번호</label>
      <input type="password" name="current_pw" class="input-field" required placeholder="현재 비밀번호를 입력하세요">
    </div>

    <div class="input-group">
      <label class="input-label">새 비밀번호</label>
      <input type="password" name="new_pw" class="input-field" required placeholder="새 비밀번호를 입력하세요">
    </div>

    <div class="input-group">
      <label class="input-label">새 비밀번호 확인</label>
      <input type="password" name="confirm_pw" class="input-field" required placeholder="새 비밀번호를 다시 입력하세요">
    </div>
    
    <button type="submit" class="btn-submit">변경 완료</button>
    <a href="/dokju/member_edit.php" class="btn-submit" style="display:block; text-align:center; background:#999; margin-top:10px; text-decoration:none;">취소</a>
  </form>
</main>
<?php include './include/footer.php'; ?>
