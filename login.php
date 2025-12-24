<?php 
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
include './include/db_connect.php'; 

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['mode'])) {
        // --- Find ID / PW Handling ---
        if($_POST['mode'] == 'find_id') {
             $name = $_POST['name'];
             $email = $_POST['email'];
             $stmt = $conn->prepare("SELECT userid FROM users WHERE name=? AND email=?");
             $stmt->bind_param("ss", $name, $email);
             $stmt->execute();
             $res = $stmt->get_result();
             if($row = $res->fetch_assoc()) {
                 echo "<script>alert('회원님의 아이디는 [ " . $row['userid'] . " ] 입니다.');</script>";
             } else {
                 echo "<script>alert('일치하는 회원 정보를 찾을 수 없습니다.');</script>";
             }
        } elseif($_POST['mode'] == 'find_pw') {
             $userid = $_POST['userid'];
             $name = $_POST['name'];
             $phone = $_POST['phone'];
             
             // Verify
             $stmt = $conn->prepare("SELECT id FROM users WHERE userid=? AND name=? AND phone=?");
             $stmt->bind_param("sss", $userid, $name, $phone);
             $stmt->execute();
             $res = $stmt->get_result();
             
             if($res->num_rows > 0) {
                 // Reset PW to '1234'
                 $new_pw = password_hash('1234', PASSWORD_DEFAULT);
                 $up = $conn->prepare("UPDATE users SET password=? WHERE userid=?");
                 $up->bind_param("ss", $new_pw, $userid);
                 $up->execute();
                 echo "<script>alert('비밀번호가 [ 1234 ] 로 초기화되었습니다. 로그인 후 변경해주세요.');</script>";
             } else {
                 echo "<script>alert('정보가 일치하지 않습니다.');</script>";
             }
        }
    } else {
        // --- Login Handling ---
        $userid = $_POST['userid'];
        $pw = $_POST['pw'];
        
        // Check if role column exists (prevent crashing if migration not run)
        // Ideally schema should be updated. Assuming schema update:
        $stmt = $conn->prepare("SELECT password, name, nickname, role FROM users WHERE userid = ?");
        // Fallback if query fails will be handled by exception usually, but let's try standard approach.
        // Recover if column missing logic is too complex for login.php. We assume migration.
        
        $stmt->bind_param("s", $userid);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($row = $result->fetch_assoc()) {
            if(password_verify($pw, $row['password'])) {
                // Login Success
                $_SESSION['userid'] = $userid;
                $_SESSION['role'] = $row['role'] ?? 'user';
                $_SESSION['nickname'] = !empty($row['nickname']) ? $row['nickname'] : $row['name'];
                
                // Hardcode admin role for 'admin' user just in case
                if ($userid === 'admin') {
                    $_SESSION['role'] = 'admin';
                }

                // Auto Login
                if(isset($_POST['auto_login'])) {
                    setcookie('dokju_auto_login', $userid, time() + (86400 * 30), "/");
                }
                
                $display_name = !empty($row['nickname']) ? $row['nickname'] : $row['name'];
                echo "<script>
                        localStorage.setItem('dokju_current_user', '$display_name');
                        alert('$display_name 님 환영합니다.');
                        location.href='/dokju/index.php';
                      </script>";
                exit;
            } else {
                echo "<script>alert('비밀번호가 일치하지 않습니다.');</script>";
            }
        } else {
            echo "<script>alert('존재하지 않는 회원입니다.');</script>";
        }
    }
}
include './include/header.php'; 
?>
<link rel="stylesheet" href="/dokju/css/member.css?v=<?php echo time(); ?>">

