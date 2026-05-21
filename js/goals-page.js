document.addEventListener("DOMContentLoaded", function() {
    loadGoals();

    document.getElementById("createGoalForm").addEventListener("submit", function(e) {
        e.preventDefault();
        createGoal();
    });

    document.getElementById("updateGoalForm").addEventListener("submit", function(e) {
        e.preventDefault();
        updateGoal();
    });

    document.getElementById("deleteGoalBtn").addEventListener("click", function() {
        deleteGoal();
    });

    document.getElementById("selectGoal").addEventListener("change", function() {
        populateUpdateForm();
    });
});


function loadGoals() {
    fetch("api/fetch_goal.php")
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            const goalsContainer = document.getElementById("goalsContainer");
            const noGoalsMessage = document.getElementById("noGoalsMessage");
            const selectGoal = document.getElementById("selectGoal");


            goalsContainer.innerHTML = "";
            selectGoal.innerHTML = '<option value="">-- choose a goal! --</option>';

            if (Array.isArray(data) && data.length > 0) {

                noGoalsMessage.style.display = "none";

                data.forEach(goal => {
                    const option = document.createElement("option");
                    option.value = goal.goal_id;
                    option.textContent = goal.title;
                    selectGoal.appendChild(option);

                    const goalElement = document.createElement("div");
                    goalElement.className = "goal-item";
                    goalElement.innerHTML = `
                        <h4>${escapeHtml(goal.title)}</h4>
                        <p class="goal-description">${escapeHtml(goal.description || "")}</p>
                        <p class="goal-meta">
                            <strong>Target Date:</strong> ${goal.target_date}<br>
                            <strong>Progress:</strong> ${goal.progress_percentage}%<br>
                            <strong>Status:</strong> <span class="status-badge status-${goal.status}">${goal.status}</span>
                        </p>
                    `;
                    goalsContainer.appendChild(goalElement);
                });
            } else {
                noGoalsMessage.style.display = "block";
            }
        })
        .catch(error => {
            console.error("Error loading goals:", error);
        });
}


function createGoal() {
    const formData = new FormData(document.getElementById("createGoalForm"));
    
    fetch("api/create_goal.php", {
        method: "POST",
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showMessage("createGoalMessage", "Goal created successfully!", "success");
            document.getElementById("createGoalForm").reset();
            loadGoals();
        } else {
            showMessage("createGoalMessage", data.error || "Failed to create goal", "error");
        }
    })
    .catch(error => {
        console.error("Error creating goal:", error);
        showMessage("createGoalMessage", "Error: " + error.message, "error");
    });
}


function updateGoal() {
    const goalId = document.getElementById("selectGoal").value;
    
    if (!goalId) {
        showMessage("updateGoalMessage", "Please select a goal to update", "error");
        return;
    }

    const formData = new FormData(document.getElementById("updateGoalForm"));
    
    fetch("api/update_goal.php", {
        method: "POST",
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showMessage("updateGoalMessage", "Goal updated successfully!", "success");
            loadGoals();
            document.getElementById("updateGoalForm").reset();
        } else {
            showMessage("updateGoalMessage", data.error || "Failed to update goal", "error");
        }
    })
    .catch(error => {
        console.error("Error updating goal:", error);
        showMessage("updateGoalMessage", "Error: " + error.message, "error");
    });
}


function deleteGoal() {
    const goalId = document.getElementById("selectGoal").value;
    
    if (!goalId) {
        showMessage("updateGoalMessage", "Please select a goal to delete", "error");
        return;
    }

    if (!confirm("Are you sure you want to delete this goal?")) {
        return;
    }

    const formData = new FormData();
    formData.append("goal_id", goalId);

    fetch("api/delete_goal.php", {
        method: "POST",
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showMessage("updateGoalMessage", "Goal deleted successfully!", "success");
            loadGoals();
            document.getElementById("updateGoalForm").reset();
        } else {
            showMessage("updateGoalMessage", data.error || "Failed to delete goal", "error");
        }
    })
    .catch(error => {
        console.error("Error deleting goal:", error);
        showMessage("updateGoalMessage", "Error: " + error.message, "error");
    });
}


function populateUpdateForm() {
    const goalId = document.getElementById("selectGoal").value;
    
    if (!goalId) {
        document.getElementById("updateGoalForm").reset();
        return;
    }

    fetch("api/fetch_goal.php")
        .then(response => response.json())
        .then(data => {
            const goal = data.find(g => g.goal_id == goalId);
            if (goal) {
                document.getElementById("updateGoalTitle").value = goal.title;
                document.getElementById("updateGoalDescription").value = goal.description || "";
                document.getElementById("updateGoalTargetDate").value = goal.target_date || "";
                document.getElementById("progressPercentage").value = goal.progress_percentage || 0;
                document.getElementById("goalStatus").value = goal.status;
            }
        })
        .catch(error => console.error("Error populating form:", error));
}


function showMessage(elementId, message, type) {
    const messageElement = document.getElementById(elementId);
    messageElement.className = `form-message message-${type}`;
    messageElement.textContent = message;
    messageElement.style.display = "block";


    if (type === "success") {
        setTimeout(() => {
            messageElement.style.display = "none";
        }, 3000);
    }
}


function escapeHtml(text) {
    if (!text) return "";
    const map = {
        "&": "&amp;",
        "<": "&lt;",
        ">": "&gt;",
        '"': "&quot;",
        "'": "&#039;"
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}
