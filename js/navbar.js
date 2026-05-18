document.querySelector(".writeHeader").innerHTML = `
    <header align="center">
        <div id="nav">
            <a href="tasks.php" class="${window.location.pathname.includes('tasks') ? 'active' : ''}">tasks</a>
            <a href="calendar.php" class="${window.location.pathname.includes('calendar') ? 'active' : ''}">calendar</a>
            <a href="index.php" class="${window.location.pathname.includes('index') ? 'active' : ''}">home</a>
            <a href="goals.php" class="${window.location.pathname.includes('goals') ? 'active' : ''}">goals</a>
            <a href="profile.php" class="${window.location.pathname.includes('profile') ? 'active' : ''}">me</a>
        </div>
    </header>`
;


if(window.location.pathname.includes('/acc/')) {
    const links = document.querySelectorAll('#nav a');
    links.forEach(link => {
        const href = link.getAttribute('href');
        if(href && !href.startsWith('http') && !href.startsWith('/')) {
            link.setAttribute('href', '../' + href);
        }
    });
}