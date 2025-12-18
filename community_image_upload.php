<?php
// community_image_upload.php
header('Content-Type: application/json; charset=utf-8');

if(isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
    $upload_dir = './uploads/';
    
    // Check dir
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // Allow extensions
    $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    $filename = $_FILES['file']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    if(!in_array($ext, $allowed)) {
        echo json_encode(['success' => false, 'message' => '허용되지 않는 파일 형식입니다.']);
        exit;
    }
    
    // Rename
    $new_filename = uniqid() . '_' . time() . '.' . $ext;
    $source = $_FILES['file']['tmp_name'];
    $destination = $upload_dir . $new_filename;
    
    // Resize & Compress Settings
    $max_width = 1200; // 가로 최대 1200px
    $quality = 80;     // 압축 품질 80%
    
    // Check if GD library is available
    if (extension_loaded('gd')) {
        list($width, $height, $type) = getimagesize($source);
        
        // 리사이징 필요 여부 확인
        if ($width > $max_width) {
            $new_width = $max_width;
            $new_height = floor($height * ($max_width / $width));
        } else {
            $new_width = $width;
            $new_height = $height;
        }
        
        $thumb = imagecreatetruecolor($new_width, $new_height);
        $source_img = null;
        
        switch ($type) {
            case IMAGETYPE_JPEG:
                $source_img = imagecreatefromjpeg($source);
                break;
            case IMAGETYPE_PNG:
                $source_img = imagecreatefrompng($source);
                imagealphablending($thumb, false);
                imagesavealpha($thumb, true);
                break;
            case IMAGETYPE_GIF:
                $source_img = imagecreatefromgif($source);
                break;
            case IMAGETYPE_WEBP:
                $source_img = imagecreatefromwebp($source);
                break;
        }
        
        if ($source_img) {
            // Resize
            imagecopyresampled($thumb, $source_img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
            
            // Save Optimized Image
            $save_success = false;
            if ($ext === 'png') {
                 $save_success = imagepng($thumb, $destination, 6); // 0-9 compression
            } elseif ($ext === 'webp') {
                 $save_success = imagewebp($thumb, $destination, $quality);
            } else {
                // JPG and others saved as JPG (or preserve original ext if supported)
                if ($ext === 'gif') {
                    $save_success = imagegif($thumb, $destination);
                } else {
                    $save_success = imagejpeg($thumb, $destination, $quality);
                }
            }
            
            imagedestroy($thumb);
            imagedestroy($source_img);
            
            if($save_success) {
                echo json_encode(['success' => true, 'url' => '/dokju/uploads/' . $new_filename]);
                exit;
            }
        }
    }
    
    // Fallback: Just move the file if optimization fails or GD is missing
    if(move_uploaded_file($source, $destination)) {
         echo json_encode(['success' => true, 'url' => '/dokju/uploads/' . $new_filename]);
    } else {
         echo json_encode(['success' => false, 'message' => '파일 저장 실패']);
    }

} else {
    echo json_encode(['success' => false, 'message' => '파일이 없거나 오류 발생: ' . ($_FILES['file']['error'] ?? 'Unknown error')]);
}
?>
