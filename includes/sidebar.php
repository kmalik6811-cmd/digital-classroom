<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- ===== SIDEBAR ===== -->
<div class="sidebar">
    <div class="brand" style="margin-bottom: 20px;">Digital Classroom</div>
    
    <?php if(isset($_SESSION['role'])): ?>
    
        <?php if($_SESSION['role'] == "student"): ?>
            <a href="/dashboard/dashboard.php" class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
            <a href="/dashboard/assignment.php" class="nav-link <?= $current_page == 'assignment.php' ? 'active' : '' ?>">Assignments</a>
            <a href="/dashboard/notes.php" class="nav-link <?= $current_page == 'notes.php' ? 'active' : '' ?>">Study Notes</a>
            <a href="/dashboard/results.php" class="nav-link <?= $current_page == 'results.php' ? 'active' : '' ?>">My Results</a>
            
        <?php elseif($_SESSION['role'] == "teacher"): ?>
            <a href="/dashboard/dashboard.php" class="nav-link <?= $current_page == 'dashboard.php' ? 'active' : '' ?>">Dashboard</a>
            <a href="/dashboard/assignment.php" class="nav-link <?= $current_page == 'assignment.php' ? 'active' : '' ?>">Manage Assignments</a>
            <a href="/dashboard/notes.php" class="nav-link <?= $current_page == 'notes.php' ? 'active' : '' ?>">Upload Notes</a>
            <a href="/dashboard/submissions.php" class="nav-link <?= $current_page == 'submissions.php' ? 'active' : '' ?>">Student Submissions</a>
            
        <?php elseif($_SESSION['role'] == "admin"): ?>
            <a href="/admin/admin_dashboard.php" class="nav-link <?= $current_page == 'admin_dashboard.php' ? 'active' : '' ?>">Admin Panel</a>
            <a href="/admin/manage_users.php" class="nav-link <?= $current_page == 'manage_users.php' ? 'active' : '' ?>">Manage Users</a>
            
        <?php endif; ?>
        
        <div style="margin-top: auto;"></div>
        <a href="/logout.php" class="nav-link logout">Logout</a>
        
    <?php endif; ?>
</div>
