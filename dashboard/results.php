<?php
session_start();
require_once "../config/db.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role']!="student"){
    header("Location: dashboard.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$role = "student";
?>

<!DOCTYPE html>
<?php require_once "../includes/header.php"; ?>
<?php require_once "../includes/sidebar.php"; ?>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="main-content">
        <div class="topbar">
            <div>
                <h1>My Results</h1>
                <p>Check your grades and feedback</p>
            </div>
        </div>

        <div class="stats-container">
            <?php
            $query = mysqli_query($conn,"
            SELECT assignments.title, submissions.grade, submissions.feedback
            FROM submissions
            JOIN assignments ON submissions.assignment_id = assignments.id
            WHERE submissions.student_id = $student_id
            ORDER BY submissions.id DESC
            ");

            if(mysqli_num_rows($query)==0){
                echo "<p style='color:#6b7280; margin-left:10px;'>No graded assignments yet.</p>";
            }

            while($row=mysqli_fetch_assoc($query)){
            ?>
            <div class="option-box">
                <h3 style="color:#4f46e5; margin-bottom: 10px;"><?php echo htmlspecialchars($row['title']); ?></h3>
                
                <p style="font-size: 15px;">
                    <span style="font-weight: 600;">Grade:</span> 
                    <?php if($row['grade']): ?>
                        <span style="color:#059669; font-weight:700;"><?php echo htmlspecialchars($row['grade']); ?></span>
                    <?php else: ?>
                        <span style="color:#9ca3af; font-style:italic;">Not graded yet</span>
                    <?php endif; ?>
                </p>

                <div style="margin-top:15px; padding:15px; background: #f9fafb; border-radius:12px; border:1px solid #e5e7eb;">
                    <span style="font-weight: 600; font-size:14px; display:block; margin-bottom:5px;">Feedback:</span>
                    <p style="margin:0; color:#4b5563; font-size:14px;">
                        <?php echo $row['feedback'] ? htmlspecialchars($row['feedback']) : "<i>No feedback provided</i>"; ?>
                    </p>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>

</body>
</html>
