<?php 
session_start();
include './include/db_connect.php'; 

if(!isset($_SESSION['userid'])) {
    echo "<script>alert('로그인이 필요합니다.'); location.href='/dokju/login.php';</script>";
    exit;
}

include './include/header.php'; 

// Edit Mode Logic
$mode = 'write';
$post_data = [];
$post_id = '';

if(isset($_GET['id'])) {
    $post_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM community_posts WHERE id = ?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if($res->num_rows > 0) {
        $post_data = $res->fetch_assoc();
        // Permission Check
        if($post_data['userid'] !== $_SESSION['userid'] && $_SESSION['userid'] !== 'admin') {
            echo "<script>alert('수정 권한이 없습니다.'); history.back();</script>";
            exit;
        }
        $mode = 'edit';
    } else {
        echo "<script>alert('존재하지 않는 게시글입니다.'); history.back();</script>";
        exit;
    }
}
?>
<link rel="stylesheet" href="/dokju/css/shop.css?v=<?php echo time(); ?>">
<link rel="stylesheet" href="/dokju/css/community_write.css?v=<?php echo time(); ?>">

<main class="write-container">
    <h2 class="write-title">글쓰기</h2>
    
    <form class="write-form" action="/dokju/community_process.php" method="POST" enctype="multipart/form-data" id="postForm">
        <input type="hidden" name="mode" value="<?php echo $mode; ?>">
        <?php if($mode == 'edit'): ?>
            <input type="hidden" name="id" value="<?php echo $post_id; ?>">
        <?php endif; ?>
        
        <div class="form-group">
            <label class="form-label">카테고리</label>
            <select name="category" class="form-select" id="category-select" required>
                <option value="">카테고리를 선택하세요</option>
                <option value="free" <?php echo (isset($post_data['category']) && $post_data['category']=='free')?'selected':''; ?>>자유</option>
                <option value="review" <?php echo (isset($post_data['category']) && $post_data['category']=='review')?'selected':''; ?>>리뷰</option>
                <option value="recommend" <?php echo (isset($post_data['category']) && $post_data['category']=='recommend')?'selected':''; ?>>추천</option>
                <option value="question" <?php echo (isset($post_data['category']) && $post_data['category']=='question')?'selected':''; ?>>질문</option>
            </select>
        </div>
        
        <div class="form-group">
            <label class="form-label">제목</label>
            <input type="text" name="title" class="form-input" placeholder="제목을 입력하세요" value="<?php echo isset($post_data['title']) ? htmlspecialchars($post_data['title']) : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label class="form-label">내용</label>
            
            <!-- Toolbar -->
            <div class="toolbar">
                <!-- Bold -->
                <button type="button" class="tool-btn" onclick="execCmd('bold')" title="굵게">
                    <svg viewBox="0 0 24 24"><path d="M15.6 10.79c.97-.67 1.65-1.77 1.65-2.79 0-2.26-1.75-4-4-4H7v14h7c2.09 0 3.85-1.75 3.85-4 0-1.25-.66-2.55-2.25-3.21zM10 6.5h3c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5h-3v-3zm3.5 9H10v-3h3.5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5z"/></svg>
                </button>
                <!-- Italic -->
                <button type="button" class="tool-btn" onclick="execCmd('italic')" title="기울임">
                    <svg viewBox="0 0 24 24"><path d="M10 4v3h2.21l-3.42 8H6v3h8v-3h-2.21l3.42-8H18V4z"/></svg>
                </button>
                <!-- Underline -->
                <button type="button" class="tool-btn" onclick="execCmd('underline')" title="밑줄">
                    <svg viewBox="0 0 24 24"><path d="M12 17c3.31 0 6-2.69 6-6V3h-2.5v8c0 1.93-1.57 3.5-3.5 3.5S8.5 12.93 8.5 11V3H6v8c0 3.31 2.69 6 6 6zm-7 2v2h14v-2H5z"/></svg>
                </button>
                
                <div class="divider"></div>
                
                <!-- Highlight -->
                 <button type="button" class="tool-btn" onclick="execCmd('hiliteColor', '#fff176')" title="형광펜">
                     <svg viewBox="0 0 24 24"><path d="M18.8 6c-.3-.3-.7-.3-1 0l-5.8 5.8-5.9-5.9 4.3-4.3c.4-.4.4-1 0-1.4l-1.4-1.4c-.4-.4-1-.4-1.4 0L1.2 5.2c-.4.4-.4 1 0 1.4L4 9.4l-1.8 1.8c-.4.4-.4 1 0 1.4l1.4 1.4c.1.1.2.1.3.1.2 0 .5-.1.6-.2L18.8 7c.3-.3.3-.7 0-1zm-6.2 7.2l1 1-6.1 6.1H4.6v-2.9l6.1-6.1z" /></svg>
                </button>
                
                <div class="divider"></div>

                <!-- Align Left -->
                <button type="button" class="tool-btn" onclick="execCmd('justifyLeft')" title="왼쪽 정렬">
                    <svg viewBox="0 0 24 24"><path d="M15 15H3v2h12v-2zm0-8H3v2h12V7zM3 13h18v-2H3v2zm0 8h18v-2H3v2zM3 5v2h18V5H3z"/></svg>
                </button>
                <!-- Align Center -->
                <button type="button" class="tool-btn" onclick="execCmd('justifyCenter')" title="가운데 정렬">
                     <svg viewBox="0 0 24 24"><path d="M7 15v2h10v-2H7zm-4 6h18v-2H3v2zm0-8h18v-2H3v2zm4-6v2h10V7H7zM3 3v2h18V3H3z"/></svg>
                </button>
                
                <div class="divider"></div>
                
                <!-- Image -->
                <button type="button" class="tool-btn" onclick="document.getElementById('image-input').click()" title="이미지 첨부" style="width:auto; padding:0 8px; gap:4px;">
                    <svg viewBox="0 0 24 24"><path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/></svg>
                    <span>이미지</span>
                </button>
                <input type="file" id="image-input" accept="image/*" onchange="uploadImage(this)">
            </div>
            
            <!-- Editor -->
            <div id="editor" class="editor-content" contenteditable="true" placeholder="내용을 입력하세요..."><?php echo isset($post_data['content']) ? $post_data['content'] : ''; ?></div>
            
            <!-- Hidden Input to store HTML content -->
            <textarea name="content" id="hiddenContent" style="display:none;"></textarea>
        </div>
        
        <div class="btn-group">
            <button type="button" class="btn-cancel" onclick="history.back()">취소</button>
            <button type="button" class="btn-preview" onclick="showPreview()">미리보기</button>
            <button type="button" class="btn-submit" onclick="submitPost()"><?php echo ($mode=='edit')?'수정완료':'작성완료'; ?></button>
        </div>
    </form>
