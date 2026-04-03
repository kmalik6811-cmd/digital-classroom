<?php
session_start();
require_once "../config/db.php";

/* ===== SECURITY ===== */
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin"){
    header("Location: ../auth/login.php");
    exit();
}

$msg = "";

/* ===== DISABLE USER ===== */
if(isset($_GET['disable'])){
    $id = intval($_GET['disable']);

    if($id != $_SESSION['user_id']){
        mysqli_query($conn,"UPDATE users SET status='disabled' WHERE id=$id AND role!='admin'");
        header("Location: manage_users.php");
        exit();
    } else {
        $msg = "You cannot disable yourself!";
    }
}

/* ===== ENABLE USER ===== */
if(isset($_GET['enable'])){
    $id = intval($_GET['enable']);
    mysqli_query($conn,"UPDATE users SET status='active' WHERE id=$id");
    header("Location: manage_users.php");
    exit();
}

/* ===== DELETE USER ===== */
if(isset($_GET['delete'])){
    $id = intval($_GET['delete']);
    if($id != $_SESSION['user_id']){
        try {
            // Unlink from subjects (Teacher)
            mysqli_query($conn,"UPDATE subjects SET teacher_id=NULL WHERE teacher_id=$id");
            
            // Delete submissions (Student)
            mysqli_query($conn,"DELETE FROM submissions WHERE student_id=$id");
            
            // Delete notes (Teacher - just in case)
            mysqli_query($conn,"DELETE FROM notes WHERE uploaded_by=$id");

            // Finally, delete the root user record
            mysqli_query($conn,"DELETE FROM users WHERE id=$id AND role!='admin'");
            header("Location: manage_users.php");
            exit();
        } catch (mysqli_sql_exception $e) {
            $msg = "Database Error: Could not delete user. Related records restrict this action.";
        }
    } else {
        $msg = "You cannot delete yourself!";
    }
}

/* ===== CHANGE ROLE ===== */
if(isset($_GET['role'])){
    $id = intval($_GET['role']);

    $res = mysqli_query($conn,"SELECT role FROM users WHERE id=$id");
    $user = mysqli_fetch_assoc($res);

    if($user && $user['role'] != "admin"){
        $newRole = ($user['role']=="student") ? "teacher" : "student";
        mysqli_query($conn,"UPDATE users SET role='$newRole' WHERE id=$id");
    }

    header("Location: manage_users.php");
    exit();
}
?>

<?php require_once "../includes/header.php"; ?>
<?php require_once "../includes/sidebar.php"; ?>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="main-content">

        <div class="topbar">
            <h1>Manage Users</h1>
            <p>Admin Control Panel</p>
        </div>

        <?php if($msg!=""): ?>
            <div class="alert-message">
                <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <div class="table-card">

            <table class="modern-table">

                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th style="text-align:center;">Action</th>
                    </tr>
                </thead>

                <tbody>

                <?php
                $query = mysqli_query($conn,"SELECT * FROM users ORDER BY id DESC");
                while($row = mysqli_fetch_assoc($query)){
                ?>

                <tr>
                    <td><?php echo htmlspecialchars($row['name']); ?></td>

                    <td class="email-cell">
                        <?php echo htmlspecialchars($row['email']); ?>
                    </td>

                    <td><?php echo ucfirst($row['role']); ?></td>

                    <td>
                        <?php if($row['status']=="active"): ?>
                            <span class="badge active">Active</span>
                        <?php else: ?>
                            <span class="badge disabled">Disabled</span>
                        <?php endif; ?>
                    </td>

                    <td class="action-buttons">

                    <?php if($row['role'] != "admin"): ?>

                        <a class="btn-primary"
                           href="?role=<?php echo $row['id']; ?>">
                           Change Role
                        </a>

                        <?php if($row['status']=="active"): ?>
                            <a class="btn-warning"
                               href="?disable=<?php echo $row['id']; ?>"
                               onclick="return confirm('Disable this user?')">
                               Disable
                            </a>
                        <?php else: ?>
                            <a class="btn-success"
                               href="?enable=<?php echo $row['id']; ?>">
                               Enable
                            </a>
                        <?php endif; ?>
                        
                        <a class="btn-danger"
                           href="?delete=<?php echo $row['id']; ?>"
                           onclick="return confirm('WARNING: Are you sure you want to permanently delete this user?')">
                           Delete
                        </a>

                    <?php else: ?>
                        <span class="admin-label">Admin</span>
                    <?php endif; ?>

                    </td>
                </tr>

                <?php } ?>

                </tbody>

            </table>

        </div>

    </div>

</div>

</body>
</html>