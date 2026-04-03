<?php
session_start();
require_once "../config/db.php";
require_once "../includes/csrf.php";

if(!isset($_SESSION['user_id'])){
header("Location: ../auth/login.php");
exit();
}

$id=$_SESSION['user_id'];
$role = $_SESSION['role'] ?? '';
$msg="";

if(isset($_POST['update'])){
verify_csrf_token($_POST['csrf_token'] ?? '');

$name=$_POST['name'];
$email=$_POST['email'];
$pass=$_POST['password'];

if(!empty($pass)){
$hash=password_hash($pass,PASSWORD_DEFAULT);
mysqli_query($conn,"UPDATE users SET name='$name',email='$email',password='$hash' WHERE id=$id");
}else{
mysqli_query($conn,"UPDATE users SET name='$name',email='$email' WHERE id=$id");
}

$_SESSION['username']=$name;
$msg="Profile updated!";
}

$user=mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM users WHERE id=$id"));
?>
<?php require_once "../includes/header.php"; ?>
<?php require_once "../includes/sidebar.php"; ?>

    <!-- ===== MAIN CONTENT ===== -->
    <div class="main-content">
        <div class="topbar">
            <div>
                <h1>Profile</h1>
                <p>Manage your account settings</p>
            </div>
        </div>

        <?php if($msg!="") echo "<p class='success'>$msg</p>"; ?>

        <div class="table-card" style="max-width: 600px;">
            <div class="option-box" style="margin: 0; box-shadow: none;">
                <h3>Update Information</h3>
                <form method="post" style="margin-top: 20px;">
                    <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                    <label style="font-size: 14px; font-weight: 600; color: #374151;">Full Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                    
                    <label style="font-size: 14px; font-weight: 600; color: #374151; margin-top: 10px; display: block;">Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    
                    <label style="font-size: 14px; font-weight: 600; color: #374151; margin-top: 10px; display: block;">New Password</label>
                    <input type="password" name="password" placeholder="Leave blank to keep current password">
                    
                    <button name="update" class="primary-btn">Update Profile</button>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>
