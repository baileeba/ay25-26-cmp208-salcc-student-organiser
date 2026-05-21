<?php
include "connect.php";
include "../config/email_config.php";
require "../vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$name = $username = $email = $password = "";
$nameErr = $usernameErr = $emailErr = $passwordErr = "";

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    if(empty($_POST['name'])){
        $nameErr = "Name is required";
    } else {
        $name = $_POST['name'];
    }

    if(empty($_POST['username'])){
        $usernameErr = "Username is required";
    } else {
        $username = $_POST['username'];
    }

    if(empty($_POST['email'])){
        $emailErr = "Email is required";
    } else {
        $email = $_POST['email'];
    }

    if(empty($_POST['password']) || empty($_POST['confirm_password'])){
        $passwordErr = "Password is required";
    } elseif($_POST['password'] != $_POST['confirm_password']){
        $passwordErr = "Passwords do not match";
    } else {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    }

    if ($nameErr == "" && $emailErr == "" && $usernameErr == "" && $passwordErr == "") {

        $stmt = $conn->prepare("INSERT INTO users (name, username, email, password) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss", $name, $username, $email, $password);

        if ($stmt->execute()) {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = SMTP_HOST;
                $mail->SMTPAuth = true;
                $mail->Username = SMTP_USER;
                $mail->Password = SMTP_PASS;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = SMTP_PORT;

                $mail->setFrom(SENDER_EMAIL, SENDER_NAME);
                $mail->addAddress($email, $name);

                $mail->isHTML(true);
                $mail->Subject = 'Welcome to StudSort!';
                
                $emailBody = "
                    <html>
                    <head>
                        <style>
                            body { font-family: Arial, sans-serif; }
                            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                            .header { background-color: #4CAF50; color: white; padding: 20px; text-align: center; border-radius: 5px 5px 0 0; }
                            .content { background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-radius: 0 0 5px 5px; }
                            .details { background-color: white; padding: 15px; margin: 15px 0; border-left: 4px solid #4CAF50; }
                            .details p { margin: 8px 0; }
                            .label { font-weight: bold; color: #333; }
                        </style>
                    </head>
                    <body>
                        <div class='container'>
                            <div class='header'>
                                <h1>Welcome to StudSort!</h1>
                            </div>
                            <div class='content'>
                                <p>Hi <strong>" . htmlspecialchars($name) . "</strong>,</p>
                                <p>Thank you for signing up with StudSort! Your account has been successfully created.</p>
                                
                                <div class='details'>
                                    <p><span class='label'>Account Details:</span></p>
                                    <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
                                    <p><strong>Username:</strong> " . htmlspecialchars($username) . "</p>
                                    <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                                </div>
                                
                                <p>You can now log in to your account and start organizing your academic life with StudSort.</p>
                                <p>If you have any questions or need assistance, feel free to contact us.</p>
                                
                                <p>Best regards,<br><strong>StudSort Team</strong></p>
                            </div>
                        </div>
                    </body>
                    </html>
                ";
                
                $mail->Body = $emailBody;
                $mail->send();
                echo "Thank you for signing up! A welcome email has been sent to your account.";
            } catch (Exception $e) {
                echo "Thank you for signing up! However, there was an issue sending the welcome email. Error: {$mail->ErrorInfo}";
            }
        } else {
            echo "Error: ".$stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang = 'en'> 
	<head>
		<meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">
		<title>Sign Up</title>
        <link rel = "stylesheet" href = "../style.css">
        <link rel = 'icon' href = 'assets/GREEN_FOLDER.png'>
	</head>
	
	<body class="signup-body">
		<h1>Sign Up</h1>
        <div class="signup-card">
		<form action = "signup.php" method = "post">
			<label>Full Name:</label><br>
			<input type = "text" name = "name"><br><br>
			<label>Username:</label><br>
			<input type="text" name = "username"><br><br>
			<label>Email:</label><br>
			<input type = "email" name = "email"><br><br>
			<label>Password:</label><br>
			<input type="password" name = "password"><br><br>
			<label>Confirm Password:</label><br>
			<input type = "password" name = "confirm_password"><br><br>
			<input type = "submit" value = "Sign Up">
		</form>

        </div>

        <a href= "login.php">back</a>
	</body>
	</html>