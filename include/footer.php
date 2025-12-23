<footer>
  <div class="footer-inner">
    <div class="footer-content">
      
      <div class="footer-info">
        <img src="/dokju/images/new_logo.png" alt="獨酒" style="width:100px; display:block; margin-bottom:24px;">
        <p style="margin:0; line-height:1.8; color:#555;">
          주소 : 서울시 ○○구 ○○로 00<br>
          전화번호 : 02-000-0000<br>
          이메일 : help@doksul.co.kr<br>
          <span style="display:block; margin-top:12px; font-size:14px; color:#888;">
            고객센터 운영시간 : 평일 10:00 ~ 17:00<br>
            (점심 12:00 ~ 13:00)
            <?php if(isset($_SESSION['userid']) && $_SESSION['userid'] === 'admin'): ?>
              <br><a href="/dokju/admin/dashboard.php" style="color:red; text-decoration:none; font-weight:bold; margin-top:10px; display:inline-block;">[관리자 페이지]</a>
            <?php endif; ?>
          </span>
        </p>
      </div>
    </div>
  </div>
</footer>


<!-- Scroll To Top Button -->
<button id="scrollToTopBtn" title="맨 위로" aria-label="맨 위로 가기">
  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M18 15l-6-6-6 6"/>
  </svg>
</button>

<style>
#scrollToTopBtn {
  display: none; /* Hidden by default */
  position: fixed;
  bottom: 30px;
  right: 30px;
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: #2b2b2b; /* Primary dark color */
  color: #fff;
  border: none;
  cursor: pointer;
  z-index: 1000;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  transition: all 0.3s ease;
  justify-content: center;
  align-items: center;
}

#scrollToTopBtn:hover {
  background: #444;
  transform: translateY(-3px);
  box-shadow: 0 6px 16px rgba(0,0,0,0.25);
}

/* Ensure flex display when visible */
#scrollToTopBtn.visible {
  display: flex !important;
  animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

@media (max-width: 768px) {
    #scrollToTopBtn {
        bottom: 20px;
        right: 20px;
        width: 44px;
        height: 44px;
    }
}
</style>

<script>
(function() {
  if(location.pathname.includes('/admin/')) return;

  const btn = document.getElementById('scrollToTopBtn');
  
  if(btn) {
      window.addEventListener('scroll', function() {
        if (window.scrollY > 300) {
          btn.classList.add('visible');
        } else {
          btn.classList.remove('visible');
        }
      });
      
      btn.addEventListener('click', function() {
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
      });
  }
})();
</script>

</body>
</html>
