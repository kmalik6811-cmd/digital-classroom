<?php
session_start();
require_once "../config/db.php";
require_once "../includes/csrf.php";

date_default_timezone_set('Asia/Kolkata');

if(!isset($_GET['token'])){
    die("Invalid access");
}

$token = $_GET['token'];

$stmt = mysqli_prepare($conn,
    "SELECT user_id, expiry FROM password_resets WHERE token=?");
mysqli_stmt_bind_param($stmt,"s",$token);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result) !== 1){
    die("Invalid or expired token");
}

$data = mysqli_fetch_assoc($result);

// Expiry check in PHP
if(strtotime($data['expiry']) < time()){
    die("Token expired");
}

$user_id = $data['user_id'];

$message = "";

if($_SERVER["REQUEST_METHOD"]=="POST"){
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $newpass = $_POST['password'];
    $confirm = $_POST['confirm'];

    if($newpass != $confirm){
        $message = "Passwords do not match!";
    } 
    else {

        // Strong password check
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
        if(!preg_match($pattern, $newpass)){
            $message = "Password must be at least 8 characters, with uppercase, lowercase, number and special char.";
        }
        else {
            $hash = password_hash($newpass,PASSWORD_DEFAULT);

            $stmt2 = mysqli_prepare($conn,
                "UPDATE users SET password=? WHERE id=?");
            mysqli_stmt_bind_param($stmt2,"si",$hash,$user_id);
        mysqli_stmt_execute($stmt2);

        // Delete token after success
        mysqli_query($conn,
            "DELETE FROM password_resets WHERE user_id='$user_id'");

        echo "Password updated successfully! 
              <a href='login.php'>Login</a>";
        exit();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Reset Password</title>
<link rel="stylesheet" href="/digital_classroom/css/style.css">
</head>
<body>

<div class="app-card">
<h2>Reset Password</h2>

<?php if($message!="") echo "<p class='error'>$message</p>"; ?>

<form method="post">
<input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
<input type="password" name="password" placeholder="New Password" required>
<input type="password" name="confirm" placeholder="Confirm Password" required>
<button class="primary-btn">Reset Password</button>
</form>

</div>
</body>
</html>