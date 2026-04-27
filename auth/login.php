<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "../config/db.php";
require_once "../includes/csrf.php";

$message = "";

if($_SERVER['REQUEST_METHOD'] === "POST"){
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if(empty($email) || empty($password)){
        $message = "Please fill all fields!";
    } 
    else {

        $stmt = mysqli_prepare($conn,
            "SELECT id, name, role, password, status, branch, mode, year, semester 
             FROM users WHERE email=?"
        );

        if($stmt){

            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);

            if($result && mysqli_num_rows($result) === 1){

                $user = mysqli_fetch_assoc($result);

                if(password_verify($password, $user['password'])){

                    $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
                    
                    if(!preg_match($pattern, $password)){
                        $message = "Your current password is too weak. Please click 'Forgot Password' to create a strong one.";
                    }
                    elseif($user['status'] === "pending"){
                        $message = "Your account is waiting for admin approval.";
                    }
                    elseif($user['status'] === "disabled"){
                        $message = "Your account is disabled. Contact admin.";
                    }
                    else {

                        session_regenerate_id(true);

                        $_SESSION['user_id']  = $user['id'];
                        $_SESSION['username'] = $user['name'];
                        $_SESSION['role']     = $user['role'];
                        $_SESSION['branch']   = $user['branch'] ?? '';
                        $_SESSION['mode']     = $user['mode'] ?? '';
                        $_SESSION['year']     = $user['year'] ?? '';
                        $_SESSION['semester'] = $user['semester'] ?? '';

                        header("Location: ../dashboard/dashboard.php");
                        exit();
                    }

                } else {
                    $message = "Invalid email or password!";
                }

            } else {
                $message = "Invalid email or password!";
            }

            mysqli_stmt_close($stmt);

        } else {
            $message = "Database error.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Login | Digital Classroom</title>
<link rel="stylesheet" href="/css/style.css?v=<?= time(); ?>">
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
                    <h2>Welcome Back</h2>
                    <p>Log in to access your courses and continue learning with Digital Classroom.</p>
                </div>
            </div>
            
            <div class="auth-form-side">
                <div class="app-card">
                    <h2 class="logo">Login</h2>
                    
                    <?php if($message!="") echo "<p class='error'>$message</p>"; ?>
                    
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="email" name="email" placeholder="Email" required>
                        <input type="password" name="password" placeholder="Password" required>
                        <button class="primary-btn">Login</button>
                        <a href="forgot_password.php" class="forgot-link">Forgot Password?</a>
                    </form>
                    
                    <p class="link-text" style="text-align: center;">
                        Don't have an account? <a href="register.php">Create account</a>
                    </p>
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