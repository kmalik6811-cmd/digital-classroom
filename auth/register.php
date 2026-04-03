<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "../config/db.php";
require_once "../includes/csrf.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../includes/PHPMailer.php';
require '../includes/SMTP.php';
require '../includes/Exception.php';

$message = "";

if($_SERVER['REQUEST_METHOD']=="POST"){
    verify_csrf_token($_POST['csrf_token'] ?? '');

    $name = mysqli_real_escape_string($conn,$_POST['name']);
    $roll_number = mysqli_real_escape_string($conn,$_POST['roll_number']);
    $email = mysqli_real_escape_string($conn,$_POST['email']);
    $role = strtolower($_POST['role']);
    $branch = mysqli_real_escape_string($conn,$_POST['branch']);
    $mode = $_POST['mode'] ?? NULL;
    $year = $_POST['year'] ?? NULL;
    $semester = $_POST['semester'] ?? NULL;

    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    if($password != $confirm){
        $message = "Passwords do not match!";
    }
    else {
        // Strong password check
        $pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
        if(!preg_match($pattern, $password)){
            $message = "Password must be at least 8 characters, with uppercase, lowercase, number and special char.";
        }
        else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            // Check existing email
            $check = mysqli_query($conn,"SELECT id FROM users WHERE email='$email'");

            if(mysqli_num_rows($check)>0){
                $message = "Email already exists!";
            }
            else{
                // Generate OTP
                $otp = rand(100000, 999999);
                
                $_SESSION['temp_user'] = [
                    'name' => $name,
                    'roll_number' => ($role === 'teacher') ? NULL : $roll_number,
                    'email' => $email,
                    'password' => $hash,
                    'role' => $role,
                    'branch' => ($role === 'teacher') ? NULL : $branch,
                    'mode' => ($role === 'teacher') ? NULL : $mode,
                    'year' => ($role === 'teacher') ? NULL : $year,
                    'semester' => ($role === 'teacher') ? NULL : $semester
                ];
                $_SESSION['otp'] = $otp;
                $_SESSION['otp_expiry'] = time() + (10 * 60); // 10 minutes

                // Send email
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
                    $mail->Subject = 'Verify Your Registration - Digital Classroom';
                    $mail->Body    = "<h3>Registration OTP</h3>
                                      <p>Hello $name,</p>
                                      <p>Your OTP for registration is: <b style='font-size:24px; color:#4f46e5;'>$otp</b></p>
                                      <p>This code will expire in 10 minutes.</p>";

                    $mail->send();
                    header("Location: verify_otp.php");
                    exit();
                } catch (Exception $e) {
                    $message = "OTP could not be sent. Mailer Error: {$mail->ErrorInfo}";
                    unset($_SESSION['temp_user'], $_SESSION['otp'], $_SESSION['otp_expiry']);
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Register | Digital Classroom</title>
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
                    <h2>Join Us</h2>
                    <p>Create an account to start your learning journey with Digital Classroom.</p>
                </div>
            </div>
            
            <div class="auth-form-side">
                <div class="app-card">
                    <h2 class="logo">Create Account</h2>
                    
                    <?php if($message!="") echo "<p class='error'>$message</p>"; ?>
                    
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="text" name="name" placeholder="Full Name" required>
                        <input type="email" name="email" placeholder="Email" required>
                        
                        <select name="role" id="roleSelect" required>
                            <option value="">Select Role</option>
                            <option value="student">Student</option>
                            <option value="teacher">Teacher</option>
                        </select>
                        
                        <div id="studentFields">
                            <input type="text" name="roll_number" placeholder="Roll Number" required>
                            <select name="branch" required>
                                <option value="">Select Branch</option>
                                <option value="computer">Computer</option>
                                <option value="electrical">Electrical</option>
                                <option value="electronics">Electronics</option>
                                <option value="mechanical">Mechanical</option>
                                <option value="civil">Civil</option>
                            </select>
                            
                            <select name="mode" id="modeSelect" required>
                                <option value="">Select Mode</option>
                                <option value="regular">Regular</option>
                                <option value="self_finance">Self Finance</option>
                            </select>
                            
                            <select name="year" required>
                                <option value="">Select Year</option>
                                <option value="1st Year">1st Year</option>
                                <option value="2nd Year">2nd Year</option>
                                <option value="3rd Year">3rd Year</option>
                            </select>
                            
                            <select name="semester" id="semesterSelect">
                                <option value="">Select Semester</option>
                                <option value="1st Sem">1st Sem</option>
                                <option value="2nd Sem">2nd Sem</option>
                                <option value="3rd Sem">3rd Sem</option>
                                <option value="4th Sem">4th Sem</option>
                                <option value="5th Sem">5th Sem</option>
                                <option value="6th Sem">6th Sem</option>
                            </select>
                        </div>
                        
                        <input type="password" name="password" placeholder="Password" required>
                        <input type="password" name="confirm" placeholder="Confirm Password" required>
                        
                        <button class="primary-btn">Register</button>
                    </form>
                    
                    <p class="link-text" style="text-align: center;"><a href="login.php">Already have account?</a></p>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2026 Digital Classroom. All rights reserved.</p>
    </footer>
</div>

<script>
const modeSelect = document.getElementById("modeSelect");
const semesterSelect = document.getElementById("semesterSelect");
const roleSelect = document.getElementById("roleSelect");
const studentFields = document.getElementById("studentFields");

// Hide/Show Student Fields based on Role
roleSelect.addEventListener("change", function() {
    const inputs = studentFields.querySelectorAll("input, select");
    if(this.value === "teacher") {
        studentFields.style.display = "none";
        inputs.forEach(el => el.removeAttribute("required"));
    } else {
        studentFields.style.display = "block";
        inputs.forEach(el => {
            if(el.id !== "semesterSelect") { // Semester requires condition
                el.setAttribute("required", "required");
            }
        });
    }
});

// Self-Finance hides semester logic
modeSelect.addEventListener("change", function() {
    if(this.value === "self_finance"){
        semesterSelect.style.display = "none";
        semesterSelect.value = "";
    } else {
        semesterSelect.style.display = "block";
    }
});

// Trigger change event to set initial state correctly
roleSelect.dispatchEvent(new Event('change'));
</script>

</body>
</html>