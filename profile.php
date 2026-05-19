<?php

    session_start();
    include "acc/connect.php";

    if(!isset($_SESSION["user_id"])) {
        header("Location: acc/login.php");
        exit();
    }

    $timeout_duration = 1800;

    if(isset($_SESSION['last_activity'])) {
        $idle_time = time() - $_SESSION['last_activity'];
        if($idle_time > $timeout_duration) {
            session_unset();
            session_destroy();
            header("Location: index.php");
            exit;
        }
    }

    $_SESSION['last_activity'] = time();
        
?>

<!DOCTYPE html>
<html lang = "en">
    <head>
        <meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">
        <title>Profile</title>
        <link rel = "stylesheet" href = "style.css">
        <link rel = 'icon' href = 'assets/GREEN_FOLDER.png'>
    </head>

    <body>
        <div align = "center">
            <div class= "writeHeader">
        </div>

        <div class= "profile-dash">
            <div class = 'images'>
                <img src = 'assets/PFPDEFAULT.png' class = 'profile'>
                <img src= "assets/HELLOBUBBLE.png" class = 'bubble'>
            </div>

            <div class = 'box1'>
                <p id = 'editProfileBtn'>edit profile</p>
                <p id = 'editCategoriesBtn'>edit categories</p>
                <p id = "importBtn">import SONIS schedule</p>
                <p id = "emailNotifsBtn">request email notifs</p>
                <div class = "contact-us">
                    <p id = 'contactBtn'>contact us</p>
                </div>
            </div>

            <div class = 'box2'>
                <p id = 'friendsBtn'>friends</p>
                <p id = "changePasswordBtn">change password</p>
                <p id = "logoutBtn">log out</p>
            </div>
        </div>

        <div id="contactModal">
            <div id="contactModalContent">
                <div id="contactModalHeader">
                    <h2>contact us</h2>
                    <span id="closeContactModal">&times;</span>
                </div>
                <div id="contactValidationError" style="display: none;">
                    <p id="contactErrorMessage">All fields are required.</p>
                </div>
                <?php
                    if(isset($_GET['error'])) {
                        if($_GET['error'] === 'emptyfields') {
                            echo '<p id="contactErrorMessage">All fields are required.</p>';
                        } elseif($_GET['error'] === 'emailFailed') {
                            $details = isset($_GET['details']) ? htmlspecialchars($_GET['details']) : 'Unknown error';
                            echo '<p id="contactErrorMessage">Failed to send email. Error: ' . $details . '</p>';
                        }
                    }

                    if(isset($_GET['success'])) {
                        echo '<p id="contactSuccessMessage">Your message has been sent!</p>';
                    }
                    ?>
                <form id="contactForm" action = "./api/contact.php" method="POST">
                    <label for="contactSubject">Subject:</label>
                    <input type="text" id="contactSubject" name="subject" value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>">

                    <label for="contactEmail">Your Email:</label>
                    <input type="email" id="contactEmail" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    
                    <label for="contactMessage">Message:</label>
                    <textarea id="contactMessage" name="message" rows="5"><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                    
                    <button type="submit" name = "submit">Send</button>
                    <button type="button" id="cancelContact">Cancel</button>
                </form>
            </div>
        </div>

        <div id="emailNotifModal">
            <div id="emailNotifModalContent">
                <div id="emailNotifModalHeader">
                    <span id="closeEmailNotifModal">&times;</span>
                </div>
                <div id="emailNotifModalBody">
                    <h2>Email Notifications Enabled!</h2>
                    <p>You will now receive email reminders for your upcoming assignments, events, and classes.</p>
                    <button id="closeEmailNotifBtn">Got it</button>
                </div>
            </div>
        </div>

        <div id="importModal">
            <div id="importModalContent">
                <div id="importModalHeader">
                    <h2>Import SONIS Schedule</h2>
                    <span id="closeImportModal">&times;</span>
                </div>
                <div id="importValidationError" style="display: none;">
                    <p id="importErrorMessage"></p>
                </div>
                <div id="importSuccessMessage" style="display: none;">
                    <p>Your schedule has been imported successfully!</p>
                </div>
                <form id="importForm" enctype="multipart/form-data">
                    <label for="pdfFile">Select PDF File:</label>
                    <input type="file" id="pdfFile" name="pdfFile" accept=".pdf" required>
                    <p style="font-size: 12px; color: #666; margin-top: 5px;">Please upload your SONIS schedule PDF file</p>
                    
                    <button type="submit" name="submit">Upload and Import</button>
                    <button type="button" id="cancelImport">Cancel</button>
                </form>
            </div>
        </div>

        <script src = "js/navbar.js" defer></script>
        <script src = "js/profile.js" defer></script>
    </body>
</html>