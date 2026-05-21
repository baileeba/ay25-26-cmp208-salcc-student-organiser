let currentWeekDate = new Date();
let weekEvents = [];
let allCourses = [];
let currentFilter = 'all';
let editingCourseId = null;

const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
const dayIds = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

// get monday of the current week
const getMondayOfWeek = (date) => {
    const d = new Date(date);
    const day = d.getDay();
    const diff = d.getDate() - day + (day === 0 ? -6 : 1);
    return new Date(d.setDate(diff));
};

// format date for display
const formatDate = (date) => {
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
};

// get weekly calendar data
const fetchWeeklyCalendar = async (weekDate) => {
    try {
        const monday = getMondayOfWeek(weekDate);
        const mondayString = `${monday.getFullYear()}-${String(monday.getMonth() + 1).padStart(2, '0')}-${String(monday.getDate()).padStart(2, '0')}`;
        const response = await fetch(`api/weekly_calendar.php?monday=${mondayString}`);
        if (!response.ok) {
            throw new Error('Failed to fetch calendar');
        }
        weekEvents = await response.json();
        updateWeeklyDisplay(weekDate);
    } catch (error) {
        console.error('Error fetching calendar:', error);
        updateWeeklyDisplay(weekDate);
    }
};

// Load all courses
const loadCourses = async () => {
    try {
        const response = await fetch('api/manage_categories.php?action=get');
        const data = await response.json();
        if (data.success) {
            allCourses = data.categories;
            displayCoursesList();
        }
    } catch (error) {
        console.error('Error loading courses:', error);
    }
};

// Display courses in the modal
const displayCoursesList = () => {
    const container = document.getElementById('coursesContainer');
    if (!container) return;

    if (allCourses.length === 0) {
        container.innerHTML = '<p class="no-courses">No courses yet. Add one to get started!</p>';
        return;
    }

    container.innerHTML = allCourses.map(course => `
        <div class="course-item">
            <div class="course-item-info">
                <div class="course-item-name">${course.name}</div>
                <div class="course-item-code">${course.course_code || 'N/A'}</div>
            </div>
            <div class="course-item-actions">
                <button class="course-edit-btn" onclick="editCourse(${course.id})">edit</button>
                <button class="course-delete-btn" onclick="deleteCourse(${course.id})">delete</button>
            </div>
        </div>
    `).join('');
};

// Show courses list
const showCoursesList = () => {
    document.getElementById('coursesList').style.display = 'block';
    document.getElementById('courseForm').style.display = 'none';
    displayCoursesList();
};

// Edit course
const editCourse = async (courseId) => {
    try {
        const response = await fetch(`api/manage_categories.php?action=fetch&id=${courseId}`);
        const data = await response.json();
        
        if (data.success) {
            const course = data.course;
            const schedules = data.schedules || [];
            
            editingCourseId = courseId;
            document.getElementById('coursesList').style.display = 'none';
            document.getElementById('courseForm').style.display = 'block';
            document.getElementById('formTitle').textContent = 'edit course';
            
            document.getElementById('courseName').value = course.course_name || '';
            document.getElementById('courseCode').value = course.course_code || '';
            document.getElementById('courseInstructor').value = course.instructor || '';
            
            // Clear and populate time slots
            const timeSlotsContainer = document.getElementById('timeSlotsContainer');
            timeSlotsContainer.innerHTML = '';
            
            if (schedules.length > 0) {
                schedules.forEach((schedule, index) => {
                    addTimeSlot(schedule, index);
                });
            } else {
                // Add one empty time slot for new schedules
                addTimeSlot(null, 0);
            }
        }
    } catch (error) {
        console.error('Error loading course:', error);
        alert('Error loading course details');
    }
};

