<?php
session_start();
require_once "../config/db.php";
require_once "../includes/csrf.php";

/* ===== SECURITY ===== */
if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit();
}

$role = $_SESSION['role'] ?? '';

if($role!="teacher"){
    echo "Access denied";
    exit();
}

$msg = "";

/* ===== SAVE GRADE ===== */
if(isset($_POST['grade_submit'])){
verify_csrf_token($_POST['csrf_token'] ?? '');

    $sid = intval($_POST['submission_id']);
    $grade = mysqli_real_escape_string($conn,$_POST['grade']);
    $feedback = mysqli_real_escape_string($conn,$_POST['feedback']);

    mysqli_query($conn,"
    UPDATE submissions 
    SET grade='$grade', feedback='$feedback'
    WHERE id=$sid
    ");

    $msg = "Grade saved successfully!";
}
?>
<?php require_once "../includes/header.php"; ?>
<?php require_once "../includes/sidebar.php"; ?>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="main-content">
        <div class="topbar">
            <div>
                <h1>Student Submissions</h1>
                <p>Review and grade homework</p>
            </div>
        </div>

        <?php if($msg!="") echo "<p class='success'>$msg</p>"; ?>

        <div class="stats-container">
            <?php
            /* 🔥 JOIN assignments + users */
            $query = mysqli_query($conn,"
            SELECT submissions.*, 
                   users.name AS student_name,
                   users.roll_number,
                   assignments.title AS assignment_title
            FROM submissions
            JOIN users ON submissions.student_id = users.id
            JOIN assignments ON submissions.assignment_id = assignments.id
            ORDER BY submissions.id DESC
            ");

            if(mysqli_num_rows($query)==0){
                echo "<p style='color:#6b7280; margin-left:10px;'>No submissions yet.</p>";
            }

            while($row=mysqli_fetch_assoc($query)){
            ?>
            <div class="option-box">
                <h3 style="color:#111827; margin-bottom:5px;"><?php echo htmlspecialchars($row['student_name']); ?></h3>
                <p style="font-size:14px; color:#6b7280; margin-bottom:15px;">
                    <b>Roll No:</b> <?php echo htmlspecialchars($row['roll_number'] ?? 'Not Assigned'); ?>
                </p>
                
                <div style="background:#f9fafb; padding:15px; border-radius:12px; margin-bottom:15px;">
                    <span style="font-size:13px; color:#6b7280; font-weight:600; text-transform:uppercase;">Assignment</span>
                    <p style="margin:5px 0 0 0; color:#4f46e5; font-weight:600;">
                        <?php echo htmlspecialchars($row['assignment_title']); ?>
                    </p>
                    <a class="small-btn" href="../uploads/<?php echo $row['file_path']; ?>" download style="display:inline-block; margin-top:10px; padding:6px 14px; font-size:13px;">
                        Download File
                    </a>
                </div>

                <form method="post" style="border-top:1px solid #e5e7eb; padding-top:15px;">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <input type="hidden" name="submission_id" value="<?php echo $row['id']; ?>">

                    <div style="margin-bottom:10px;">
                        <label style="font-size:14px; font-weight:600; color:#374151;">Grade:</label>
                        <input type="text" name="grade" placeholder="e.g. 8/10" value="<?php echo htmlspecialchars($row['grade'] ?? ''); ?>" required style="margin-top:5px; padding:10px;">
                    </div>

                    <div style="margin-bottom:15px;">
                        <label style="font-size:14px; font-weight:600; color:#374151;">Feedback:</label>
                        <input type="text" name="feedback" placeholder="Great work!" value="<?php echo htmlspecialchars($row['feedback'] ?? ''); ?>" style="margin-top:5px; padding:10px;">
                    </div>

                    <button class="btn-primary" name="grade_submit" style="width:100%;">Save Grade</button>
                </form>
            </div>
            <?php } ?>
        </div>
    </div>
</div>

</body>
</html>