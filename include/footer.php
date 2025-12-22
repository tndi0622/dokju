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

</body>
</html>
