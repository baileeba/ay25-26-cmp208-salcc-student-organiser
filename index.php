<?php

    session_start();
    include "connect.php";

    if(!isset($_SESSION["user_id"])) {
        header("Location: acc/login.php");
        exit();
    }
        
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
                <div class = "remember">
                <p>this is placeholder text, the reminders section would go here!</p>
                </div>
            </div>

            <div class= "section3">
                <div class = "upcoming">
                    <p>this is where upcoming reminders would go</p>
                </div>
            </div>
        </div>
        <script src = "js/navbar.js" defer></script>
        <script src = "js/calendar.js" defer></script>
    </body>
</html>