let currentWeekDate = new Date();
let weekEvents = [];

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
        const response = await fetch('/api/weekly_calendar.php');
        if (!response.ok) {
            throw new Error('Failed to fetch calendar');
        }
        weekEvents = await response.json();
        updateWeeklyDisplay(weekDate);
    } catch (error) {
        console.error('Error fetching calendar:', error);
        // Set fallback title even if fetch fails
        updateWeeklyDisplay(weekDate);
    }
};

// get events for a specific date
const getEventsForDate = (dateString) => {
    return weekEvents.events ? weekEvents.events.filter(event => event.date === dateString) : [];
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
        const dateString = dayDate.toISOString().split('T')[0];

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

if (document.getElementById('monday')) {
    fetchWeeklyCalendar(currentWeekDate);
}