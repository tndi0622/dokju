<?php 
include './include/db_connect.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userid = $_POST['userid'];
    $pw = password_hash($_POST['pw'], PASSWORD_DEFAULT);
    $name = $_POST['name'];
    $nickname = $_POST['nickname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    
    // Check Duplicate
    $check = $conn->prepare("SELECT id FROM users WHERE userid = ?");
    $check->bind_param("s", $userid);
    $check->execute();
    $check->store_result();
    
    if($check->num_rows > 0) {
        echo "<script>alert('이미 존재하는 아이디입니다.');</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (userid, password, name, nickname, email, phone) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $userid, $pw, $name, $nickname, $email, $phone);
        
        if ($stmt->execute()) {
             echo "<script>alert('회원가입 완료! 로그인해주세요.'); location.href='/dokju/login.php';</script>";
        } else {
             echo "<script>alert('오류가 발생했습니다.');</script>";
        }
    }
}
include './include/header.php'; 
?>
<link rel="stylesheet" href="/dokju/css/member.css?v=<?php echo time(); ?>">

<main class="member-container">
  <h2 class="member-title">JOIN</h2>
  
  <form class="member-form" method="POST" action="" onsubmit="return validateJoin()">
    <div class="input-group">
      <label class="input-label">아이디</label>
      <input type="text" name="userid" class="input-field" placeholder="로그인에 사용할 아이디" required>
    </div>
    <div class="input-group">
      <label class="input-label">비밀번호</label>
      <input type="password" name="pw" class="input-field" placeholder="비밀번호" required>
    </div>
    <div class="input-group">
      <label class="input-label">이름 (실명)</label>
      <input type="text" name="name" class="input-field" placeholder="실명을 입력해주세요" required>
    </div>
    <div class="input-group">
      <label class="input-label">닉네임</label>
      <input type="text" name="nickname" class="input-field" placeholder="표시될 닉네임" required>
    </div>
    <div class="input-group">
      <label class="input-label">이메일</label>
      <input type="email" name="email" class="input-field" placeholder="news@dokju.com" required>
    </div>
    <div class="input-group">
      <label class="input-label">휴대전화</label>
      <input type="tel" name="phone" class="input-field" placeholder="010-0000-0000">
    </div>
    
    <button type="submit" class="btn-submit">가입하기</button>
  </form>
</main>

<script>
function validateJoin() {
    const form = document.querySelector('.member-form');
    const nickname = form.nickname.value.trim();
    const email = form.email.value.trim();
    const phone = form.phone.value.trim();
    
    // Validate Nickname
    // Korean >= 2 chars OR English >= 4 chars
    const isKorean = /[가-힣]/.test(nickname);
    if(isKorean) {
        if(nickname.length < 2) {
            alert('한글 닉네임은 2글자 이상이어야 합니다.');
            return false;
        }
    } else {
        if(nickname.length < 4) {
            alert('영문 닉네임은 4글자 이상이어야 합니다.');
            return false;
        }
    }
    
    // Validate Email
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if(!emailPattern.test(email)) {
        alert('올바른 이메일 형식이 아닙니다.');
        return false;
    }
    
    // Validate Phone (Only numbers, 10-11 digits)
    if(!/^[0-9]+$/.test(phone)) {
        alert('전화번호는 숫자만 입력해주세요.');
        return false;
    }
    if(phone.length < 10 || phone.length > 11) {
        alert('전화번호는 올바른 형식이 아닙니다 (10~11자리 숫자).');
        return false;
    }
    
    return true;
}

// Auto-format phone input (remove non-digits)
document.querySelector('input[name="phone"]').addEventListener('input', function(e) {
    this.value = this.value.replace(/[^0-9]/g, '');
});
</script>

<?php include './include/footer.php'; ?>
