<?php if(session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <title>獨酒 | 일본 술 전문 쇼핑몰</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="/dokju/css/common.css?v=<?php echo time(); ?>">
</head>
<body>

<?php $page = basename($_SERVER['PHP_SELF']); ?>
<header>
  <div class="header-inner">
    <!-- Mobile: Left Hamburger -->
    <button class="hamburger-btn mobile-only" onclick="toggleMobileMenu()">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <!-- Logo (Center on Mobile, Left on PC via CSS) -->
    <div class="logo"><a href="/dokju/index.php"><img src="/dokju/images/logo.png" alt="獨酒 로고"></a></div>
    
    <!-- Mobile: Right Cart -->
    <a href="/dokju/cart.php" class="mobile-cart-btn mobile-only">🛒</a>

    <!-- PC Navigation -->
    <nav class="pc-nav">
      <a href="/dokju/index.php" class="<?php echo ($page == 'index.php') ? 'active' : ''; ?>">HOME</a>
      <a href="/dokju/intro.php" class="<?php echo ($page == 'intro.php') ? 'active' : ''; ?>">INTRODUCE</a>
      <a href="/dokju/shop.php" class="<?php echo ($page == 'shop.php') ? 'active' : ''; ?>">SHOP</a>
      <a href="/dokju/community.php" class="<?php echo (strpos($page, 'community') !== false) ? 'active' : ''; ?>">COMMUNITY</a>
    </nav>
    
    <!-- PC User Menu -->
    <div class="user-menu pc-user-menu" id="user-auth-menu">
       <!-- Filled by JS -->
       <a href="/dokju/login.php" style="text-decoration:none; color:#2b2b2b;">LOGIN</a>
       <a href="/dokju/cart.php" style="text-decoration:none; color:#2b2b2b; font-weight:bold; margin-left:20px;">CART</a>
    </div>
  </div>
</header>

<!-- Mobile Menu Overlay -->
<div id="mobileMenuOverlay" class="mobile-menu-overlay">
    <div class="mobile-menu-content">
        <div class="mobile-menu-header">
            <span class="mobile-title">MENU</span>
            <button class="close-btn" onclick="toggleMobileMenu()">&times;</button>
        </div>
        
        <div class="mobile-user-area" id="mobile-auth-menu">
            <!-- JS Injected -->
            <a href="/dokju/login.php" class="mobile-login-btn">로그인해주세요</a>
        </div>

        <nav class="mobile-nav-links">
            <a href="/dokju/index.php" class="<?php echo ($page == 'index.php') ? 'active' : ''; ?>">HOME</a>
            <a href="/dokju/intro.php" class="<?php echo ($page == 'intro.php') ? 'active' : ''; ?>">INTRODUCE</a>
            <a href="/dokju/shop.php" class="<?php echo ($page == 'shop.php') ? 'active' : ''; ?>">SHOP</a>
            <a href="/dokju/community.php" class="<?php echo (strpos($page, 'community') !== false) ? 'active' : ''; ?>">COMMUNITY</a>
        </nav>
    </div>
</div>

<script>
  function toggleMobileMenu() {
      const menu = document.getElementById('mobileMenuOverlay');
      menu.classList.toggle('active');
      
      if(menu.classList.contains('active')) {
          document.body.style.overflow = 'hidden';
      } else {
          document.body.style.overflow = '';
      }
  }

  function logoutHeader() {
      localStorage.removeItem('dokju_current_user');
      location.href = '/dokju/logout.php';
  }

  (function(){
      const pcMenu = document.getElementById('user-auth-menu');
      const mobileMenu = document.getElementById('mobile-auth-menu');
      
      // Auth Check & Nickname Fetch
      <?php 
        $current_user_name = '';
        $isAdmin = false;
        
        if(isset($_SESSION['userid'])) {
             if($_SESSION['userid'] === 'admin') {
                 $isAdmin = true;
                 $current_user_name = '관리자';
             } elseif(isset($conn)) {
                 $stmt = $conn->prepare("SELECT nickname, name FROM users WHERE userid = ?");
                 $stmt->bind_param("s", $_SESSION['userid']);
                 $stmt->execute();
                 $res = $stmt->get_result();
                 if($row = $res->fetch_assoc()) {
                     $current_user_name = !empty($row['nickname']) ? $row['nickname'] : $row['name'];
                 }
             }
        }
      ?>
      
      const dbUser = "<?php echo htmlspecialchars($current_user_name); ?>";
      if(dbUser) {
          localStorage.setItem('dokju_current_user', dbUser);
      }
      
      const user = dbUser || localStorage.getItem('dokju_current_user');
      const isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;
      let adminLink = isAdmin ? `<a href="/dokju/admin/dashboard.php" style="text-decoration:none; color:#ef6c00; margin-right:10px; font-weight:600;">⚙️ ADMIN</a>` : '';

      if(user) {
          // PC Menu Update
          if(pcMenu) {
              pcMenu.innerHTML = `
                <span style="color:#888; margin-right:15px; font-weight:500;">${user}님</span>
                <a href="/dokju/mypage.php" style="text-decoration:none; color:#2b2b2b; margin-right:10px;">MY PAGE</a>
                ${adminLink}
                <a href="javascript:void(0)" onclick="logoutHeader();" style="text-decoration:none; color:#aaa; font-size:12px; margin-right:20px;">LOGOUT</a>
                <span style="border-left:1px solid #ddd; height:12px; display:inline-block; vertical-align:middle; margin-right:20px;"></span>
                <a href="/dokju/cart.php" style="text-decoration:none; color:#fff; background:#2b2b2b; padding:6px 14px; border-radius:2px; font-size:13px;">CART</a>
              `;
              pcMenu.style.alignItems = "center";
              pcMenu.style.display = "flex";
          }
          
          // Mobile Menu Update
          if(mobileMenu) {
              let mobileAdmin = isAdmin ? `<a href="/dokju/admin/dashboard.php" class="mobile-user-link admin">관리자 페이지</a>` : '';
              mobileMenu.innerHTML = `
                  <div class="mobile-welcome">${user}님 환영합니다</div>
                  <div class="mobile-user-links">
                      <a href="/dokju/mypage.php" class="mobile-user-link">마이페이지</a>
                      <a href="/dokju/cart.php" class="mobile-user-link">장바구니</a>
                      ${mobileAdmin}
                  </div>
                  <button onclick="logoutHeader()" class="mobile-logout-btn">로그아웃</button>
              `;
          }
      } else {
          // Not Logged In
           if(mobileMenu) {
              mobileMenu.innerHTML = `
                  <p style="margin-bottom:15px; color:#666;">로그인이 필요합니다</p>
                  <a href="/dokju/login.php" class="mobile-login-btn">로그인 / 회원가입</a>
              `;
           }
      }
  })();

  // Close mobile menu on overlay click
  window.addEventListener('click', function(e) {
      const menu = document.getElementById('mobileMenuOverlay');
      if (e.target === menu && menu.classList.contains('active')) {
          menu.classList.remove('active');
          document.body.style.overflow = '';
      }
  });
</script>
