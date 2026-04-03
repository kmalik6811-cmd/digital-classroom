<?php
session_start();
require_once "../config/db.php";

/* ===== LOGIN CHECK ===== */
if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);
$role = $_SESSION['role'];

/* ===== DISABLED ACCOUNT CHECK ===== */
$checkUser = mysqli_query($conn,"SELECT status FROM users WHERE id=$user_id");
$userData = mysqli_fetch_assoc($checkUser);

if(isset($userData['status']) && $userData['status']=="disabled"){
    session_destroy();
    header("Location: ../auth/login.php");
    exit();
}

$alerts = [];

/* ===== STUDENT NOTIFICATION SYSTEM ===== */
if($role == "student"){

    $today = date("Y-m-d H:i:s");

    $query = mysqli_query($conn,"
        SELECT * FROM assignments 
        WHERE id NOT IN (
            SELECT assignment_id FROM submissions WHERE student_id=$user_id
        )
    ");

    while($row = mysqli_fetch_assoc($query)){

        $deadline = $row['deadline'];
        $diff = strtotime($deadline) - strtotime($today);
        $days_left = floor($diff / (60*60*24));

        if($diff < 0){
            $alerts[] = "⚠️ Assignment '".$row['title']."' deadline passed!";
        }
        elseif($days_left <= 2){
            $alerts[] = "⏰ '".$row['title']."' deadline in ".$days_left." day(s)";
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
                <h1>Welcome <?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></h1>
                <p>Role: <?php echo ucfirst(htmlspecialchars($role)); ?> 
                <?php if($role == 'student' && !empty($_SESSION['branch'])) { ?>
                    | <?php echo ucfirst(htmlspecialchars($_SESSION['branch'])); ?> | <?php echo ucfirst(htmlspecialchars($_SESSION['mode'])); ?> | <?php echo htmlspecialchars($_SESSION['year']); ?>
                    <?php if($_SESSION['mode']=="regular" && !empty($_SESSION['semester'])) { echo " | " . htmlspecialchars($_SESSION['semester']); } ?>
                <?php } ?>
                </p>
            </div>
        </div>

        <!-- 🔔 Notification Section -->
        <?php if(!empty($alerts)): ?>
        <div class="notify-box">
            <?php foreach($alerts as $alert): ?>
            <p><?php echo htmlspecialchars($alert); ?></p>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div class="stats-container">
            <?php if($role=="student"): ?>
                <div class="option-box">
                    <h3>Assignments</h3>
                    <p>View and submit homework</p>
                    <a class="small-btn" href="assignment.php">Open</a>
                </div>
                <div class="option-box">
                    <h3>Study Notes</h3>
                    <p>Download class notes</p>
                    <a class="small-btn" href="notes.php">Open</a>
                </div>
                <div class="option-box">
                    <h3>My Results</h3>
                    <p>Check grades and feedback</p>
                    <a class="small-btn" href="results.php">View</a>
                </div>
            <?php elseif($role=="teacher"): ?>
                <div class="option-box">
                    <h3>Manage Assignments</h3>
                    <p>Create and manage homework</p>
                    <a class="small-btn" href="assignment.php">Open</a>
                </div>
                <div class="option-box">
                    <h3>Upload Notes</h3>
                    <p>Upload and manage study material</p>
                    <a class="small-btn" href="notes.php">Open</a>
                </div>
                <div class="option-box">
                    <h3>Student Submissions</h3>
                    <p>Check and grade submissions</p>
                    <a class="small-btn" href="submissions.php">View</a>
                </div>
            <?php elseif($role=="admin"): ?>
                <div class="option-box">
                    <h3>Admin Dashboard</h3>
                    <p>Manage users and view statistics</p>
                    <a class="small-btn" href="../admin/admin_dashboard.php">Open</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
