document.getElementById('editProfileBtn').addEventListener('click', function() {
    window.location.href = './acc/edit_profile.php';
});

document.getElementById('friendsBtn').addEventListener('click', function() {
    window.location.href = './friends.php';
});

document.getElementById('contactBtn').addEventListener('click', function() {
    document.getElementById('contactModal').style.display = 'block';
});

document.getElementById('closeContactModal').addEventListener('click', function() {
    document.getElementById('contactModal').style.display = 'none';
});

document.getElementById('cancelContact').addEventListener('click', function() {
    document.getElementById('contactModal').style.display = 'none';
});

window.addEventListener('click', function(event) {
    const modal = document.getElementById('contactModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
});

document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const subject = document.getElementById('contactSubject').value;
    const message = document.getElementById('contactMessage').value;
    
    const xhr = new XMLHttpRequest();
    xhr.open('POST', './api/contact.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    
    xhr.onload = function() {
        if (xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);
            if (response.success) {
                alert(response.message);
                document.getElementById('contactForm').reset();
                document.getElementById('contactModal').style.display = 'none';
            } else {
                alert('Error: ' + response.message);
            }
        }
    };
    
    xhr.send('subject=' + encodeURIComponent(subject) + '&message=' + encodeURIComponent(message));
});
    
document.getElementById('logoutBtn').addEventListener('click', function() {
    window.location.href = './api/logout.php';
});