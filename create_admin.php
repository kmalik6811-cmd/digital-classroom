<?php
require_once "config/db.php";

$email = "admin@test.com";
$newPassword = "123456";

$hash = password_hash($newPassword, PASSWORD_DEFAULT);

mysqli_query($conn,"UPDATE users SET password='$hash' WHERE email='$email'");

echo "Password updated successfully!";
?>
