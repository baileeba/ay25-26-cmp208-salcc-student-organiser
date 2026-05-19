const monthYearElement = document.getElementById('monthYear');
const datesElement = document.getElementById('dates');
const rememberElement = document.getElementById('remember');
const prevBtn = document.getElementById('prevBtn');
const nextBtn = document.getElementById('nextBtn');

let currentDate = new Date();
let selectedDate = null;

let remindersData = [];


const fetchReminders = async () => {
    try {
        const response = await fetch('api/reminders.php');
        if (!response.ok) {
            throw new Error('server error');
        }
        remindersData = await response.json();
        console.log('Reminders fetched:', remindersData);
        return remindersData;
    } catch (error) {
        console.error('failed to fetch reminders:', error);
        return [];
    }
};


const getRemindersForDate = (year, month, day) => {
    const dateString = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
    return remindersData.filter(reminder => reminder.date === dateString);
};


const displayReminders = (year, month, day) => {

    const defaultMsg = document.getElementById('defaultReminder');
    if (defaultMsg) {
        defaultMsg.style.display = 'none';
    }

    const reminders = getRemindersForDate(year, month, day);
    
    let html = '';
    
    if (reminders.length === 0) {
        html = '<p class="no-reminders">no reminders for this date</p>';
    } else {
        html = `<h3>${new Date(year, month, day).toLocaleDateString('default', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })}</h3>`;
        html += '<div class="reminders-list">';
        
        reminders.forEach(reminder => {
            html += `
                <div class="reminder-item">
                    <div class="reminder-color-line" style="background-color: ${reminder.color};"></div>
                    <div class="reminder-content">
                        <p class="reminder-title">${reminder.title}</p>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
    }
    
    rememberElement.innerHTML = html;
};


const hasReminders = (year, month, day) => {
    return getRemindersForDate(year, month, day).length > 0;
};

const updateCalendar = () => {
    const currentYear = currentDate.getFullYear();
    const currentMonth = currentDate.getMonth();

    const firstDay = new Date(currentYear, currentMonth, 1);
    const lastDay = new Date(currentYear, currentMonth + 1, 0);

    const totalDays = lastDay.getDate();
    const firstDayIndex = firstDay.getDay();
    const lastDayIndex = lastDay.getDay();

    const monthYearString = currentDate.toLocaleString('default', {
        month: 'long',
        year: 'numeric'
    });
    monthYearElement.textContent = monthYearString;

    let datesHTML = '';


    for (let i = firstDayIndex; i > 0; i--) {
        const prevDate = new Date(currentYear, currentMonth, 1 - i);
        datesHTML += `<div class="date inactive">${prevDate.getDate()}</div>`;
    }


    for (let i = 1; i <= totalDays; i++) {
        const date = new Date(currentYear, currentMonth, i);
        const isToday = date.toDateString() === new Date().toDateString() ? 'active' : '';
        const hasReminder = hasReminders(currentYear, currentMonth, i);
        const reminderClass = hasReminder ? 'has-reminder' : '';

        datesHTML += `<div class="date ${isToday} ${reminderClass}" data-day="${i}">${i}</div>`;
    }


    for (let i = 1; i <= 6 - lastDayIndex; i++) {
        const nextDate = new Date(currentYear, currentMonth + 1, i);
        datesHTML += `<div class="date inactive">${nextDate.getDate()}</div>`;
    }

    datesElement.innerHTML = datesHTML;


    document.querySelectorAll('.date:not(.inactive)').forEach(dateElement => {
        dateElement.addEventListener('click', () => {
            const day = parseInt(dateElement.getAttribute('data-day'));
            selectedDate = new Date(currentYear, currentMonth, day);
            displayReminders(currentYear, currentMonth, day);

            
            document.querySelectorAll('.date').forEach(el => el.classList.remove('selected'));
            dateElement.classList.add('selected');
        });
    });
};


if (prevBtn) {
    prevBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() - 1);
        updateCalendar();
    });
}

if (nextBtn) {
    nextBtn.addEventListener('click', () => {
        currentDate.setMonth(currentDate.getMonth() + 1);
        updateCalendar();
    });
}


const initializeMiniCalendar = async () => {
    if (monthYearElement && datesElement) {
        await fetchReminders();
        updateCalendar();
    }
};


window.fetchReminders = fetchReminders;
window.remindersData = remindersData;

if (monthYearElement && datesElement) {
    initializeMiniCalendar();
}
