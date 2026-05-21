let allAssignments = [];
let allReminders = [];
let allCourses = [];
let selectedCollaborators = [];


const fetchCourses = async () => {
    try {
        const response = await fetch('api/search_users.php?action=get_courses');
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
    courseSelect.innerHTML = '<option value="">course</option>';
    
    allCourses.forEach(course => {
        const option = document.createElement('option');
        option.value = course.course_id;
        option.textContent = `${course.course_code} - ${course.course_name}`;
        courseSelect.appendChild(option);
    });
};


const fetchFriends = async () => {
    try {
        const response = await fetch('api/assignments.php?action=get_friends');
        if (!response.ok) {
            throw new Error('Failed to fetch friends');
        }
        const friends = await response.json();
        populateFriendsDropdown(friends);
    } catch (error) {
        console.error('Error fetching friends:', error);
    }
};


const populateFriendsDropdown = (friends) => {
    const select = document.getElementById('assignment-collaborator');
    select.innerHTML = '<option value="">select a friend to add...</option>';
    
    friends.forEach(friend => {
        const option = document.createElement('option');
        option.value = friend.user_id;
        option.textContent = `${friend.name} (@${friend.username})`;
        select.appendChild(option);
    });
};


const addCollaborator = () => {
    const select = document.getElementById('assignment-collaborator');
    const collaborator_id = select.value;
    const collaborator_text = select.options[select.selectedIndex].text;
    
    if (!collaborator_id) {
        alert('please select a friend');
        return;
    }
    
    
    if (selectedCollaborators.find(c => c.id === parseInt(collaborator_id))) {
        alert('this person is already added');
        return;
    }
    
    selectedCollaborators.push({
        id: parseInt(collaborator_id),
        name: collaborator_text
    });
    
    renderCollaborators();
    select.value = '';
};


const renderCollaborators = () => {
    const container = document.getElementById('collaborators-list');
    container.innerHTML = '';
    
    selectedCollaborators.forEach(collaborator => {
        const chip = document.createElement('div');
        chip.className = 'collaborator-chip';
        chip.innerHTML = `
            ${collaborator.name}
            <button type="button" class="btn-remove" data-id="${collaborator.id}">×</button>
        `;
        
        chip.querySelector('.btn-remove').addEventListener('click', function(e) {
            e.preventDefault();
            selectedCollaborators = selectedCollaborators.filter(c => c.id !== collaborator.id);
            renderCollaborators();
        });
        
        container.appendChild(chip);
    });
};


const fetchAssignments = async () => {
    try {
        const response = await fetch('api/assignments.php?action=get_all');
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
        container.innerHTML = '<p class="no-items">no active assignments</p>';
        return;
    }

    container.innerHTML = allAssignments.map(assignment => `
        <div class="item assignment-item priority-${assignment.priority}" style="border-left: 4px solid ${assignment.color};">
            <button class="btn-delete-x" onclick="deleteAssignment(${assignment.id})" title="Delete assignment">×</button>
            <div class="item-header">
                <h3>${assignment.title}</h3>
                <span class="course-badge">${assignment.course_code}</span>
                ${assignment.is_group_assignment ? '<span class="badge badge-group">Group</span>' : ''}
            </div>
            <div class="item-body">
                <p class="course-name">${assignment.course_name}</p>
                ${assignment.description ? `<p class="description">${assignment.description}</p>` : ''}
                <div id="collaborators-${assignment.id}" class="assignment-collaborators"></div>
                <div class="item-details">
                    <span class="due-date"><i class="fa-regular fa-calendar"></i> ${(() => { const [y, m, d] = assignment.due_date.split('-'); return new Date(y, m-1, d).toLocaleDateString(); })()}</span>
                    ${assignment.due_time ? `<span class="due-time"><i class="fa-regular fa-clock"></i> ${assignment.due_time}</span>` : ''}
                    <span class="priority badge-${assignment.priority}">${assignment.priority}</span>
                    <span class="status">${assignment.status.replace('_', ' ')}</span>
                    ${assignment.weight_percentage ? `<span class="weight">${assignment.weight_percentage}%</span>` : ''}
                </div>
            </div>
        </div>
    `).join('');
    
    
    allAssignments.forEach(assignment => {
        if (assignment.is_group_assignment && assignment.group_id) {
            fetchCollaborators(assignment.id, assignment.group_id);
        }
    });
};


const fetchCollaborators = async (assignmentId, groupId) => {
    try {
        const response = await fetch(`api/assignments.php?action=get_collaborators&group_id=${groupId}`);
        if (!response.ok) {
            throw new Error('Failed to fetch collaborators');
        }
        const collaborators = await response.json();
        displayCollaborators(assignmentId, collaborators);
    } catch (error) {
        console.error('Error fetching collaborators:', error);
    }
};


const displayCollaborators = (assignmentId, collaborators) => {
    const container = document.getElementById(`collaborators-${assignmentId}`);
    
    if (collaborators.length === 0) {
        return;
    }
    
    let html = '<strong>Collaborators:</strong> ';
    html += collaborators.map(collab => 
        `<span class="collaborator-tag">${collab.name} (${collab.role})</span>`
    ).join(', ');
    
    container.innerHTML = html;
};


const assignmentForm = document.getElementById('add-assignment-form');
if (assignmentForm) {
    assignmentForm.addEventListener('submit', async (e) => {
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
        formData.append('color', document.getElementById('assignment-color').value);
        
        
        if (selectedCollaborators.length > 0) {
            formData.append('is_group_assignment', '1');
            selectedCollaborators.forEach((collab, index) => {
                formData.append(`collaborators[${index}]`, collab.id);
            });
        }

        try {
            const response = await fetch('api/assignments.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            
            if (result.error) {
                alert('Error: ' + result.error);
            } else {
                alert('Assignment added successfully!');
                document.getElementById('add-assignment-form').reset();
                selectedCollaborators = [];
                renderCollaborators();
                fetchAssignments();
            }
        } catch (error) {
            console.error('Error adding assignment:', error);
            alert('Failed to add assignment');
        }
    });
} else {
    console.error('add-assignment-form not found');
}


const reminderForm = document.getElementById('add-reminder-form');
if (reminderForm) {
    reminderForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const reminderText = document.getElementById('reminder-text').value;
        const reminderDate = document.getElementById('reminder-date').value;
        const reminderTime = document.getElementById('reminder-time').value;
        const reminderColor = document.getElementById('reminder-color').value;
        
        console.log('Submitting reminder:', { reminderText, reminderDate, reminderTime, reminderColor });
        
        const formData = new FormData();
        formData.append('action', 'create');
        formData.append('title', reminderText);
        formData.append('date', reminderDate);
        if (reminderTime) {
            formData.append('time', reminderTime);
        }
        formData.append('color', reminderColor);

        try {
            const response = await fetch('api/reminders.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();
            console.log('API Response:', result);
            
            if (result.error) {
                alert('Error: ' + result.error);
                console.error('Server error:', result);
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
} else {
    console.error('add-reminder-form not found');
}


const showDeleteConfirmation = (title, message, onConfirm) => {
    const modal = document.getElementById('deleteConfirmationModal');
    const messageElement = document.getElementById('deleteConfirmationMessage');
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    const cancelBtn = document.getElementById('cancelDeleteBtn');
    
    if (messageElement) {
        messageElement.textContent = message;
    }
    
    modal.style.display = 'block';
    
    const handleConfirm = async () => {
        modal.style.display = 'none';
        await onConfirm();
        cleanupEventListeners();
    };
    
    const handleCancel = () => {
        modal.style.display = 'none';
        cleanupEventListeners();
    };
    
    const cleanupEventListeners = () => {
        confirmBtn.removeEventListener('click', handleConfirm);
        cancelBtn.removeEventListener('click', handleCancel);
    };
    
    confirmBtn.addEventListener('click', handleConfirm);
    cancelBtn.addEventListener('click', handleCancel);
};


const deleteAssignment = async (assignmentId) => {
    showDeleteConfirmation('Delete Assignment', 'Are you sure you want to delete this assignment?', async () => {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('assignment_id', assignmentId);

        try {
            const response = await fetch('api/assignments.php', {
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
    });
};


const deleteReminder = async (reminderId) => {
    showDeleteConfirmation('Delete Reminder', 'Are you sure you want to delete this reminder?', async () => {
        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', reminderId);

        try {
            const response = await fetch('api/reminders.php', {
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
    });
};

const fetchReminders = async () => {
    try {
        const response = await fetch('api/reminders.php');
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
        container.innerHTML = '<p class="no-items">no reminders</p>';
        return;
    }

    container.innerHTML = allReminders.map(reminder => `
        <div class="item reminder-item">
            <button class="btn-delete-x" onclick="deleteReminder(${reminder.id})" title="Delete reminder">×</button>
            <div class="item-color-bar" style="background-color: ${reminder.color || '#3498db'}"></div>
            <div class="item-body">
                <h3>${reminder.title}</h3>
                <div class="item-details">
                    <span class="reminder-date"><i class="fa-regular fa-calendar"></i> ${(() => { const [y, m, d] = reminder.date.split('-'); return new Date(y, m-1, d).toLocaleDateString(); })()}</span>
                </div>
            </div>
        </div>
    `).join('');
};

document.addEventListener('DOMContentLoaded', () => {
    console.log('Tasks page loaded, initializing...');
    console.log('Form elements:', {
        assignmentForm: !!document.getElementById('add-assignment-form'),
        reminderForm: !!document.getElementById('add-reminder-form'),
        assignmentsList: !!document.getElementById('assignments-list'),
        remindersList: !!document.getElementById('reminders-list')
    });
    

    const btnAddCollab = document.getElementById('btn-add-collaborator');
    if (btnAddCollab) {
        btnAddCollab.addEventListener('click', (e) => {
            e.preventDefault();
            addCollaborator();
        });
    }
    
    fetchCourses();
    fetchFriends();
    fetchAssignments();
    fetchReminders();
});