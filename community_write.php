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
<style>
/* Write Page Styles */
.write-container { max-width: 900px; margin: 60px auto; padding: 0 20px; }
.write-title { font-size: 28px; font-weight: 700; margin-bottom: 30px; text-align: center; font-family: 'Inter', sans-serif; }

.write-form { background: #fff; border: 1px solid #ddd; padding: 40px; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.05); }

.form-group { margin-bottom: 25px; }
.form-label { display: block; font-size: 15px; font-weight: 600; margin-bottom: 8px; color: #333; }
.form-input, .form-select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 15px; box-sizing: border-box; transition: border-color 0.2s; }
.form-input:focus, .form-select:focus { border-color: #2b2b2b; outline: none; }

/* Editor Toolbar */
.toolbar { 
    display: flex; gap: 8px; padding: 12px; background: #fff; 
    border: 1px solid #e0e0e0; border-bottom: 1px solid #eee; border-radius: 8px 8px 0 0; 
    align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.02);
    /* Mobile Scrollable */
    overflow-x: auto;
    white-space: nowrap;
    -webkit-overflow-scrolling: touch;
}
.toolbar::-webkit-scrollbar { height: 4px; }
.toolbar::-webkit-scrollbar-thumb { background: #ddd; border-radius: 4px; }

.tool-btn { 
    background: transparent; border: none; width: 32px; height: 32px; border-radius: 4px; 
    cursor: pointer; color: #555; transition: all 0.2s; 
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.tool-btn:hover { background: #f0f0f0; color: #000; }
.tool-btn svg { width: 18px; height: 18px; fill: currentColor; }
.divider { width: 1px; height: 20px; background: #ddd; margin: 0 5px; }

/* Modal for Preview */
.modal { 
    display: none; 
    position: fixed; 
    top: 0; 
    left: 0; 
    width: 100%; 
    height: 100%; 
    background-color: rgba(0,0,0,0.6); 
    z-index: 99999; 
    justify-content: center; 
    align-items: center; 
    backdrop-filter: blur(3px);
}
.modal-content { 
    background: #fff; 
    padding: 40px; 
    width: 90%; 
    max-width: 800px; 
    max-height: 85vh; 
    overflow-y: auto; 
    border-radius: 12px; 
    position: relative; 
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); 
    animation: modalFadeIn 0.2s ease-out;
    font-family: 'Pretendard', 'Malgun Gothic', sans-serif !important; /* Force Readable Font */
}
.modal-content * {
    font-family: 'Pretendard', 'Malgun Gothic', sans-serif !important;
}
@keyframes modalFadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.modal-close { 
    position: absolute; 
    top: 20px; 
    right: 25px; 
    font-size: 28px; 
    cursor: pointer; 
    color: #999; 
    transition: color 0.2s;
    line-height: 1;
}
.modal-close:hover { color: #2b2b2b; }
.preview-title { font-size: 24px; font-weight: 700; margin-bottom: 20px; border-bottom: 2px solid #2b2b2b; padding-bottom: 15px; }
.preview-body { font-size: 16px; line-height: 1.7; min-height: 200px; word-break: break-all; }
.preview-body img { max-width: 100%; height: auto; border-radius: 4px; }

/* Editor Content: Force normal weight to make bold visible */
.editor-content {
    width: 100%; min-height: 400px; padding: 25px; border: 1px solid #e0e0e0; border-top: none; 
    border-radius: 0 0 8px 8px; font-size: 16px; line-height: 1.7; box-sizing: border-box; overflow-y: auto;
    background: #fff; font-weight: 400 !important; /* Force Normal Weight */
    font-family: 'Pretendard', 'Malgun Gothic', sans-serif; /* Clean Font */
}
.editor-content:focus { outline: none; }
.editor-content img { max-width: 100%; margin: 15px 0; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.btn-group {
    margin-top: 30px;
    display: flex;
    justify-content: center;
    gap: 15px;
}
.btn-cancel, .btn-preview, .btn-submit {
    padding: 12px 25px; border:none; border-radius:4px; font-size:15px; cursor:pointer; font-weight:500;
}
.btn-cancel { background:#f5f5f5; color:#555; border:1px solid #ddd; }
.btn-preview { background:#fff; color:#2b2b2b; border:1px solid #2b2b2b; }
.btn-submit { background:#2b2b2b; color:#fff; }

@media (max-width: 600px) {
    .btn-group { flex-direction: column-reverse; gap: 10px; }
    .btn-group button { width: 100%; padding: 14px; }
    .write-container { padding: 0 15px; margin: 30px auto; }
    .write-form { padding: 20px 15px; }
    .write-title { font-size: 22px; margin-bottom: 20px; }
}
</style>

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
