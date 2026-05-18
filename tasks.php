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
            header("Location: tasks.php");
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
        <title>Tasks</title>
        <link rel = "stylesheet" href = "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel = "stylesheet" href = "style.css">
        <link rel = 'icon' href = 'assets/GREEN_FOLDER.png'>
    </head>

    <body>
        <div align = "center">
            <div class= "writeHeader">
        </div>

        <div class = "tasks">
            <div class = "assignments">
                <h2>Assignments</h2>
            </div>

            <div class = "add-assignment">
                <h2>Add Assignment</h2>
            </div>

            <div id = "reminders">
                <h2>Reminders</h2>
            </div>

            <div class = "add-reminder">
                <h2>Add Reminder</h2>
            </div>

        </div>

        <script src = "js/navbar.js" defer></script>
        <script src = "js/calendar.js" defer></script>
    </body>
</html>