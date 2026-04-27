<?php
session_start();
require_once "../config/db.php";
require_once "../includes/csrf.php";

$message = "";

if(!isset($_SESSION['temp_user'])) {
    header("Location: register.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == "POST") {
    verify_csrf_token($_POST['csrf_token'] ?? '');
    
    $submitted_otp = $_POST['otp'];
    
    if(time() > $_SESSION['otp_expiry']) {
        $message = "OTP has expired. Please register again.";
        unset($_SESSION['temp_user'], $_SESSION['otp'], $_SESSION['otp_expiry']);
    }
    elseif($submitted_otp == $_SESSION['otp']) {
        // Correct OTP, insert into DB
        $user = $_SESSION['temp_user'];
        $name = mysqli_real_escape_string($conn, $user['name']);
        
        $roll_number = $user['roll_number'] ? "'" . mysqli_real_escape_string($conn, $user['roll_number']) . "'" : "''";
        $email = mysqli_real_escape_string($conn, $user['email']);
        $hash = $user['password'];
        $role = mysqli_real_escape_string($conn, $user['role']);
        $branch = $user['branch'] ? "'" . mysqli_real_escape_string($conn, $user['branch']) . "'" : 'NULL';
        $mode = $user['mode'] ? "'" . mysqli_real_escape_string($conn, $user['mode']) . "'" : 'NULL';
        $year = $user['year'] ? "'" . mysqli_real_escape_string($conn, $user['year']) . "'" : 'NULL';
        $semester = $user['semester'] ? "'" . mysqli_real_escape_string($conn, $user['semester']) . "'" : 'NULL';
        $status = "pending";

        $query = "INSERT INTO users (name, roll_number, email, password, role, branch, mode, year, semester, status)
                  VALUES ('$name', $roll_number, '$email', '$hash', '$role', $branch, $mode, $year, $semester, '$status')";

        if(mysqli_query($conn, $query)){
            $_SESSION['success'] = "Email verified successfully! Please wait for admin approval.";
            unset($_SESSION['temp_user'], $_SESSION['otp'], $_SESSION['otp_expiry']);
            header("Location: login.php");
            exit();
        } else {
            $message = "Database error. Please try again.";
        }
    }
    else {
        $message = "Invalid OTP code!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Verify Email | Digital Classroom</title>
<link rel="stylesheet" href="/css/style.css?v=<?= time(); ?>">
<style>
.otp-input {
    letter-spacing: 12px;
    font-size: 24px !important;
    text-align: center;
    font-weight: 700;
}
.otp-instruction {
    color: #6b7280;
    margin-bottom: 25px;
    text-align: center;
    line-height: 1.5;
}
</style>
</head>
<body>

<div class="auth-layout">
    <header class="header">
        <h1 class="logo">Digital Classroom</h1>
        <nav class="nav-links">
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        </nav>
    </header>

    <div class="auth-container">
        <div class="auth-wrapper">
            <div class="auth-image-side">
                <div class="auth-image-content">
                    <h2>Verify Your Email</h2>
                    <p>Security check. Please verify your email to continue creating your account.</p>
                </div>
            </div>
            
            <div class="auth-form-side">
                <div class="app-card">
                    <h2 class="logo" style="text-align:center;">Enter OTP</h2>
                    
                    <p class="otp-instruction">We sent a 6-digit verification code to<br><b><?php echo htmlspecialchars($_SESSION['temp_user']['email'] ?? ''); ?></b></p>

                    <?php if($message!="") echo "<p class='error'>$message</p>"; ?>
                    
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="text" name="otp" class="otp-input" placeholder="000000" maxlength="6" required pattern="\d{6}" autocomplete="off">
                        <button class="primary-btn">Verify Account</button>
                    </form>
                    
                    <p class="link-text" style="text-align: center;"><a href="register.php">Cancel & Register Again</a></p>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 Digital Classroom. All rights reserved.</p>
    </footer>
</div>

</body>
</html>
