<?php
session_start();
session_destroy();
?>
<script>
    localStorage.removeItem('dokju_current_user');
    alert('로그아웃 되었습니다.');
    location.href = '/dokju/index.php';
</script>
