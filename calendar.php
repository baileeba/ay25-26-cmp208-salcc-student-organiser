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
        <title>Calendar</title>
        <link rel = "stylesheet" href = "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel = "stylesheet" href = "style.css">
        <link rel = 'icon' href = 'assets/GREEN_FOLDER.png'>
    </head>

    <body>
        <div align = "center">
            <div class= "writeHeader">
        </div>

            <div class="calendar-container">
                <div class="calendar-header">
                    <button id="prevWeek" class="week-nav-btn">&lt; Previous</button>
                    <h2 id="weekTitle">Week of</h2>
                    <button id="nextWeek" class="week-nav-btn">Next &gt;</button>
                </div>

                <div class="weekly-calendar">
                    <div id="monday" class="day-column">
                        <h3 class="day-name">M</h3>
                        <div class="events-container"></div>
                    </div>
                    <div id="tuesday" class="day-column">
                        <h3 class="day-name">T</h3>
                        <div class="events-container"></div>
                    </div>
                    <div id="wednesday" class="day-column">
                        <h3 class="day-name">W</h3>
                        <div class="events-container"></div>
                    </div>
                    <div id="thursday" class="day-column">
                        <h3 class="day-name">Th</h3>
                        <div class="events-container"></div>
                    </div>
                    <div id="friday" class="day-column">
                        <h3 class="day-name">F</h3>
                        <div class="events-container"></div>
                    </div>
                    <div id="saturday" class="day-column">
                        <h3 class="day-name">S</h3>
                        <div class="events-container"></div>
                    </div>
                    <div id="sunday" class="day-column">
                        <h3 class="day-name">Su</h3>
                        <div class="events-container"></div>
                    </div>
                </div>
            </div>

            <div class = "edit-category">
                <button id = "editCategoryBtn"><h2>edit category</h2></button>
            </div>

            <div class= "filter">
                <h2>filter by:</h2>
                <button id="filterAll" class="filter-btn active">all</button>
                <button id="filterReminders" class="filter-btn">reminders</button>
                <button id="filterClasses" class="filter-btn">classes</button>
                <button id="filterCategory" class="filter-btn">category</button>
            </div>

        <script src = "js/navbar.js" defer></script>
        <script src = "js/calendar.js" defer></script>
    </body>
</html>