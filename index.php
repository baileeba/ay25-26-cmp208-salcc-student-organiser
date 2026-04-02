<?php
    session_start();
    include "acc/connect.php";

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
        <title>[placeholder]</title>
        <link rel= "stylesheet" href = "style.css">
    </head>

    <body>
        <div align = "center">
            <div class= "writeHeader">
        </div>

        <div class= "mini-calender">
            <div class= "header">
                <button id = "prevBtn">
                    <i class = "fa-solid fa-chervon-left"></i>
                </button>
                <div class= "monthYear" id = "monthYear"></div>
                <button id = "nextBtn">
                    <i class = "fa-solid fa-chervon-right"></i>
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
        <script src = "js/navbar.js" defer></script>
    </body>
</html>