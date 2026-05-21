const upcomingElement = document.getElementById('upcoming');

// display upcoming reminders, assignments, and classes
const displayUpcoming = () => {
    // Use globally available data from mini-calendar.js
    const remindersData = window.remindersData || [];
    const assignmentsData = window.assignmentsData || [];
    const classesData = window.classesData || [];

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const todayTime = today.getTime();
    const dayOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][today.getDay()];

    // keep only future/today reminders
    const upcomingReminders = remindersData.filter(reminder => {
        const reminderDate = new Date(reminder.date);
        reminderDate.setHours(0, 0, 0, 0);
        return reminderDate.getTime() >= todayTime;
    });

    // keep only future/today assignments
    const upcomingAssignments = assignmentsData.filter(assignment => {
        const assignmentDate = new Date(assignment.due_date);
        assignmentDate.setHours(0, 0, 0, 0);
        return assignmentDate.getTime() >= todayTime;
    });

    // get classes for today
    const todayClasses = classesData.filter(classItem => classItem.day === dayOfWeek);

    // combine and sort by date/time
    const allItems = [
        ...upcomingReminders.map(r => ({ ...r, type: 'reminder', sortDate: new Date(r.date), sortTime: r.time || '00:00' })),
        ...upcomingAssignments.map(a => ({ ...a, type: 'assignment', sortDate: new Date(a.due_date), sortTime: a.due_date })),
        ...todayClasses.map(c => ({ ...c, type: 'class', sortDate: today, sortTime: c.start_time }))
    ];
    
    allItems.sort((a, b) => {
        const dateA = a.sortDate;
        const dateB = b.sortDate;
        if (dateA.getTime() !== dateB.getTime()) {
            return dateA - dateB;
        }
        return a.sortTime.localeCompare(b.sortTime);
    });

    let html = '';

    if (allItems.length === 0) {

        html = `<p class="no-reminders">no upcoming reminders, assignments, or classes</p>`;

    } else {

        allItems.forEach(item => {

            const formattedDate = new Date(item.date || item.due_date)
                .toLocaleDateString('default', {
                    weekday: 'short',
                    month: 'short',
                    day: 'numeric'
                });

            if (item.type === 'reminder') {
                html += `
                    <div class="upcoming-item">

                        <div class="upcoming-color"
                             style="background-color: ${item.color};">
                        </div>

                        <div class="upcoming-content">
                            <p class="upcoming-title">${item.title}</p>
                            <p class="upcoming-date">${formattedDate}</p>
                        </div>

                    </div>
                `;
            } else if (item.type === 'assignment') {
                html += `
                    <div class="upcoming-item">

                        <div class="upcoming-color"
                             style="background-color: ${item.color || '#FF6B6B'};">
                        </div>

                        <div class="upcoming-content">
                            <p class="upcoming-title">${item.title} <span class="badge">${item.course_code}</span></p>
                            <p class="upcoming-date">${formattedDate}</p>
                        </div>

                    </div>
                `;
            } else if (item.type === 'class') {
                html += `
                    <div class="upcoming-item">

                        <div class="upcoming-color"
                             style="background-color: ${item.color || '#4ECDC4'};">
                        </div>

                        <div class="upcoming-content">
                            <p class="upcoming-title">${item.course_name} <span class="badge">${item.course_code}</span></p>
                            <p class="upcoming-date">${item.start_time} - ${item.end_time} at ${item.location}</p>
                        </div>

                    </div>
                `;
            }
        });
    }

    upcomingElement.innerHTML = html;
};


const initializeUpcoming = async () => {
    
    if (window.fetchReminders) {
        await window.fetchReminders();
    }
    
    if (window.fetchAssignments) {
        await window.fetchAssignments();
    }

    if (window.fetchClasses) {
        await window.fetchClasses();
    }
    
    displayUpcoming();
};


if (upcomingElement) {
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(initializeUpcoming, 100);
        });
    } else {
        setTimeout(initializeUpcoming, 100);
    }
}