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
      <div style="position:relative;">
          <input type="password" name="pw" id="pwInput" class="input-field" placeholder="비밀번호" required style="padding-right:45px;">
          <button type="button" onclick="togglePassword()" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#888; padding:5px; display:flex; align-items:center;">
              <svg id="eyeIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
              </svg>
              <svg id="eyeOffIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                  <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                  <line x1="1" y1="1" x2="23" y2="23"></line>
              </svg>
          </button>
      </div>
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
    // Validate Nickname
    // Korean >= 2 chars, English >= 4 chars
    // Max Length: Korean 10 chars, English 16 chars
    const isKorean = /[가-힣]/.test(nickname);
    
    if(isKorean) {
        if(nickname.length < 2) {
            alert('한글 닉네임은 2글자 이상이어야 합니다.');
            return false;
        }
        if(nickname.length > 10) { // Limit Korean to 10
            alert('한글 닉네임은 최대 10글자까지 가능합니다.');
            return false;
        }
    } else {
        if(nickname.length < 4) {
            alert('영문 닉네임은 4글자 이상이어야 합니다.');
            return false;
        }
        if(nickname.length > 16) { // Limit English to 16
            alert('영문 닉네임은 최대 16글자까지 가능합니다.');
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

function togglePassword() {
    const pwInput = document.getElementById('pwInput');
    const eyeIcon = document.getElementById('eyeIcon');
    const eyeOffIcon = document.getElementById('eyeOffIcon');
    
    if (pwInput.type === 'password') {
        pwInput.type = 'text';
        eyeIcon.style.display = 'none';
        eyeOffIcon.style.display = 'block';
    } else {
        pwInput.type = 'password';
        eyeIcon.style.display = 'block';
        eyeOffIcon.style.display = 'none';
    }
}
</script>

<?php include './include/footer.php'; ?>
