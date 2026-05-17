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
                <h3>Create New Goal</h3>
                
                <form id="createGoalForm" method="POST" action="php/create-goal.php">

                    <label for="goalTitle">Goal Title</label>
                    <input type="text" id="goalTitle" name="title" required maxlength="150">


                    <label for="goalDescription">Description</label>
                    <textarea id="goalDescription" name="description" placeholder="Enter goal description" rows="4"></textarea>

                    <label for="goalTargetDate">Target Date</label>
                    <input type="date" id="goalTargetDate" name="target_date" required>
                
                    <button type="submit" class="btn btn-primary">Create Goal</button>
                </form>
                <div id="createGoalMessage" class="form-message"></div>
            </div>

            <div class= "update-goal">
                <h3>Update Goal</h3>
                <form id="updateGoalForm" method="POST" action="php/update-goal.php">
                    <label for="selectGoal">Select Goal</label>
                    <select id="selectGoal" name="goal_id" required>
                        <option value="">-- Choose a goal --</option>
                    </select>
                    
                    <label for="updateGoalTitle">Goal Title</label>
                    <input type="text" id="updateGoalTitle" name="title" maxlength="150">

                    <label for="updateGoalDescription">Description</label>
                    <textarea id="updateGoalDescription" name="description" rows="4"></textarea>

                    <label for="updateGoalTargetDate">Target Date</label>
                    <input type="date" id="updateGoalTargetDate" name="target_date">

                    <label for="progressPercentage">Progress (%)</label>
                    <input type="number" id="progressPercentage" name="progress_percentage" min="0" max="100">

                    <label for="goalStatus">Status</label>
                    <select id="goalStatus" name="status" required>
                        <option value="active">Active</option>
                        <option value="completed">Completed</option>
                        <option value="abandoned">Abandoned</option>
                    </select>

                    <button type="submit" class="btn btn-primary">Update Goal</button>
                    <button type="button" class="btn btn-danger" id="deleteGoalBtn">Delete Goal</button>
                </form>
                <div id="updateGoalMessage" class="form-message"></div>
            </div>
        </div>
        </div>

        <script src="js/navbar.js" defer></script>
        <script src="js/goals-page.js" defer></script>
    </body>
</html>