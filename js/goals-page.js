document.addEventListener('DOMContentLoaded', function() {
    loadGoals();

    const createGoalForm = document.getElementById('createGoalForm');
    if (createGoalForm) {
        createGoalForm.addEventListener('submit', handleCreateGoal);
    }

    const updateGoalForm = document.getElementById('updateGoalForm');
    if (updateGoalForm) {
        updateGoalForm.addEventListener('submit', handleUpdateGoal);
    }

    const deleteGoalBtn = document.getElementById('deleteGoalBtn');
    if (deleteGoalBtn) {
        deleteGoalBtn.addEventListener('click', handleDeleteGoal);
    }

    const selectGoal = document.getElementById('selectGoal');
    if (selectGoal) {
        selectGoal.addEventListener('change', loadGoalDetails);
    }
});

function loadGoals() {
    fetch('php/fetch-goals.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayGoals(data.goals);
                populateGoalSelect(data.goals);
            } else {
                showMessage('createGoalMessage', 'Error loading goals', 'error');
            }
        })
        .catch(error => {
            console.error('Error fetching goals:', error);
            showMessage('createGoalMessage', 'Error loading goals', 'error');
        });
}

function displayGoals(goals) {
    const goalsContainer = document.getElementById('goalsContainer');
    const noGoalsMessage = document.getElementById('noGoalsMessage');

    if (!goals || goals.length === 0) {
        goalsContainer.innerHTML = '';
        noGoalsMessage.style.display = 'block';
        return;
    }

    noGoalsMessage.style.display = 'none';
    goalsContainer.innerHTML = '';

    goals.forEach(goal => {
        const goalCard = createGoalCard(goal);
        goalsContainer.appendChild(goalCard);
    });
}

function createGoalCard(goal) {
    const card = document.createElement('div');
    card.className = `goal-card goal-${goal.status}`;
    card.setAttribute('data-goal-id', goal.goal_id);

    const targetDate = new Date(goal.target_date);
    const formattedDate = targetDate.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });

    let statusClass = '';
    let statusIcon = '';
    switch (goal.status) {
        case 'completed':
            statusClass = 'status-completed';
            statusIcon = '<i class="fas fa-check-circle"></i>';
            break;
        case 'abandoned':
            statusClass = 'status-abandoned';
            statusIcon = '<i class="fas fa-times-circle"></i>';
            break;
        default:
            statusClass = 'status-active';
            statusIcon = '<i class="fas fa-circle"></i>';
    }

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const daysRemaining = Math.ceil((targetDate - today) / (1000 * 60 * 60 * 24));
    
    let daysText = '';
    if (daysRemaining > 0) {
        daysText = `${daysRemaining} days left`;
    } else if (daysRemaining === 0) {
        daysText = 'Today is the deadline';
    } else {
        daysText = `${Math.abs(daysRemaining)} days overdue`;
    }

    card.innerHTML = `
        <div class="goal-card-header">
            <h3 class="goal-title">${escapeHtml(goal.title)}</h3>
            <span class="goal-status ${statusClass}">
                ${statusIcon}
                ${goal.status}
            </span>
        </div>

        ${goal.description ? `<p class="goal-description">${escapeHtml(goal.description)}</p>` : ''}

        <div class="goal-progress">
            <div class="progress-bar-container">
                <div class="progress-bar-fill" style="width: ${goal.progress_percentage}%"></div>
            </div>
            <span class="progress-text">${goal.progress_percentage}% Complete</span>
        </div>

        <div class="goal-footer">
            <span class="goal-date">
                <i class="fas fa-calendar-alt"></i>
                ${formattedDate}
            </span>
            <span class="goal-deadline ${daysRemaining < 0 ? 'overdue' : ''}">
                ${daysText}
            </span>
        </div>

        <div class="goal-actions">
            <button class="goal-quick-edit-btn" onclick="selectGoalForEdit(${goal.goal_id})">
                <i class="fas fa-edit"></i> Edit
            </button>
        </div>
    `;

    return card;
}

