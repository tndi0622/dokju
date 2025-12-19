<?php
// Social Login Configuration

// Environment Detection
$is_local = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']);

// Base URL Setting
if ($is_local) {
    // Local environment
    $base_url = 'http://localhost/dokju';
} else {
    // Production environment
    $base_url = 'https://sake.dothome.co.kr/dokju';
}

// Kakao
$kakao_client_id = getenv('KAKAO_REST_API_KEY'); // REST API Key
$kakao_redirect_uri = $base_url . '/login_kakao.php';

// Naver
$naver_client_id = getenv('NAVER_CLIENT_ID');
$naver_client_secret = getenv('NAVER_CLIENT_SECRET');
$naver_redirect_uri = $base_url . '/login_naver.php';
$naver_state = 'RAMDOM_STATE_STRING'; // Generate a random string for state validation if possible
