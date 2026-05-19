<?php

    session_start();
    include "../acc/connect.php";
    require '../vendor/autoload.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    if(isset($_POST['submit'])) {
        $subject = $_POST['subject'];
        $email = $_POST['email'];
        $message = $_POST['message'];

        if(empty($subject) || empty($email) || empty($message)) {
            header("Location: ../profile.php?error=emptyfields");
        } else {
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'ayarmarks@gmail.com';
                $mail->Password = 'emgk fywu kyag thor'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                
                
                $mail->setFrom('ayarmarks@gmail.com', 'Student Organizer');
                $mail->addAddress('ayarmarks@gmail.com');
                $mail->addReplyTo($email, 'User');
                
            
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body = "<p><strong>From:</strong> {$email}</p><p><strong>Message:</strong></p><p>{$message}</p>";
                $mail->AltBody = "From: {$email}\n\nMessage:\n{$message}";

                if($mail->send()) {
                    header("Location: ../profile.php?success=messageSent");
                } else {
                    header("Location: ../profile.php?error=emailFailed&details=" . urlencode($mail->ErrorInfo));
                }
            } catch (Exception $e) {
                header("Location: ../profile.php?error=emailFailed&details=" . urlencode($e->getMessage()));
            }
        }
    } else {
        header("Location: ../profile.php");
    }
?>
