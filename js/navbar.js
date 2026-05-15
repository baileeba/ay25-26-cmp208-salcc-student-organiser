document.querySelector(".writeHeader").innerHTML = `
    <header align="center">
        <div id="nav">
            <a href="reminders.php" class="${window.location.pathname.includes('reminders') ? 'active' : ''}">reminders</a>
            <a href="calendar.php" class="${window.location.pathname.includes('calendar') ? 'active' : ''}">calendar</a>
            <a href="index.php" class="${window.location.pathname.includes('index') ? 'active' : ''}">home</a>
            <a href="goals.php" class="${window.location.pathname.includes('goals') ? 'active' : ''}">goals</a>
            <a href="profile.php" class="${window.location.pathname.includes('profile') ? 'active' : ''}">me</a>
        </div>
    </header>
`;