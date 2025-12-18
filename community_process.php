<?php
include './include/db_connect.php';
session_start();

$action = $_REQUEST['mode'] ?? '';

// Check Login for actions requiring auth
if (!in_array($action, ['']) && !isset($_SESSION['userid'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='/dokju/login.php';</script>";
    exit;
}

$userid = $_SESSION['userid'] ?? '';

if ($action == 'write') {
    // Write Post
    $category = $_POST['category'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $image = $_POST['image'] ?? '';
    
    $stmt = $conn->prepare("INSERT INTO community_posts (userid, category, title, content, image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $userid, $category, $title, $content, $image);
    
    if($stmt->execute()) {
        echo "<script>alert('작성되었습니다.'); location.href='/dokju/community.php';</script>";
    } else {
        echo "<script>alert('오류가 발생했습니다.'); history.back();</script>";
    }


} elseif ($action == 'edit') {
    // Edit Post
    $id = $_POST['id'];
    $category = $_POST['category'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $image = $_POST['image'] ?? '';
    
    // Check permission
    $check = $conn->prepare("SELECT userid FROM community_posts WHERE id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $res = $check->get_result();
    $row = $res->fetch_assoc();
    
    if($row['userid'] !== $userid) {
        echo "<script>alert('권한이 없습니다.'); history.back();</script>";
        exit;
    }
    
    $stmt = $conn->prepare("UPDATE community_posts SET category=?, title=?, content=?, image=? WHERE id=?");
    $stmt->bind_param("ssssi", $category, $title, $content, $image, $id);
    
    if($stmt->execute()) {
        echo "<script>alert('수정되었습니다.'); location.href='/dokju/community_view.php?id=$id';</script>";
    } else {
        echo "<script>alert('오류가 발생했습니다.'); history.back();</script>";
    }


} elseif ($action == 'delete') {
    // Delete Post
    $id = $_GET['id'];
    
    // Check permission
    $check = $conn->prepare("SELECT userid FROM community_posts WHERE id = ?");
    $check->bind_param("i", $id);
    $check->execute();
    $res = $check->get_result();
    $row = $res->fetch_assoc();
    
    if($row['userid'] !== $userid) {
        echo "<script>alert('권한이 없습니다.'); history.back();</script>";
        exit;
    }
    
    $stmt = $conn->prepare("DELETE FROM community_posts WHERE id=?");
    $stmt->bind_param("i", $id);
    
    if($stmt->execute()) {
        echo "<script>alert('삭제되었습니다.'); location.href='/dokju/community.php';</script>";
    } else {
        echo "<script>alert('오류가 발생했습니다.'); history.back();</script>";
    }

} elseif ($action == 'comment') {
    // Write Comment
    $post_id = $_POST['post_id'];
    $content = $_POST['content'];
    
    $stmt = $conn->prepare("INSERT INTO community_comments (post_id, userid, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $post_id, $userid, $content);
    
    if($stmt->execute()) {
        echo "<script>location.href='/dokju/community_view.php?id=$post_id';</script>";
    } else {
        echo "<script>alert('댓글 등록 실패'); history.back();</script>";
    }

} elseif ($action == 'delete_comment') {
    // Delete Comment
    $comment_id = $_GET['id'];
    $post_id = $_GET['post_id'];
    
    // Check permission (Comment Owner OR Post Owner)
    $check = $conn->prepare("
        SELECT c.userid as comment_writer, p.userid as post_writer 
        FROM community_comments c 
        JOIN community_posts p ON c.post_id = p.id 
        WHERE c.id = ?
    ");
    $check->bind_param("i", $comment_id);
    $check->execute();
    $res = $check->get_result();
    $row = $res->fetch_assoc();
    
    if($row['comment_writer'] !== $userid && $row['post_writer'] !== $userid) {
        echo "<script>alert('권한이 없습니다.'); history.back();</script>";
        exit;
    }
    
    $stmt = $conn->prepare("DELETE FROM community_comments WHERE id=?");
    $stmt->bind_param("i", $comment_id);
    $stmt->execute();
    
    echo "<script>location.href='/dokju/community_view.php?id=$post_id';</script>";

} elseif ($action == 'like') {
    // Toggle Like
    $post_id = $_POST['post_id'];
    
    // Check if already liked
    $check = $conn->prepare("SELECT id FROM community_likes WHERE post_id=? AND userid=?");
    $check->bind_param("is", $post_id, $userid);
    $check->execute();
    $res = $check->get_result();
    
    if ($res->num_rows > 0) {
        // Unlike
        $conn->query("DELETE FROM community_likes WHERE post_id=$post_id AND userid='$userid'");
        $conn->query("UPDATE community_posts SET likes = likes - 1 WHERE id=$post_id");
        echo "unliked";
    } else {
        // Like
        $stmt = $conn->prepare("INSERT INTO community_likes (post_id, userid) VALUES (?, ?)");
        $stmt->bind_param("is", $post_id, $userid);
        if($stmt->execute()){
            $conn->query("UPDATE community_posts SET likes = likes + 1 WHERE id=$post_id");
            echo "liked";
        } else {
            echo "error";
        }
    }
}
?>