// Add time slot to form (for displaying existing or new slots)
const addTimeSlot = (schedule = null, index = 0) => {
    const timeSlotsContainer = document.getElementById('timeSlotsContainer');
    const slotDiv = document.createElement('div');
    slotDiv.className = 'time-slot';
    slotDiv.id = `timeSlot-${index}`;
    
    slotDiv.innerHTML = `
        <div class="time-slot-content">
            <div class="time-slot-header">
                <h5>class time ${index + 1}</h5>
                ${index > 0 ? '<button type="button" class="remove-time-slot-btn" onclick="removeTimeSlot(' + index + ')">✕ remove</button>' : ''}
            </div>
            
            <label>day of week</label>
            <select name="dayOfWeek[]" class="day-select" required>
                <option value="">-- select day --</option>
                <option value="Monday" ${schedule?.day_of_week === 'Monday' ? 'selected' : ''}>Monday</option>
                <option value="Tuesday" ${schedule?.day_of_week === 'Tuesday' ? 'selected' : ''}>Tuesday</option>
                <option value="Wednesday" ${schedule?.day_of_week === 'Wednesday' ? 'selected' : ''}>Wednesday</option>
                <option value="Thursday" ${schedule?.day_of_week === 'Thursday' ? 'selected' : ''}>Thursday</option>
                <option value="Friday" ${schedule?.day_of_week === 'Friday' ? 'selected' : ''}>Friday</option>
                <option value="Saturday" ${schedule?.day_of_week === 'Saturday' ? 'selected' : ''}>Saturday</option>
                <option value="Sunday" ${schedule?.day_of_week === 'Sunday' ? 'selected' : ''}>Sunday</option>
            </select>
            
            <label>start time</label>
            <input type="time" name="startTime[]" value="${schedule?.start_time || ''}" required>
            
            <label>end time</label>
            <input type="time" name="endTime[]" value="${schedule?.end_time || ''}" required>
            
            <label>location</label>
            <input type="text" name="location[]" placeholder="e.g., Room 101" value="${schedule?.location || ''}">
        </div>
    `;
    
    timeSlotsContainer.appendChild(slotDiv);
};

// Remove time slot
const removeTimeSlot = (index) => {
    const slotDiv = document.getElementById(`timeSlot-${index}`);
    if (slotDiv) {
        slotDiv.remove();
    }
};

// Show add course form
const showAddCourseForm = () => {
    editingCourseId = null;
    document.getElementById('coursesList').style.display = 'none';
    document.getElementById('courseForm').style.display = 'block';
    document.getElementById('formTitle').textContent = 'add course';
    document.getElementById('courseFormElement').reset();
    
    // Add one empty time slot
    const timeSlotsContainer = document.getElementById('timeSlotsContainer');
    timeSlotsContainer.innerHTML = '';
    addTimeSlot(null, 0);
};

