<?php
session_start();
require_once "../config/db.php";
require_once "../includes/csrf.php";

date_default_timezone_set('Asia/Kolkata');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../includes/PHPMailer.php';
require '../includes/SMTP.php';
require '../includes/Exception.php';

$message = "";

if($_SERVER['REQUEST_METHOD'] === "POST"){
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $email = trim($_POST['email']);

    if(empty($email)){
        $message = "Please enter your email.";
    } 
    else {

        // Check if email exists
        $stmt = mysqli_prepare($conn,"SELECT id FROM users WHERE email=?");
        mysqli_stmt_bind_param($stmt,"s",$email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($result) === 1){

            $user = mysqli_fetch_assoc($result);
            $user_id = $user['id'];

            // Generate secure token
            $token = bin2hex(random_bytes(32));
            $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

            // Delete expired tokens only
            mysqli_query($conn,"DELETE FROM password_resets WHERE expiry < NOW()");

            // Delete old tokens of this user
            mysqli_query($conn,"DELETE FROM password_resets WHERE user_id='$user_id'");

            // Insert new token
            $stmt2 = mysqli_prepare($conn,
                "INSERT INTO password_resets(user_id,token,expiry) VALUES(?,?,?)");
            mysqli_stmt_bind_param($stmt2,"iss",$user_id,$token,$expiry);
            mysqli_stmt_execute($stmt2);

            // Create reset link
            $reset_link = "http://localhost/digital_classroom/auth/reset_password.php?token=".$token;

            // Send Email
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = SMTP_HOST;
                $mail->SMTPAuth   = true;
                $mail->Username   = SMTP_USER;
                $mail->Password   = SMTP_PASS; 
                $mail->Port       = SMTP_PORT;

                $mail->setFrom(SMTP_USER, 'Digital Classroom');
                $mail->addAddress($email);

                $mail->isHTML(true);
                $mail->Subject = 'Reset Your Password - Digital Classroom';

                $mail->Body = "
                    <h3>Password Reset Request</h3>
                    <p>Hello,</p>
                    <p>Click the button below to reset your password:</p>
                    <p>
                        <a href='$reset_link' 
                        style='background:#4CAF50;
                               padding:10px 15px;
                               color:white;
                               text-decoration:none;
                               border-radius:5px;'>
                               Reset Password
                        </a>
                    </p>
                    <br>
                    <p>If you did not request this, please ignore this email.</p>
                    <small>This link will expire in 1 hour.</small>
                ";

                $mail->send();
                $message = "Reset link has been sent to your email.";

            } catch (Exception $e) {
                $message = "Mailer Error: " . $mail->ErrorInfo;
            }

            mysqli_stmt_close($stmt2);

        } else {
            $message = "If the email exists, a reset link has been sent.";
        }

        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Forgot Password | Digital Classroom</title>
<link rel="stylesheet" href="/digital_classroom/css/style.css?v=<?= time(); ?>">
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
                    <h2>Password Recovery</h2>
                    <p>Enter your email to receive a password reset link and get back to your learning journey.</p>
                </div>
            </div>
            
            <div class="auth-form-side">
                <div class="app-card">
                    <h2 class="logo">Forgot Password</h2>
                    
                    <?php if($message!="") echo "<p class='success'>$message</p>"; ?>
                    
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="email" name="email" placeholder="Enter your email" required>
                        <button class="primary-btn">Send Reset Link</button>
                    </form>
                    
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="login.php" class="link-text" style="font-weight: 500;">Back to Login</a>
                    </div>
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