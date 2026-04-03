<?php
session_start();
require_once "../config/db.php";
require_once "../includes/csrf.php";

if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit();
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];
$msg = "";

/* ================= TEACHER UPLOAD NOTES ================= */

if(isset($_POST['upload']) && $role=="teacher"){
verify_csrf_token($_POST['csrf_token'] ?? '');

$title = mysqli_real_escape_string($conn,$_POST['title']);
$desc = mysqli_real_escape_string($conn,$_POST['description']);
$branch = $_POST['branch'];
$mode = $_POST['mode'];
$year = $_POST['year'];
$semester = isset($_POST['semester']) ? $_POST['semester'] : NULL;

if(isset($_FILES['file']['name']) && $_FILES['file']['name']!=""){

$ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

    $allowed_mimes = [
        'application/pdf', 
        'application/msword', 
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/zip',
        'application/x-zip-compressed'
    ];
    $allowed_ext = ['pdf','doc','docx','zip'];

    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['file']['tmp_name']);
        finfo_close($finfo);
    } elseif (function_exists('mime_content_type')) {
        $mime = mime_content_type($_FILES['file']['tmp_name']);
    } else {
        $mime = $_FILES['file']['type'];
    }

    if(!in_array($ext, $allowed_ext) || !in_array($mime, $allowed_mimes)){
        $msg="Invalid file type! Only PDF, DOCX, and ZIP allowed.";
    } elseif ($_FILES['file']['size'] > 5000000) {
        $msg="File too large! Max size is 5MB.";
    }else{

$filename = uniqid() . '_' . preg_replace('/[^A-Za-z0-9.\-]/', '_', basename($_FILES['file']['name']));
$path = "../uploads/".$filename;

if(move_uploaded_file($_FILES['file']['tmp_name'],$path)){

mysqli_query($conn,"INSERT INTO notes
(title,description,file_path,branch,mode,year,semester,uploaded_by)
VALUES
('$title','$desc','$filename','$branch','$mode','$year','$semester','$user_id')");

$msg="Notes uploaded successfully!";
}else{
$msg="Upload failed!";
}
}
}
}
?>

<?php require_once "../includes/header.php"; ?>
<?php require_once "../includes/sidebar.php"; ?>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="main-content">
        <div class="topbar">
            <div>
                <h1>Study Notes</h1>
                <p>Access and download course materials</p>
            </div>
        </div>

        <?php if($msg!="") echo "<p class='success'>$msg</p>"; ?>

        <!-- ================= TEACHER UPLOAD FORM ================= -->
        <?php if($role=="teacher"): ?>
        <div class="table-card" style="margin-bottom:30px;">
            <div class="option-box" style="margin:0; box-shadow:none;">
                <h3>Upload Notes</h3>
                <form method="post" enctype="multipart/form-data" id="notes-form" style="max-width:600px;">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="text" name="title" placeholder="Title" required>
                    
                    <div id="editor-container" style="height: 150px; background: white; margin-bottom: 20px; border-radius: 8px;"></div>
                    <input type="hidden" name="description" id="hidden_desc">
                    
                    <select name="branch" required>
                    <option value="">Select Branch</option>
                    <option value="computer">Computer</option>
                    <option value="electrical">Electrical</option>
                    <option value="electronics">Electronics</option>
                    <option value="mechanical">Mechanical</option>
                    <option value="civil">Civil</option>
                    </select>
                    <select name="mode" id="modeSelect" required>
                    <option value="">Select Mode</option>
                    <option value="regular">Regular</option>
                    <option value="self_finance">Self Finance</option>
                    </select>
                    <select name="year" required>
                    <option value="">Select Year</option>
                    <option value="1st Year">1st Year</option>
                    <option value="2nd Year">2nd Year</option>
                    <option value="3rd Year">3rd Year</option>
                    </select>
                    <select name="semester" id="semesterSelect">
                    <option value="">Select Semester</option>
                    <option value="1st Sem">1st Sem</option>
                    <option value="2nd Sem">2nd Sem</option>
                    <option value="3rd Sem">3rd Sem</option>
                    <option value="4th Sem">4th Sem</option>
                    <option value="5th Sem">5th Sem</option>
                    <option value="6th Sem">6th Sem</option>
                    </select>
                    <input type="file" name="file" required style="background:white;">
                    <button class="primary-btn" name="upload">Upload Notes</button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <h2>Available Notes</h2>
        <div class="stats-container" style="margin-top:20px;">
            <?php
            /* ================= STUDENT FILTER ================= */
            if($role=="student"){
                $branch = $_SESSION['branch'];
                $mode = $_SESSION['mode'];
                $year = $_SESSION['year'];
                $semester = $_SESSION['semester'];
                if($mode=="regular"){
                    $query = mysqli_query($conn,"SELECT * FROM notes WHERE branch='$branch' AND mode='$mode' AND year='$year' AND semester='$semester' ORDER BY id DESC");
                }else{
                    $query = mysqli_query($conn,"SELECT * FROM notes WHERE branch='$branch' AND mode='$mode' AND year='$year' ORDER BY id DESC");
                }
            }else{
                $query = mysqli_query($conn,"SELECT * FROM notes ORDER BY id DESC");
            }

            if(mysqli_num_rows($query)==0){
                echo "<p>No notes available</p>";
            }

            while($row=mysqli_fetch_assoc($query)){
            ?>
            <div class="option-box">
                <h3 style="color:#059669; margin-bottom:5px;"><?php echo htmlspecialchars($row['title']); ?></h3>
                <p style="font-weight:500; font-size:15px; color:#374151; margin-bottom:15px;"><?php echo htmlspecialchars($row['description']); ?></p>
                
                <a class="btn-primary" href="../uploads/<?php echo $row['file_path']; ?>" download style="display:block; text-align:center;">
                    Download PDF
                </a>
            </div>
            <?php } ?>
        </div>
    </div>
</div>

<script>
var quill = new Quill('#editor-container', {
  theme: 'snow',
  placeholder: 'Write notes description here...',
  modules: {
    toolbar: [
      ['bold', 'italic', 'underline'],
      [{ 'list': 'ordered'}, { 'list': 'bullet' }],
      ['clean']
    ]
  }
});

var form = document.getElementById('notes-form');
if(form) {
    form.onsubmit = function() {
        document.getElementById('hidden_desc').value = quill.root.innerHTML;
    };
}

const modeSelect = document.getElementById("modeSelect");
const semesterSelect = document.getElementById("semesterSelect");

if(modeSelect){
modeSelect.addEventListener("change", function() {
    if(this.value === "self_finance"){
        semesterSelect.style.display = "none";
        semesterSelect.value = "";
    } else {
        semesterSelect.style.display = "block";
    }
});
}
</script>

</body>
</html>