</main>

<!-- Preview Modal -->
<div id="previewModal" class="modal">
    <div class="modal-content">
        <span class="modal-close" onclick="closePreview()">&times;</span>
        <h3 class="preview-title" id="previewTitle"></h3>
        <div class="preview-body" id="previewBody"></div>
    </div>
</div>

<script>
function execCmd(command) {
    document.execCommand(command, false, null);
    document.getElementById('editor').focus();
}

function uploadImage(input) {
    if(input.files && input.files[0]) {
        const file = input.files[0];
        const formData = new FormData();
        formData.append('file', file);
        
        // Upload to Server
        fetch('/dokju/community_image_upload.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                // Insert image at cursor
                document.getElementById('editor').focus();
                const html = `<img src="${data.url}" style="max-width:100%; margin: 10px 0;">`;
                document.execCommand('insertHTML', false, html);
            } else {
                alert('이미지 업로드 실패: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('이미지 업로드 중 오류가 발생했습니다.');
        });
        
        // Reset input for same file selection
        input.value = '';
    }
}

function submitPost() {
    const editor = document.getElementById('editor');
    const content = document.getElementById('hiddenContent');
    
    // Copy HTML from editor to textarea
    content.value = editor.innerHTML;
    
    // Validation
    const category = document.querySelector('select[name="category"]');
    if(!category.value) {
        alert('카테고리를 선택해주세요.');
        category.focus();
        return;
    }
    
    if(editor.innerText.trim() === '' && !editor.innerHTML.includes('<img')) {
        alert('내용을 입력하세요.');
        return;
    }
    
    if(confirm('등록하시겠습니까?')) {
        document.getElementById('postForm').submit();
    }
}

function showPreview() {
    const titleVal = document.querySelector('input[name="title"]').value;
    const bodyVal = document.getElementById('editor').innerHTML;
    
    if(!titleVal) { alert('제목을 입력해주세요.'); return; }
    
    document.getElementById('previewTitle').innerText = titleVal;
    document.getElementById('previewBody').innerHTML = bodyVal;
    
    document.getElementById('previewModal').style.display = 'flex';
}

function closePreview() {
    document.getElementById('previewModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('previewModal');
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>

<?php include './include/footer.php'; ?>
