<?php
session_start();
include './include/db_connect.php';

// Check Auth
if(!isset($_SESSION['userid'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='/dokju/login.php';</script>";
    exit;
}

$userid = $_SESSION['userid'];

// Fetch User Data
$stmt = $conn->prepare("SELECT name, nickname, email, phone FROM users WHERE userid = ?");
$stmt->bind_param("s", $userid);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Handle Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $nickname = $_POST['nickname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    // Update Query - Password change moved to member_pw_change.php
    $update = $conn->prepare("UPDATE users SET name=?, nickname=?, email=?, phone=? WHERE userid=?");
    $update->bind_param("sssss", $name, $nickname, $email, $phone, $userid);
    
    if($update->execute()) {
        echo "<script>alert('회원정보가 수정되었습니다.'); location.href='/dokju/mypage.php';</script>";
    } else {
        echo "<script>alert('수정 실패: " . $conn->error . "');</script>";
    }
}
include './include/header.php';
?>
<link rel="stylesheet" href="/dokju/css/member.css?v=<?php echo time(); ?>">

<main class="member-container">
  <h2 class="member-title">정보 수정</h2>
  
  <form class="member-form" method="POST" action="" onsubmit="return validateEdit()">
    <div class="input-group">
      <label class="input-label">아이디</label>
      <input type="text" class="input-field" value="<?php echo $userid; ?>" disabled style="background:#f5f5f5;">
    </div>
    
    <div class="input-group">
      <label class="input-label">이름 (실명)</label>
      <input type="text" name="name" class="input-field" value="<?php echo $user['name']; ?>" required>
    </div>
    
    <div class="input-group">
      <label class="input-label">닉네임</label>
      <input type="text" name="nickname" class="input-field" value="<?php echo $user['nickname']; ?>" required>
    </div>
    
    <div class="input-group">
      <label class="input-label">이메일</label>
      <input type="email" name="email" class="input-field" value="<?php echo $user['email']; ?>" required>
    </div>
    
    <div class="input-group">
      <label class="input-label">휴대전화</label>
      <input type="tel" name="phone" id="phone" class="input-field" value="<?php echo $user['phone']; ?>">
    </div>
    
    <div class="input-group">
      <label class="input-label">비밀번호</label>
      <a href="/dokju/member_pw_change.php" class="btn-submit" style="display:block; text-align:center; background:#555; text-decoration:none; width: 100%; box-sizing: border-box;">비밀번호 변경</a>
    </div>
    
    <button type="submit" class="btn-submit">수정 완료</button>
    <a href="/dokju/mypage.php" class="btn-submit" style="display:block; text-align:center; background:#999; margin-top:10px; text-decoration:none;">취소</a>
  </form>
</main>
<script>
// Auto-format phone input
document.getElementById('phone').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});

function validateEdit() {
    const form = document.querySelector('.member-form');
    // Name is required but usually fine. Nickname is critical.
    const nickname = form.nickname.value.trim();
    
    // Validate Nickname (Same logic as Join)
    const isKorean = /[가-힣]/.test(nickname);
    if(isKorean) {
        if(nickname.length < 2) {
            alert('한글 닉네임은 2글자 이상이어야 합니다.');
            return false;
        }
        if(nickname.length > 10) {
            alert('한글 닉네임은 최대 10글자까지 가능합니다.');
            return false;
        }
    } else {
        if(nickname.length < 4) {
            alert('영문 닉네임은 4글자 이상이어야 합니다.');
            return false;
        }
        if(nickname.length > 16) {
            alert('영문 닉네임은 최대 16글자까지 가능합니다.');
            return false;
        }
    }
    return true;
}
</script>
<?php include './include/footer.php'; ?>