// Delete course
const deleteCourse = async (courseId) => {
    if (!confirm('Are you sure you want to delete this course? This action cannot be undone.')) {
        return;
    }
    
    try {
        const response = await fetch(`api/manage_categories.php?action=delete&id=${courseId}`, {
            method: 'POST'
        });
        const data = await response.json();
        
        if (data.success) {
            alert('Course deleted successfully');
            loadCourses();
            fetchWeeklyCalendar(currentWeekDate);
        } else {
            alert('Error deleting course: ' + (data.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error deleting course:', error);
        alert('Error deleting course');
    }
};

// Save course (add or update)
const saveCourse = async (e) => {
    e.preventDefault();
    
    const courseName = document.getElementById('courseName').value.trim();
    const courseCode = document.getElementById('courseCode').value.trim();
    const courseInstructor = document.getElementById('courseInstructor').value.trim();
    
    if (!courseName) {
        alert('Course name is required');
        return;
    }
    
    // Collect all time slots (brackets need to be escaped in CSS selectors)
    const daysOfWeek = document.querySelectorAll('select[name="dayOfWeek\\[\\]"]');
    const startTimes = document.querySelectorAll('input[name="startTime\\[\\]"]');
    const endTimes = document.querySelectorAll('input[name="endTime\\[\\]"]');
    const locations = document.querySelectorAll('input[name="location\\[\\]"]');
    
    // Validate at least one time slot is filled
    let hasValidTimeSlot = false;
    const timeSlots = [];
    
    for (let i = 0; i < daysOfWeek.length; i++) {
        const day = daysOfWeek[i]?.value || '';
        const start = startTimes[i]?.value || '';
        const end = endTimes[i]?.value || '';
        const location = locations[i]?.value || '';
        
        // Check if this slot has at least day and times
        if (day || start || end) {
            if (!day || !start || !end) {
                alert('Please fill in all fields for each class time (day, start time, and end time are required)');
                return;
            }
            hasValidTimeSlot = true;
            timeSlots.push({ day, start, end, location });
        }
    }
    
    // Optional: Allow courses without times (can be added later)
    // if (!hasValidTimeSlot) {
    //     alert('Please add at least one class time');
    //     return;
    // }
    
    const formData = new FormData();
    formData.append('action', editingCourseId ? 'update' : 'add');
    formData.append('name', courseName);
    formData.append('code', courseCode);
    formData.append('instructor', courseInstructor);
    formData.append('schedules', JSON.stringify(timeSlots));
    
    if (editingCourseId) {
        formData.append('course_id', editingCourseId);
        formData.append('course_name', courseName);
        formData.append('course_code', courseCode);
    }
    
    try {
        const response = await fetch('api/manage_categories.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        
        if (data.success) {
            alert(data.message || 'Course saved successfully');
            loadCourses();
            fetchWeeklyCalendar(currentWeekDate);
            showCoursesList();
        } else {
            alert('Error: ' + (data.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error saving course:', error);
        alert('Error saving course');
    }
};

// get events for a specific date
const getEventsForDate = (dateString) => {
    if (!weekEvents.events) return [];
    
    let events = weekEvents.events.filter(event => event.date === dateString);
    
    // Apply filter
    if (currentFilter === 'reminders') {
        events = events.filter(event => event.category === 'reminder');
    } else if (currentFilter === 'classes') {
        events = events.filter(event => event.category === 'class');
    } else if (currentFilter === 'assignment') {
        events = events.filter(event => event.category === 'assignment');
    }
    
    return events;
};

// create event element
const createEventElement = (event) => {
    if (event.category === 'reminder') {
        return `
            <div class="event-item reminder-event" style="border-left: 4px solid ${event.color || '#3498db'}">
                <div class="event-time">${event.time || 'All day'}</div>
                <div class="event-title">${event.text}</div>
                <div class="event-type">${event.type}</div>
            </div>
        `;
    } else if (event.category === 'class') {
        return `
            <div class="event-item class-event" style="border-left: 4px solid #27ae60">
                <div class="event-code">${event.course_code}</div>
                <div class="event-title">${event.course_name}</div>
                <div class="event-time">${event.start_time} - ${event.end_time}</div>
                <div class="event-location">📍 ${event.location || 'TBA'}</div>
            </div>
        `;
    } else if (event.category === 'assignment') {
        return `
            <div class="event-item assignment-event" style="border-left: 4px solid ${event.color || '#e74c3c'}">
                <div class="event-code">${event.course_code}</div>
                <div class="event-title">${event.title}</div>
                <div class="event-time">${event.time || 'All day'}</div>
                <div class="event-priority">${event.priority || 'Normal'}</div>
            </div>
        `;
    }
};

// update the weekly calendar display
const updateWeeklyDisplay = (weekDate) => {
    const monday = getMondayOfWeek(weekDate);
    const weekEnd = new Date(monday);
    weekEnd.setDate(weekEnd.getDate() + 6);

    // update header
    const weekTitleElement = document.getElementById('weekTitle');
    if (weekTitleElement) {
        const titleText = `Week of ${formatDate(monday)} - ${formatDate(weekEnd)}`;
        weekTitleElement.textContent = titleText;
    }

    // clear and populate each day
    dayIds.forEach((dayId, index) => {
        const dayDate = new Date(monday);
        dayDate.setDate(dayDate.getDate() + index);
        const dateString = `${dayDate.getFullYear()}-${String(dayDate.getMonth() + 1).padStart(2, '0')}-${String(dayDate.getDate()).padStart(2, '0')}`;

        const dayColumn = document.getElementById(dayId);
        const eventsContainer = dayColumn.querySelector('.events-container');
        const dayHeader = dayColumn.querySelector('.day-name');

        // update day header with date
        dayHeader.innerHTML = `
            ${days[index]}<br>
            <span class="day-date">${formatDate(dayDate)}</span>
        `;

        // get events for this day
        const dayEvents = getEventsForDate(dateString);

        // populate events
        if (dayEvents.length === 0) {
            eventsContainer.innerHTML = '<p class="no-events">No events</p>';
        } else {
            eventsContainer.innerHTML = dayEvents
                .map(event => createEventElement(event))
                .join('');
        }
    });
};

// navigation
const prevWeekBtn = document.getElementById('prevWeek');
const nextWeekBtn = document.getElementById('nextWeek');

if (prevWeekBtn) {
    prevWeekBtn.addEventListener('click', () => {
        currentWeekDate.setDate(currentWeekDate.getDate() - 7);
        fetchWeeklyCalendar(currentWeekDate);
    });
}

if (nextWeekBtn) {
    nextWeekBtn.addEventListener('click', () => {
        currentWeekDate.setDate(currentWeekDate.getDate() + 7);
        fetchWeeklyCalendar(currentWeekDate);
    });
}

// Initialize calendar
if (document.getElementById('monday')) {
    fetchWeeklyCalendar(currentWeekDate);
}

// Filter functionality
const filterButtons = document.querySelectorAll('.filter-btn');
filterButtons.forEach(btn => {
    btn.addEventListener('click', function() {
        filterButtons.forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        if (this.id === 'filterAll') {
            currentFilter = 'all';
        } else if (this.id === 'filterReminders') {
            currentFilter = 'reminders';
        } else if (this.id === 'filterClasses') {
            currentFilter = 'classes';
        } else if (this.id === 'filterCategory') {
            currentFilter = 'assignment';
        }
        
        updateWeeklyDisplay(currentWeekDate);
    });
});

// Modal functionality
const editCategoryBtn = document.getElementById('editCategoryBtn');
const categoryModal = document.getElementById('categoryModal');
const closeCategoryModal = document.getElementById('closeCategoryModal');
const addNewCourseBtn = document.getElementById('addNewCourseBtn');
const cancelFormBtn = document.getElementById('cancelFormBtn');
const courseFormElement = document.getElementById('courseFormElement');

if (editCategoryBtn) {
    editCategoryBtn.addEventListener('click', () => {
        loadCourses();
        showCoursesList();
        categoryModal.style.display = 'block';
    });
}

if (closeCategoryModal) {
    closeCategoryModal.addEventListener('click', () => {
        categoryModal.style.display = 'none';
    });
}

if (addNewCourseBtn) {
    addNewCourseBtn.addEventListener('click', showAddCourseForm);
}

if (cancelFormBtn) {
    cancelFormBtn.addEventListener('click', showCoursesList);
}

if (courseFormElement) {
    courseFormElement.addEventListener('submit', saveCourse);
}

// Add time slot button listener
const addTimeSlotBtn = document.getElementById('addTimeSlotBtn');
if (addTimeSlotBtn) {
    addTimeSlotBtn.addEventListener('click', (e) => {
        e.preventDefault();
        const timeSlotsContainer = document.getElementById('timeSlotsContainer');
        const slotCount = timeSlotsContainer.children.length;
        addTimeSlot(null, slotCount);
    });
}

// close if user clicks outside the modal content
window.addEventListener('click', (e) => {
    if (e.target === categoryModal) {
        categoryModal.style.display = 'none';
    }
});