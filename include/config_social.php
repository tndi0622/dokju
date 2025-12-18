<?php
// Social Login Configuration

// Kakao
$kakao_client_id = getenv('KAKAO_REST_API_KEY'); // REST API Key
$kakao_redirect_uri = 'http://localhost/dokju/login_kakao.php';

// Naver
$naver_client_id = getenv('NAVER_CLIENT_ID');
$naver_client_secret = getenv('NAVER_CLIENT_SECRET');
$naver_redirect_uri = 'http://localhost/dokju/login_naver.php';
$naver_state = 'RAMDOM_STATE_STRING'; // Generate a random string for state validation if possible
?>