<main class="member-container">
  <h2 class="member-title">LOGIN</h2>
  
  <form class="member-form" method="POST" action="">
    <div class="input-group">
      <label class="input-label">아이디</label>
      <input type="text" name="userid" class="input-field" placeholder="아이디를 입력해주세요" required>
    </div>
    <div class="input-group">
      <label class="input-label">비밀번호</label>
      <div style="position:relative;">
          <input type="password" name="pw" id="pwInput" class="input-field" placeholder="비밀번호를 입력해주세요" required style="padding-right:45px;">
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
    
    <!-- Login Options -->
    <div class="login-options" style="display:flex; gap:15px; margin-bottom:30px; align-items:center;">
        <label class="checkbox-label" style="display:flex; align-items:center; gap:6px; font-size:14px; color:#555; cursor:pointer; user-select:none;">
            <input type="checkbox" id="save_id" style="width:16px; height:16px; margin:0; cursor:pointer;"> 
            아이디 저장
        </label>
        <label class="checkbox-label" style="display:flex; align-items:center; gap:6px; font-size:14px; color:#555; cursor:pointer; user-select:none;">
            <input type="checkbox" name="auto_login" value="1" style="width:16px; height:16px; margin:0; cursor:pointer;"> 
            자동 로그인
        </label>
    </div>
    
    <button type="submit" class="btn-submit" onclick="handleLoginSubmit()">로그인</button>
  </form>

  <div class="social-login">
      <a href="/dokju/login_kakao.php" class="btn-social btn-kakao">카카오 로그인</a>
      <a href="/dokju/login_naver.php" class="btn-social btn-naver">네이버 로그인</a>
  </div>

  <div class="member-links" style="margin-top:30px; padding-top:20px; border-top:1px solid #eee;">
    <a href="/dokju/join.php" style="font-size:16px; font-weight:700; color:#2b2b2b; text-decoration:underline; margin-right:20px;">회원가입</a>
    <a href="#" onclick="toggleFind(); return false;" style="font-size:14px; color:#888;">아이디/비밀번호 찾기</a>
  </div>
  
  <script>
  // Save ID Logic
  window.addEventListener('DOMContentLoaded', () => {
      const savedId = localStorage.getItem('dokju_saved_id');
      if(savedId) {
          document.querySelector('input[name="userid"]').value = savedId;
          document.getElementById('save_id').checked = true;
      }
  });

  function handleLoginSubmit() {
      const idInput = document.querySelector('input[name="userid"]');
      const saveCheckbox = document.getElementById('save_id');
      
      if(saveCheckbox.checked) {
          localStorage.setItem('dokju_saved_id', idInput.value);
      } else {
          localStorage.removeItem('dokju_saved_id');
      }
  }

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
  
  <!-- Find Section -->
  <div id="find-section" style="display:none; margin-top:30px; border-top:1px solid #eee; padding-top:20px; text-align:left;">
      <div class="find-tabs" style="display:flex; gap:10px; margin-bottom:15px;">
        <button onclick="showFind('id')" class="btn-submit" style="padding:10px; margin:0; font-size:14px; background:#666;">아이디 찾기</button>
        <button onclick="showFind('pw')" class="btn-submit" style="padding:10px; margin:0; font-size:14px; background:#999;">비밀번호 찾기</button>
      </div>
      
      <!-- Find ID Form -->
      <form id="find-id-form" method="POST" action="">
         <input type="hidden" name="mode" value="find_id">
         <div class="input-group">
            <label class="input-label">이름</label>
            <input type="text" name="name" class="input-field" placeholder="이름을 입력해주세요" required>
         </div>
         <div class="input-group">
            <label class="input-label">이메일</label>
            <input type="email" name="email" class="input-field" placeholder="이메일을 입력해주세요" required>
         </div>
         <button type="submit" class="btn-submit" style="padding:12px; font-size:16px;">아이디 찾기</button>
      </form>
      
      <!-- Find PW Form -->
      <form id="find-pw-form" method="POST" action="" style="display:none;">
         <input type="hidden" name="mode" value="find_pw">
         <div class="input-group">
            <label class="input-label">아이디</label>
            <input type="text" name="userid" class="input-field" placeholder="아이디를 입력해주세요" required>
         </div>
         <div class="input-group">
            <label class="input-label">이름</label>
            <input type="text" name="name" class="input-field" placeholder="이름을 입력해주세요" required>
         </div>
         <div class="input-group">
            <label class="input-label">휴대전화</label>
            <input type="tel" name="phone" class="input-field" placeholder="숫자만 입력해주세요" required>
         </div>
         <button type="submit" class="btn-submit" style="padding:12px; font-size:16px;">비밀번호 찾기</button>
      </form>
  </div>
</main>

<script>
function toggleFind() {
    const el = document.getElementById('find-section');
    el.style.display = (el.style.display === 'none') ? 'block' : 'none';
}
function showFind(type) {
    document.getElementById('find-id-form').style.display = (type === 'id') ? 'block' : 'none';
    document.getElementById('find-pw-form').style.display = (type === 'pw') ? 'block' : 'none';
}
</script>

<?php include './include/footer.php'; ?>
