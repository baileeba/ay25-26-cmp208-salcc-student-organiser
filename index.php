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
            header("Location: login.php");
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
        <title>StudSorter</title>
        <link rel = "stylesheet" href = "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel = "stylesheet" href = "style.css">
        <link rel = 'icon' href = 'assets/GREEN_FOLDER.png'>
    </head>

    <body>
        <div align = "center">
            <div class= "writeHeader">
        </div>

        <div class="dashboard">
            <div class = "section1">
                <div class= "mini-calendar">
                <div class= "header">
                        <button id = "prevBtn">
                            <i class = "fa-solid fa-chevron-left"></i>
                        </button>
                        <div class= "monthYear" id = "monthYear"></div>
                        <button id = "nextBtn">
                            <i class = "fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                    <div class = "days">
                        <div class = "day">Sun</div>
                        <div class = "day">Mon</div>
                        <div class = "day">Tue</div>
                        <div class = "day">Wed</div>
                        <div class = "day">Thu</div>
                        <div class = "day">Fri</div>
                        <div class = "day">Sat</div>
                    </div>
                    <div class = "dates" id = "dates"></div>
                </div>

                <div class= "goal">
                    <h1>goal</h1>
                    <p>placeholder text</p>
                </div>
            </div>

            <div class = 'section2'>
                <div class="remember">
                    <h2>reminders</h2>
                    <div id="remember">
                        <p class="no-reminders" id="defaultReminder">
                            select a date to view reminders!</p>
                    </div>
                </div>
            </div>

            <div class="section3">
                <div class="upcoming">
                    <h2>upcoming</h2>
                    <div id="upcoming"></div>
                </div>
            </div>

        </div>
        <script src = "js/navbar.js" defer></script>
        <script src = "js/calendar.js" defer></script>
        <script src = "js/upcoming.js" defer></script>
    </body>
</html>