<!DOCTYPE html>
<html lang = "en">
    <head>
        <meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">
        <title>Calender</title>
        <link rel = "stylesheet" href = "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel = "stylesheet" href = "style.css">
        <link rel = 'icon' href = 'assets/GREEN_FOLDER.png'>
    </head>

    <body>
        <div align = "center">
            <div class= "writeHeader"></div>

            <div class="calendar-container">
                <div class="calendar-header">
                    <button id="prevWeek" class="week-nav-btn">&lt; Previous</button>
                    <h2 id="weekTitle">Week of</h2>
                    <button id="nextWeek" class="week-nav-btn">Next &gt;</button>
                </div>

                <div class="weekly-calendar">
                    <div id="monday" class="day-column">
                        <h3 class="day-name">Monday</h3>
                        <div class="events-container"></div>
                    </div>
                    <div id="tuesday" class="day-column">
                        <h3 class="day-name">Tuesday</h3>
                        <div class="events-container"></div>
                    </div>
                    <div id="wednesday" class="day-column">
                        <h3 class="day-name">Wednesday</h3>
                        <div class="events-container"></div>
                    </div>
                    <div id="thursday" class="day-column">
                        <h3 class="day-name">Thursday</h3>
                        <div class="events-container"></div>
                    </div>
                    <div id="friday" class="day-column">
                        <h3 class="day-name">Friday</h3>
                        <div class="events-container"></div>
                    </div>
                    <div id="saturday" class="day-column">
                        <h3 class="day-name">Saturday</h3>
                        <div class="events-container"></div>
                    </div>
                    <div id="sunday" class="day-column">
                        <h3 class="day-name">Sunday</h3>
                        <div class="events-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <script src = "js/navbar.js" defer></script>
        <script src = "js/calendar.js" defer></script>
    </body>
</html>