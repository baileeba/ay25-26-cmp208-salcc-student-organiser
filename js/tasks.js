let allAssignments = [];
let allReminders = [];
let allCourses = [];


const fetchCourses = async () => {
    try {
        const response = await fetch('/api/search_users.php?action=get_courses');
        if (!response.ok) {
            throw new Error('Failed to fetch courses');
        }
        allCourses = await response.json();
        populateCourseDropdown();
    } catch (error) {
        console.error('Error fetching courses:', error);
    }
};


const populateCourseDropdown = () => {
    const courseSelect = document.getElementById('assignment-course');
    courseSelect.innerHTML = '<option value="">Select a course</option>';
    
    allCourses.forEach(course => {
        const option = document.createElement('option');
        option.value = course.course_id;
        option.textContent = `${course.course_code} - ${course.course_name}`;
        courseSelect.appendChild(option);
    });
};


const fetchAssignments = async () => {
    try {
        const response = await fetch('/api/assignments.php');
        if (!response.ok) {
            throw new Error('Failed to fetch assignments');
        }
        allAssignments = await response.json();
        displayAssignments();
    } catch (error) {
        console.error('Error fetching assignments:', error);
    }
};


const displayAssignments = () => {
    const container = document.getElementById('assignments-list');
    
    if (allAssignments.length === 0) {
        container.innerHTML = '<p class="no-items">No active assignments</p>';
        return;
    }

    container.innerHTML = allAssignments.map(assignment => `
        <div class="item assignment-item priority-${assignment.priority}">
            <div class="item-header">
                <h3>${assignment.title}</h3>
                <span class="course-badge">${assignment.course_code}</span>
            </div>
            <div class="item-body">
                <p class="course-name">${assignment.course_name}</p>
                ${assignment.description ? `<p class="description">${assignment.description}</p>` : ''}
                <div class="item-details">
                    <span class="due-date"><i class="fa-regular fa-calendar"></i> ${new Date(assignment.due_date).toLocaleDateString()}</span>
                    ${assignment.due_time ? `<span class="due-time"><i class="fa-regular fa-clock"></i> ${assignment.due_time}</span>` : ''}
                    <span class="priority badge-${assignment.priority}">${assignment.priority}</span>
                    <span class="status">${assignment.status.replace('_', ' ')}</span>
                    ${assignment.weight_percentage ? `<span class="weight">${assignment.weight_percentage}%</span>` : ''}
                </div>
            </div>
            <div class="item-actions">
                <button onclick="deleteAssignment(${assignment.id})" class="btn-delete">Delete</button>
            </div>
        </div>
    `).join('');
};


const fetchReminders = async () => {
    try {
        const response = await fetch('/api/reminders.php');
        if (!response.ok) {
            throw new Error('Failed to fetch reminders');
        }
        allReminders = await response.json();
        displayReminders();
    } catch (error) {
        console.error('Error fetching reminders:', error);
    }
};


const displayReminders = () => {
    const container = document.getElementById('reminders-list');
    
    if (allReminders.length === 0) {
        container.innerHTML = '<p class="no-items">No reminders</p>';
        return;
    }

    container.innerHTML = allReminders.map(reminder => `
        <div class="item reminder-item">
            <div class="item-color-bar" style="background-color: ${reminder.color || '#3498db'}"></div>
            <div class="item-body">
                <h3>${reminder.title}</h3>
                <div class="item-details">
                    <span class="reminder-date"><i class="fa-regular fa-calendar"></i> ${new Date(reminder.date).toLocaleDateString()}</span>
                </div>
            </div>
            <div class="item-actions">
                <button onclick="deleteReminder(${reminder.id})" class="btn-delete">Delete</button>
            </div>
        </div>
    `).join('');
};


document.getElementById('add-assignment-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('action', 'create');
    formData.append('course_id', document.getElementById('assignment-course').value);
    formData.append('title', document.getElementById('assignment-title').value);
    formData.append('description', document.getElementById('assignment-description').value);
    formData.append('due_date', document.getElementById('assignment-due-date').value);
    formData.append('due_time', document.getElementById('assignment-due-time').value);
    formData.append('priority', document.getElementById('assignment-priority').value);
    formData.append('weight_percentage', document.getElementById('assignment-weight').value);

    try {
        const response = await fetch('/api/assignments.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        
        if (result.error) {
            alert('Error: ' + result.error);
        } else {
            alert('Assignment added successfully!');
            document.getElementById('add-assignment-form').reset();
            fetchAssignments();
        }
    } catch (error) {
        console.error('Error adding assignment:', error);
        alert('Failed to add assignment');
    }
});


document.getElementById('add-reminder-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData();
    formData.append('action', 'create');
    formData.append('title', document.getElementById('reminder-text').value);
    formData.append('date', document.getElementById('reminder-date').value);
    formData.append('color', document.getElementById('reminder-color').value);

    try {
        const response = await fetch('/api/reminders.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        
        if (result.error) {
            alert('Error: ' + result.error);
        } else {
            alert('Reminder added successfully!');
            document.getElementById('add-reminder-form').reset();
            fetchReminders();
        }
    } catch (error) {
        console.error('Error adding reminder:', error);
        alert('Failed to add reminder');
    }
});


const deleteAssignment = async (assignmentId) => {
    if (!confirm('Are you sure you want to delete this assignment?')) return;
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('assignment_id', assignmentId);

    try {
        const response = await fetch('/api/assignments.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        
        if (result.error) {
            alert('Error: ' + result.error);
        } else {
            alert('Assignment deleted!');
            fetchAssignments();
        }
    } catch (error) {
        console.error('Error deleting assignment:', error);
        alert('Failed to delete assignment');
    }
};


const deleteReminder = async (reminderId) => {
    if (!confirm('Are you sure you want to delete this reminder?')) return;
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', reminderId);

    try {
        const response = await fetch('/api/reminders.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();
        
        if (result.error) {
            alert('Error: ' + result.error);
        } else {
            alert('Reminder deleted!');
            fetchReminders();
        }
    } catch (error) {
        console.error('Error deleting reminder:', error);
        alert('Failed to delete reminder');
    }
};

document.addEventListener('DOMContentLoaded', () => {
    fetchCourses();
    fetchAssignments();
    fetchReminders();
});
