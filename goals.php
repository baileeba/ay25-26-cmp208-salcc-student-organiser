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

        <div class = 'goal-editor'>
            <div class="edit">
                <h2>your goals</h2>
            </div>

            <div class="add-goal">
                <h2>add goal</h2>

                <div class= "add">
                    <h2>create goal</h2>
                    <form id="createGoalForm">
                        <label for="goalTitle">goal title</label><br>
                        <input type="text" id="goalTitle" name="title" required><br>
                        
                        <label for="goalDescription">description</label><br>
                        <textarea id="goalDescription" name="description" rows="4"></textarea><br>
                        
                        <label for="goalDate">target date</label><br>
                        <input type="date" id="goalDate" name="target_date"><br><br>
                        
                        <button type="submit">create</button>
                    </form>
                    <p id="createGoalMessage"></p>
                </div>
                
                <div class = "update">
                    <h2>update goal</h2>
                    <form id="updateGoalForm">
                        <input type="hidden" id="goalId" name="goal_id">
                        
                        <label for="goalProgress">progress percentage</label>
                        <input type="number" id="goalProgress" name="progress_percentage" min="0" max="100" required>
                        
                        <label for="goalStatus">status</label>
                        <select id="goalStatus" name="status" required>
                            <option value="active">active</option>
                            <option value="completed">completed</option>
                            <option value="abandoned">abandoned</option>
                        </select><br><br>

                        <button type="submit">update</button>
                    </form>
                    <p id="updateGoalMessage"></p>
                </div>

            </div>
        </div>

        <script src="js/navbar.js" defer></script>
        <script src="js/goals-page.js" defer></script>
    </body>
</html>