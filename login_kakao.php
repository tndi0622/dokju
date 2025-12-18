<?php
session_start();
include './include/db_connect.php';
include './include/config_social.php';

// 1. Authorization Code Received
if (isset($_GET['code'])) {
    $code = $_GET['code'];
    
    // 2. Get Access Token
    $token_url = "https://kauth.kakao.com/oauth/token";
    $params = [
        'grant_type' => 'authorization_code',
        'client_id' => $kakao_client_id,
        'redirect_uri' => $kakao_redirect_uri,
        'code' => $code
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $token_url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $token_data = json_decode($response, true);
    
    if (isset($token_data['access_token'])) {
        $access_token = $token_data['access_token'];
        
        // 3. Get User Info
        $user_url = "https://kapi.kakao.com/v2/user/me";
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
        
        if (isset($user_data['id'])) {
            $social_id = $user_data['id'];
            $nickname = $user_data['properties']['nickname'] ?? 'Kakao User';
            $social_type = 'kakao';
            
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
                $new_userid = "kakao_" . $social_id;
                // Ensure userid is not too long or duplicate (unlikely with this format but good to assume safely)
                // Password: random hash, they can't login with password unless they change it (which we might not support for social yet)
                $random_pw = password_hash(bin2hex(random_bytes(10)), PASSWORD_DEFAULT);
                
                $stmt_insert = $conn->prepare("INSERT INTO users (userid, password, name, nickname, social_type, social_id) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt_insert->bind_param("ssssss", $new_userid, $random_pw, $nickname, $nickname, $social_type, $social_id);
                
                if ($stmt_insert->execute()) {
                    $_SESSION['userid'] = $new_userid;
                     echo "<script>
                        localStorage.setItem('dokju_current_user', '$nickname');
                        alert('카카오 계정으로 가입되었습니다.');
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
        echo "<script>alert('토큰 발급 실패'); history.back();</script>";
    }
} else {
    // Redirect to Kakao Login Auth
    $auth_url = "https://kauth.kakao.com/oauth/authorize?client_id={$kakao_client_id}&redirect_uri={$kakao_redirect_uri}&response_type=code";
    header("Location: " . $auth_url);
    exit;
}
?>