function selectGoalForEdit(goalId) {
    const selectGoal = document.getElementById('selectGoal');
    selectGoal.value = goalId;
    selectGoal.dispatchEvent(new Event('change'));
    
    document.querySelector('.update-goal').scrollIntoView({ behavior: 'smooth' });
}

function populateGoalSelect(goals) {
    const selectGoal = document.getElementById('selectGoal');
    selectGoal.innerHTML = '<option value="">-- Choose a goal --</option>';

    goals.forEach(goal => {
        const option = document.createElement('option');
        option.value = goal.goal_id;
        option.textContent = `${goal.title} (${goal.status})`;
        selectGoal.appendChild(option);
    });
}

function loadGoalDetails() {
    const selectGoal = document.getElementById('selectGoal');
    const goalId = selectGoal.value;

    if (!goalId) {
        clearUpdateForm();
        return;
    }

    fetch('php/fetch-goals.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const goal = data.goals.find(g => g.goal_id == goalId);
                if (goal) {
                    populateUpdateForm(goal);
                }
            }
        })
        .catch(error => console.error('Error loading goal details:', error));
}

function populateUpdateForm(goal) {
    document.getElementById('updateGoalTitle').value = goal.title;
    document.getElementById('updateGoalDescription').value = goal.description || '';
    document.getElementById('updateGoalTargetDate').value = goal.target_date;
    document.getElementById('progressPercentage').value = goal.progress_percentage;
    document.getElementById('goalStatus').value = goal.status;
}

function clearUpdateForm() {
    document.getElementById('updateGoalTitle').value = '';
    document.getElementById('updateGoalDescription').value = '';
    document.getElementById('updateGoalTargetDate').value = '';
    document.getElementById('progressPercentage').value = '0';
    document.getElementById('goalStatus').value = 'active';
}

function handleCreateGoal(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const messageDiv = document.getElementById('createGoalMessage');

    fetch('php/create-goal.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('createGoalMessage', data.message, 'success');
            document.getElementById('createGoalForm').reset();
            loadGoals();
        } else {
            showMessage('createGoalMessage', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error creating goal:', error);
        showMessage('createGoalMessage', 'Error creating goal. Please try again.', 'error');
    });
}

function handleUpdateGoal(e) {
    e.preventDefault();

    const goalId = document.getElementById('selectGoal').value;

    if (!goalId) {
        showMessage('updateGoalMessage', 'Please select a goal to update', 'error');
        return;
    }

    const formData = new FormData(e.target);
    const messageDiv = document.getElementById('updateGoalMessage');

    fetch('php/update-goal.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('updateGoalMessage', data.message, 'success');
            loadGoals();
            setTimeout(() => {
                clearUpdateForm();
                document.getElementById('selectGoal').value = '';
            }, 1500);
        } else {
            showMessage('updateGoalMessage', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error updating goal:', error);
        showMessage('updateGoalMessage', 'Error updating goal. Please try again.', 'error');
    });
}

function handleDeleteGoal(e) {
    e.preventDefault();

    const goalId = document.getElementById('selectGoal').value;

    if (!goalId) {
        showMessage('updateGoalMessage', 'Please select a goal to delete', 'error');
        return;
    }

    if (!confirm('Are you sure you want to delete this goal? This action cannot be undone.')) {
        return;
    }

    const formData = new FormData();
    formData.append('goal_id', goalId);

    fetch('php/delete-goal.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage('updateGoalMessage', data.message, 'success');
            loadGoals();
            setTimeout(() => {
                clearUpdateForm();
                document.getElementById('selectGoal').value = '';
            }, 1500);
        } else {
            showMessage('updateGoalMessage', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error deleting goal:', error);
        showMessage('updateGoalMessage', 'Error deleting goal. Please try again.', 'error');
    });
}

function showMessage(elementId, message, type) {
    const messageDiv = document.getElementById(elementId);
    messageDiv.textContent = message;
    messageDiv.className = `form-message ${type}`;
    messageDiv.style.display = 'block';

    if (type === 'success') {
        setTimeout(() => {
            messageDiv.style.display = 'none';
        }, 5000);
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}