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
            <div class = 'images'><
                <img src = 'assets/PFPDEFAULT.png' class = 'profile'>
                <img src= "assets/HELLOBUBBLE.png" class = 'bubble'>
            </div>

            <div class = 'box1'>
                <p id = 'editProfileBtn'>edit profile</p>
                <p>edit categories</p>
                <p>import SONIS schedule</p>
                <p>request email notifs</p>
                <p>contact us</p>
            </div>

            <div class = 'box2'>
                <p>log out</p>
                <p>change password</p>
                <p>sync to phone</p>
            </div>
        </div>

        <script src = "js/navbar.js" defer></script>
    </body>
</html>