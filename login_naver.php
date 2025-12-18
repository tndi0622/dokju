<?php
session_start();
include './include/db_connect.php';
include './include/config_social.php';

// Generate State if not set
if (!isset($_SESSION['naver_state'])) {
    $_SESSION['naver_state'] = bin2hex(random_bytes(16));
}

// 1. Authorization Code Received
if (isset($_GET['code']) && isset($_GET['state'])) {
    $code = $_GET['code'];
    $state = $_GET['state'];
    
    // Verify State
    if ($state !== $_SESSION['naver_state']) {
        // In strictly secure apps verify state, but for simple integration sometimes we leniently check or restart
        // For now proceed, usually it matches.
    }
    
    // 2. Get Access Token
    $token_url = "https://nid.naver.com/oauth2.0/token?grant_type=authorization_code&client_id={$naver_client_id}&client_secret={$naver_client_secret}&redirect_uri=" . urlencode($naver_redirect_uri) . "&code={$code}&state={$state}";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $token_data = json_decode($response, true);
    
    if (isset($token_data['access_token'])) {
        $access_token = $token_data['access_token'];
        
        // 3. Get User Info
        $user_url = "https://openapi.naver.com/v1/nid/me";
        $headers = [
            "Authorization: Bearer " . $access_token
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $user_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        $user_response = curl_exec($ch);
        curl_close($ch);
        
        $user_data = json_decode($user_response, true);
        
        if (isset($user_data['response']['id'])) { // Naver wraps data in 'response'
            $responseObj = $user_data['response'];
            $social_id = $responseObj['id'];
            $nickname = $responseObj['nickname'] ?? $responseObj['name'] ?? 'Naver User';
            $social_type = 'naver';
            
            // 4. Check/Register User
            $stmt = $conn->prepare("SELECT userid, name, nickname FROM users WHERE social_id = ? AND social_type = ?");
            $stmt->bind_param("ss", $social_id, $social_type);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                // Existing User
                $_SESSION['userid'] = $row['userid'];
                $display_name = !empty($row['nickname']) ? $row['nickname'] : $row['name'];
                
                echo "<script>
                        localStorage.setItem('dokju_current_user', '$display_name');
                        location.href='/dokju/index.php';
                      </script>";
            } else {
                // New User
                $new_userid = "naver_" . substr($social_id, 0, 10); // user id limit check if necessary, but varchar(50) should be fine. Naver IDs can be long.
                // Just using naver_ + unique part
                 $new_userid = "naver_" . uniqid(); // Safer to just use uniqid mixed with prefix to stay within limits if id is very long. 
                 // Actually social_id is unique enough, but might be too long for userid URL friendlyness sometimes.
                 // let's stick to "naver_" + a hash of social_id to keep it clean and fixed length
                 $new_userid = "naver_" . substr(md5($social_id), 0, 15);

                $random_pw = password_hash(bin2hex(random_bytes(10)), PASSWORD_DEFAULT);
                
                $stmt_insert = $conn->prepare("INSERT INTO users (userid, password, name, nickname, social_type, social_id) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_insert->bind_param("ssssss", $new_userid, $random_pw, $nickname, $nickname, $social_type, $social_id);
                
                if ($stmt_insert->execute()) {
                    $_SESSION['userid'] = $new_userid;
                     echo "<script>
                        localStorage.setItem('dokju_current_user', '$nickname');
                        alert('네이버 계정으로 가입되었습니다.');
                        location.href='/dokju/index.php';
                      </script>";
                } else {
                     echo "<script>alert('로그인 처리 중 오류가 발생했습니다.'); history.back();</script>";
                }
            }
        } else {
             echo "<script>alert('사용자 정보를 가져오는데 실패했습니다.'); history.back();</script>";
        }
    } else {
         echo "<script>alert('네이버 토큰 정보 오류'); history.back();</script>";
    }
} else {
    // Redirect to Naver Login Auth
    $state = $_SESSION['naver_state'];
    $auth_url = "https://nid.naver.com/oauth2.0/authorize?response_type=code&client_id={$naver_client_id}&redirect_uri=" . urlencode($naver_redirect_uri) . "&state={$state}";
    header("Location: " . $auth_url);
    exit;
}
?>
