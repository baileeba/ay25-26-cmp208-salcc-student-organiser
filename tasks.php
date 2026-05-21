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

            <div class="tasks">
                <div class= "assignments">
                    <h2>Assignments</h2>
                    <div id="assignments-list" class="list-container"></div>
                </div>

                <div class= "add-assignment">
                    <h2>Add Assignment</h2>
                    <form id="add-assignment-form" class="task-form">
                        <div class="form-group">
                            <select id="assignment-course" required></select>
                        </div>
                        <div class="form-group">
                            <input type="text" id="assignment-title" placeholder="title" required>
                        </div>
                        <div class="form-group">
                            <label for="assignment-collaborator">Add Collaborators:</label>
                            <select id="assignment-collaborator"></select>
                            <button type="button" id="btn-add-collaborator" class="btn-secondary">Add</button>
                        </div>
                        <div id="collaborators-list" class="collaborators-list"></div>
                        <div class="form-group">
                            <textarea id="assignment-description" placeholder="description"></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="assignment-due-date">Due Date:</label>
                                <input type="date" id="assignment-due-date" required>
                            </div>
                            <div class="form-group">
                                <label for="assignment-due-time">Due Time:</label>
                                <input type="time" id="assignment-due-time">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="assignment-priority">Priority:</label>
                                <select id="assignment-priority">
                                    <option value="small">Small</option>
                                    <option value="medium" selected>Medium</option>
                                    <option value="large">Large</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="assignment-weight">Weight (%):</label>
                                <input type="number" id="assignment-weight" min="0" max="100">
                            </div>
                            <div class="form-group">
                                <label for="assignment-color">Color:</label>
                                <input type="color" id="assignment-color" value="#3498db">
                            </div>
                        </div>
                        <button type="submit" class="btn-primary">Add Assignment</button>
                    </form>
                </div>

                <div id = "reminders">
                    <h2>Reminders</h2>
                    <div id="reminders-list" class="list-container"></div>
                </div>

                <div class= "add-reminder">
                    <h2>Add Reminder</h2>
                    <form id="add-reminder-form" class="task-form">
                        <div class="form-group">
                            <label for="reminder-text">Reminder:</label>
                            <textarea id="reminder-text" required></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="reminder-date">Date:</label>
                                <input type="date" id="reminder-date" required>
                            </div>
                            <div class="form-group">
                                <label for="reminder-time">Time:</label>
                                <input type="time" id="reminder-time">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="reminder-color">Color:</label>
                            <input type="color" id="reminder-color" value="#3498db">
                        </div>
                        <button type="submit" class="btn-primary">Add Reminder</button>
                    </form>
                </div>
            </div>
            <!-- collaborator popup -->
        <div class="collaborator-popup" id="collaboratorPopup">
            <div class="collaborator-popup-content">
                <button class="close-modal" id="closeCollaboratorPopup">&times;</button>
                <h3>add collaborator</h3>
                <input type="text" id="collaboratorEmail" placeholder="email@salcc.edu">
                <button class="send-btn" onclick="document.getElementById('collaboratorPopup').style.display='none'">send</button>
            </div>
        </div>

        <!-- delete confirmation modal -->
        <div class="delete-confirmation-modal" id="deleteConfirmationModal">
            <div class="delete-confirmation-content">
                <h3>Confirm Delete</h3>
                <p id="deleteConfirmationMessage">Are you sure you want to delete this item?</p>
                <div class="delete-confirmation-buttons">
                    <button class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
                    <button class="btn btn-secondary" id="cancelDeleteBtn">Cancel</button>
                </div>
            </div>
        </div>

        <script src = "js/navbar.js" defer></script>
        <script src = "js/tasks.js" defer></script>
    </body>
</html>