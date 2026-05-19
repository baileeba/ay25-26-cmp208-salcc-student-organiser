document.addEventListener('DOMContentLoaded', function() {
    fetchUserGoal();
});

function fetchUserGoal() {
    fetch('api/fetch_goal.php')
        .then(response => response.json())
        .then(data => {
            displayGoal(data);
        })
        .catch(error => console.error('Error fetching goal:', error));
}

function displayGoal(data) {
    const goalContainer = document.getElementById('goal');
    const defaultGoal = document.getElementById('defaultGoal');

    if (Array.isArray(data) && data.length > 0) {
        const goal = data[0];
        
        defaultGoal.style.display = 'none';
        
        goalContainer.innerHTML = '';
        
        const targetDate = new Date(goal.target_date);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        targetDate.setHours(0, 0, 0, 0);
        const daysUntil = Math.ceil((targetDate - today) / (1000 * 60 * 60 * 24));
        
        
        const goalElement = document.createElement('div');
        goalElement.className = 'goal-card';
        goalElement.innerHTML = `
            <div class="goal-header">
                <h2>${escapeHtml(goal.title)}</h2>
                <span class="goal-status ${goal.status}">${goal.status}</span>
            </div>
            <p class="goal-description">${escapeHtml(goal.description || 'No description provided')}</p>
            <div class="goal-progress">
                <div class="progress-bar">
                    <div class="progress-fill" style="width: ${goal.progress_percentage}%"></div>
                </div>
                <p class="progress-text">${goal.progress_percentage}% complete</p>
            </div>
            <p class="goal-deadline">
                <i class="fa-solid fa-calendar"></i>
                Target: ${new Date(goal.target_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })}
                ${daysUntil > 0 ? `<span class="days-left">(${daysUntil} days)</span>` : daysUntil === 0 ? '<span class="due-today">(Due today!)</span>' : '<span class="overdue">(Overdue)</span>'}
            </p>
            <button class="view-goals-btn" onclick="window.location.href='goals.php'">View All Goals</button>
        `;
        
        goalContainer.appendChild(goalElement);
    } else {
        defaultGoal.style.display = 'block';
        goalContainer.innerHTML = defaultGoal.outerHTML;
    }
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}