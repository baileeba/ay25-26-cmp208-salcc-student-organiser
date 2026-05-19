document.getElementById('importBtn').addEventListener('click', function() {
    document.getElementById('importModal').style.display = 'block';
    document.getElementById('importValidationError').style.display = 'none';
    document.getElementById('importSuccessMessage').style.display = 'none';
});

document.getElementById('closeImportModal').addEventListener('click', function() {
    document.getElementById('importModal').style.display = 'none';
});

document.getElementById('cancelImport').addEventListener('click', function() {
    document.getElementById('importModal').style.display = 'none';
});

window.addEventListener('click', function(event) {
    const modal = document.getElementById('importModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
});

document.getElementById('importForm').addEventListener('submit', function(event) {
    event.preventDefault();
    
    const fileInput = document.getElementById('pdfFile');
    const file = fileInput.files[0];
    const errorDiv = document.getElementById('importValidationError');
    const successDiv = document.getElementById('importSuccessMessage');
    const errorMsg = document.getElementById('importErrorMessage');
    
    if (!file) {
        errorMsg.textContent = 'Please select a PDF file.';
        errorDiv.style.display = 'block';
        successDiv.style.display = 'none';
        return;
    }
    
    if (file.type !== 'application/pdf') {
        errorMsg.textContent = 'Please select a valid PDF file.';
        errorDiv.style.display = 'block';
        successDiv.style.display = 'none';
        return;
    }
    
    
    const formData = new FormData();
    formData.append('pdfFile', file);
    
    fetch('./api/import.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            errorDiv.style.display = 'none';
            successDiv.style.display = 'block';
            document.getElementById('importForm').reset();
            setTimeout(() => {
                document.getElementById('importModal').style.display = 'none';
            }, 2000);
        } else {
            errorMsg.textContent = data.error || 'Failed to import schedule.';
            errorDiv.style.display = 'block';
            successDiv.style.display = 'none';
        }
    })
    .catch(error => {
        errorMsg.textContent = 'Error: ' + error.message;
        errorDiv.style.display = 'block';
        successDiv.style.display = 'none';
    });
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

// Form submission handler
document.getElementById('contactForm').addEventListener('submit', function(event) {
    const subject = document.getElementById('contactSubject').value.trim();
    const email = document.getElementById('contactEmail').value.trim();
    const message = document.getElementById('contactMessage').value.trim();
    const errorDiv = document.getElementById('contactValidationError');

    if (!subject || !email || !message) {
        event.preventDefault();
        errorDiv.style.display = 'block';
        return false;
    } else {
        errorDiv.style.display = 'none';
    }
});

// Auto-open contact modal if there's an error parameter
window.addEventListener('load', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.has('error')) {
        document.getElementById('contactModal').style.display = 'block';
    }
    
    // Clear form on page load
    document.getElementById('contactForm').reset();
    document.getElementById('contactValidationError').style.display = 'none';
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