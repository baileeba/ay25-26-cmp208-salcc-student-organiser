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
            header("Location: profile.php");
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
                <p id = 'contactBtn'>contact us</p>
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
                <form id="contactForm">
                    <label for="contactSubject">Subject:</label>
                    <input type="text" id="contactSubject" name="subject" required>
                    
                    <label for="contactMessage">Message:</label>
                    <textarea id="contactMessage" name="message" rows="5" required></textarea>
                    
                    <button type="submit">Send</button>
                    <button type="button" id="cancelContact">Cancel</button>
                </form>
            </div>
        </div>

        <script src = "js/navbar.js" defer></script>
        <script src = "js/profile.js" defer></script>
    </body>
</html>