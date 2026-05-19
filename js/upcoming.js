const upcomingElement = document.getElementById('upcoming');

// display upcoming reminders
const displayUpcoming = () => {
    // Use globally available remindersData from mini-calendar.js
    const remindersData = window.remindersData || [];

    const today = new Date();

    // keep only future/today reminders
    const upcoming = remindersData.filter(reminder => {
        const reminderDate = new Date(reminder.date);
        return reminderDate >= new Date(today.setHours(0,0,0,0));
    });

    // sort by date
    upcoming.sort((a, b) => new Date(a.date) - new Date(b.date));

    let html = '';

    if (upcoming.length === 0) {

        html = `<p class="no-reminders">no upcoming reminders</p>`;

    } else {

        upcoming.forEach(reminder => {

            const formattedDate = new Date(reminder.date)
                .toLocaleDateString('default', {
                    weekday: 'short',
                    month: 'short',
                    day: 'numeric'
                });

            html += `
                <div class="upcoming-item">

                    <div class="upcoming-color"
                         style="background-color: ${reminder.color};">
                    </div>

                    <div class="upcoming-content">
                        <p class="upcoming-title">${reminder.title}</p>
                        <p class="upcoming-date">${formattedDate}</p>
                    </div>

                </div>
            `;
        });
    }

    upcomingElement.innerHTML = html;
};

// Wait for mini-calendar to fetch reminders, then display upcoming
const initializeUpcoming = async () => {
    // Wait for mini-calendar to fetch reminders (it exposes fetchReminders globally)
    if (window.fetchReminders) {
        await window.fetchReminders();
    }
    displayUpcoming();
};

// initialize
if (upcomingElement) {
    // If mini-calendar hasn't loaded yet, wait a bit and try
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(initializeUpcoming, 100);
        });
    } else {
        setTimeout(initializeUpcoming, 100);
    }
}