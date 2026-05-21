<?php
session_start();
include "connect.php";
include "../config/email_config.php";
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$email = "";
$emailErr = $successMsg = $resetErr = "";
$step = 1;

if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    if(isset($_POST["step"]) && $_POST["step"] == 1){
        
        if(empty($_POST["email"])){
            $emailErr = "Email is required";
        } else {
            $email = $_POST["email"];
            
            $stmt = $conn->prepare("SELECT user_id, username FROM users WHERE email = ?");
            if(!$stmt) {
                $resetErr = "Database error: " . $conn->error;
            } else {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if($result->num_rows == 1){
                    $row = $result->fetch_assoc();
                    $user_id = $row["user_id"];
                    
                    $reset_token = bin2hex(random_bytes(32));
                    $token_expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));
                    
                    $stmt2 = $conn->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE user_id = ?");
                    if(!$stmt2) {
                        $resetErr = "Database error: " . $conn->error;
                    } else {
                        $stmt2->bind_param("ssi", $reset_token, $token_expiry, $user_id);
                        $stmt2->execute();
                        
                        $reset_link = "https://localhost/ay25-26-cmp208-salcc-student-organiser/acc/forgot.php?token=" . $reset_token;
                        $subject = "Password Reset Request";
                        $message = "Click this link to reset your password: " . $reset_link . "\n\nThis link expires in 1 hour.";
                        
                        $mail = new PHPMailer(true);
                        try {
                            $mail->isSMTP();
                            $mail->Host = SMTP_HOST;
                            $mail->SMTPAuth = true;
                            $mail->Username = SMTP_USER;
                            $mail->Password = SMTP_PASS;
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                            $mail->Port = SMTP_PORT;
                            
                            $mail->setFrom(SENDER_EMAIL, 'Student Organizer');
                            $mail->addAddress($email);
                            
                            $mail->isHTML(true);
                            $mail->Subject = $subject;
                            $mail->Body = "<p>Click this link to reset your password:</p><p><a href='" . $reset_link . "'>" . $reset_link . "</a></p><p>This link expires in 1 hour.</p>";
                            $mail->AltBody = $message;
                            
                            if($mail->send()){
                                $successMsg = "Password reset link sent to your email!";
                            } else {
                                $resetErr = "Failed to send email: " . $mail->ErrorInfo;
                            }
                        } catch (Exception $e) {
                            $resetErr = "Failed to send email: " . $e->getMessage();
                        }
                    }
                } else {
                    $resetErr = "Email not found in our system";
                }
            }
        }
    }
    
    if(isset($_POST["step"]) && $_POST["step"] == 2){
        
        $token = $_POST["token"];
        $new_password = $_POST["new_password"];
        $confirm_password = $_POST["confirm_password"];
        
        if(empty($new_password)){
            $resetErr = "Password is required";
        } elseif(strlen($new_password) < 8){
            $resetErr = "Password must be at least 8 characters";
        } elseif($new_password !== $confirm_password){
            $resetErr = "Passwords do not match";
        } else {
            $stmt = $conn->prepare("SELECT user_id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
            if(!$stmt) {
                $resetErr = "Database error: " . $conn->error;
            } else {
                $stmt->bind_param("s", $token);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if($result->num_rows == 1){
                    $row = $result->fetch_assoc();
                    $user_id = $row["user_id"];
                    
                    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                    $stmt2 = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE user_id = ?");
                    if(!$stmt2) {
                        $resetErr = "Database error: " . $conn->error;
                    } else {
                        $stmt2->bind_param("si", $hashed_password, $user_id);
                        
                        if($stmt2->execute()){
                            $successMsg = "Password reset successful! You can now login.";
                            $step = 1;
                        } else {
                            $resetErr = "Error updating password. Try again.";
                        }
                    }
                } else {
                    $resetErr = "Invalid or expired reset link";
                }
            }
        }
    }
}


$token_from_url = isset($_GET["token"]) ? $_GET["token"] : "";
if($token_from_url){
    $stmt = $conn->prepare("SELECT user_id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
    if(!$stmt) {
        $resetErr = "Database error: " . $conn->error;
    } else {
        $stmt->bind_param("s", $token_from_url);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($result->num_rows == 1){
            $step = 2;
        } else {
            $resetErr = "Invalid or expired reset link";
        }
    }
}
?>

<!DOCTYPE html>
<html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Forgot Password</title>
        <link rel='stylesheet' href='../style.css'>
        <link rel='icon' href='../assets/GREEN_FOLDER.png'>
    </head>
    
    <body id='login'>
        <br><br>
        <div class='folder'>
            <br><br>
            <h1>Reset Password</h1>
            
            <?php if($step == 1): ?>
                <form action='forgot.php' method='post'>
                    <label>Enter your email:</label><br>
                    <input type='email' name='email' required><br><br>
                    <input type='hidden' name='step' value='1'>
                    <input type='submit' value='Send Reset Link' class='login'>
                </form>
            <?php elseif($step == 2): ?>
                <form action='forgot.php' method='post'>
                    <label>New Password:</label><br>
                    <input type='password' name='new_password' required><br>
                    <label>Confirm Password:</label><br>
                    <input type='password' name='confirm_password' required><br><br>
                    <input type='hidden' name='token' value='<?php echo htmlspecialchars($token_from_url); ?>'>
                    <input type='hidden' name='step' value='2'>
                    <input type='submit' value='Reset Password' class='login'>
                </form>
            <?php endif; ?>
            
        </div>

        <a href="../acc/login.php">back to login</a><br>
        <a href="../acc/signup.php">create account</a>

        <div class='error-display'>
            <?php
                if($emailErr) echo $emailErr;
                if($resetErr) echo $resetErr;
                if($successMsg) echo "<span style='color: green;'>" . $successMsg . "</span>";
            ?>
        </div>
    </body>
</html>