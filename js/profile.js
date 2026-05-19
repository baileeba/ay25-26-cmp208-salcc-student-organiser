document.getElementById('editProfileBtn').addEventListener('click', function() {
    window.location.href = './acc/edit_profile.php';
});

document.getElementById('editCategoriesBtn').addEventListener('click', function() {
    // Placeholder for edit categories functionality
    alert('Edit categories functionality coming soon!');
});

document.getElementById('importBtn').addEventListener('click', function() {
    // Placeholder for import SONIS schedule functionality
    alert('Import SONIS schedule functionality coming soon!');
});

document.getElementById('friendsBtn').addEventListener('click', function() {
    window.location.href = './friends.php';
});

document.getElementById('changePasswordBtn').addEventListener('click', function() {
    window.location.href = './acc/reset.php';
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
    

let emailNotifsEnabled = false;

document.getElementById('emailNotifsBtn').addEventListener('click', function() {
    const btn = document.getElementById('emailNotifsBtn');
    const isEnabled = btn.textContent.includes('request email notifs');
    
    if (isEnabled) {
        // Show modal confirmation (placeholder - no actual email sending)
        btn.textContent = 'request no email notifs';
        emailNotifsEnabled = true;
        document.getElementById('emailNotifModal').style.display = 'block';
    } else {
        // Disable notifications
        btn.textContent = 'request email notifs';
        emailNotifsEnabled = false;
        alert('Email notifications disabled');
    }
});


document.getElementById('closeEmailNotifModal').addEventListener('click', function() {
    document.getElementById('emailNotifModal').style.display = 'none';
});

document.getElementById('closeEmailNotifBtn').addEventListener('click', function() {
    document.getElementById('emailNotifModal').style.display = 'none';
});

window.addEventListener('click', function(event) {
    const emailModal = document.getElementById('emailNotifModal');
    if (event.target === emailModal) {
        emailModal.style.display = 'none';
    }
});

document.getElementById('logoutBtn').addEventListener('click', function() {
    window.location.href = './api/logout.php';
});