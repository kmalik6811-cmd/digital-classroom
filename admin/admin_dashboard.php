<?php
session_start();
require_once "../config/db.php";

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin"){
    header("Location: ../auth/login.php");
    exit();
}

/* ===== FETCH STATS ===== */
$total_students = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT COUNT(*) as total FROM users WHERE role='student'")
)['total'];

$total_teachers = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT COUNT(*) as total FROM users WHERE role='teacher'")
)['total'];

$total_assignments = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT COUNT(*) as total FROM assignments")
)['total'];

$total_submissions = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT COUNT(*) as total FROM submissions")
)['total'];
?>
<?php require_once "../includes/header.php"; ?>
<?php require_once "../includes/sidebar.php"; ?>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="main-content">
        <div class="topbar">
            <div>
                <h1>Admin Dashboard</h1>
                <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></p>
            </div>
        </div>

        <div class="stats-container">
            <div class="stat-box">
                <h3><?php echo $total_students; ?></h3>
                <p>Total Students</p>
            </div>

            <div class="stat-box">
                <h3><?php echo $total_teachers; ?></h3>
                <p>Total Teachers</p>
            </div>

            <div class="stat-box">
                <h3><?php echo $total_assignments; ?></h3>
                <p>Total Assignments</p>
            </div>

            <div class="stat-box">
                <h3><?php echo $total_submissions; ?></h3>
                <p>Total Submissions</p>
            </div>
        </div>


    </div>
</div>

</body>
</html>