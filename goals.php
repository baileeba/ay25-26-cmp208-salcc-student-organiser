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
            header("Location: goals.php");
            exit;
        }
    }

    $_SESSION['last_activity'] = time();
        
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Goals</title>

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="style.css">
        <link rel="icon" href="assets/GREEN_FOLDER.png">
    </head>

    <body>
        <div align = "center">
            <div class= "writeHeader">
        </div>
        <h1>goal editor</h1>

        <div class = "goals-page">
        <div class = "goal-display">
            <h2>goals</h2>
            <div id="goalsContainer" class="goals-container"></div>
            <div id="noGoalsMessage" class="no-goals-message">
                <p>no goals yet! create one to get started!</p>
            </div>
        </div>

        <div class= "goal-forms">
            <div class= "add-goal">
                <h3>create new goal</h3>
                
                <form id="createGoalForm" method="POST" action="api/create_goal.php">

                    <label for="goalTitle">goal title</label>
                    <input type="text" id="goalTitle" name="title" required maxlength="150">


                    <label for="goalDescription">description</label>
                    <textarea id="goalDescription" name="description" rows="4"></textarea>

                    <label for="goalTargetDate">target date</label>
                    <input type="date" id="goalTargetDate" name="target_date" required>
                
                    <button type="submit" class="btn btn-primary">create</button>
                </form>
                <div id="createGoalMessage" class="form-message"></div>
            </div>

            <div class= "update-goal">
                <h3>update goal</h3>
                <form id="updateGoalForm" method="POST" action="api/update_goal.php">
                    <label for="selectGoal">select goal</label>
                    <select id="selectGoal" name="goal_id" required>
                        <option value="">-- choose a goal! --</option>
                    </select>
                    
                    <label for="updateGoalTitle">goal title</label>
                    <input type="text" id="updateGoalTitle" name="title" maxlength="150">

                    <label for="updateGoalDescription">description</label>
                    <textarea id="updateGoalDescription" name="description" rows="4"></textarea>

                    <label for="updateGoalTargetDate">target date</label>
                    <input type="date" id="updateGoalTargetDate" name="target_date">

                    <label for="progressPercentage">progress (%)</label>
                    <input type="number" id="progressPercentage" name="progress_percentage" min="0" max="100">

                    <label for="goalStatus">status</label>
                    <select id="goalStatus" name="status" required>
                        <option value="active">active</option>
                        <option value="completed">completed</option>
                        <option value="abandoned">abandoned</option>
                    </select>

                    <button type="submit" class="btn btn-primary">update</button>
                    <button type="button" class="btn btn-danger" id="deleteGoalBtn">delete</button>
                </form>
                <div id="updateGoalMessage" class="form-message"></div>
            </div>
        </div>
        </div>

        <script src="js/navbar.js" defer></script>
        <script src="js/goals-page.js" defer></script>
    </body>
</html>