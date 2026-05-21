document.getElementById('importBtn').addEventListener('click', function() {
    document.getElementById('importModal').style.display = 'block';
    document.getElementById('importValidationError').style.display = 'none';
    document.getElementById('importSuccessMessage').style.display = 'none';
});

document.getElementById('editProfileBtn').addEventListener('click', function() {
    window.location.href = './acc/edit_profile.php';
});

document.getElementById('editCategoriesBtn').addEventListener('click', function() {
    document.getElementById('categoryModal').style.display = 'block';
    loadCategories();
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

document.getElementById('closeCategoryModal').addEventListener('click', function() {
    document.getElementById('categoryModal').style.display = 'none';
});

window.addEventListener('click', function(event) {
    const categoryModal = document.getElementById('categoryModal');
    if (event.target === categoryModal) {
        categoryModal.style.display = 'none';
    }
});


document.getElementById('closeEditCourseModal').addEventListener('click', function() {
    document.getElementById('editCourseModal').style.display = 'none';
});

document.getElementById('cancelEditCourse').addEventListener('click', function() {
    document.getElementById('editCourseModal').style.display = 'none';
});

window.addEventListener('click', function(event) {
    const editModal = document.getElementById('editCourseModal');
    if (event.target === editModal) {
        editModal.style.display = 'none';
    }
});

function editCourse(courseId) {
    fetch('./api/manage_categories.php?action=fetch&id=' + courseId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('editCourseId').value = data.course.course_id;
                document.getElementById('editCourseName').value = data.course.course_name;
                document.getElementById('editCourseCode').value = data.course.course_code || '';
                document.getElementById('editInstructor').value = data.course.instructor || '';
                
                if (data.schedule) {
                    document.getElementById('editDayOfWeek').value = data.schedule.day_of_week || '';
                    document.getElementById('editStartTime').value = data.schedule.start_time || '';
                    document.getElementById('editEndTime').value = data.schedule.end_time || '';
                    document.getElementById('editLocation').value = data.schedule.location || '';
                } else {
                    document.getElementById('editDayOfWeek').value = '';
                    document.getElementById('editStartTime').value = '';
                    document.getElementById('editEndTime').value = '';
                    document.getElementById('editLocation').value = '';
                }
                
                document.getElementById('editCourseModal').style.display = 'block';
            } else {
                alert('Error loading course: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error loading course:', error);
            alert('Error loading course');
        });
}

document.getElementById('editCourseForm').addEventListener('submit', function(event) {
    event.preventDefault();
    
    const courseId = document.getElementById('editCourseId').value;
    const courseName = document.getElementById('editCourseName').value.trim();
    const courseCode = document.getElementById('editCourseCode').value.trim();
    const instructor = document.getElementById('editInstructor').value.trim();
    const dayOfWeek = document.getElementById('editDayOfWeek').value;
    const startTime = document.getElementById('editStartTime').value;
    const endTime = document.getElementById('editEndTime').value;
    const location = document.getElementById('editLocation').value.trim();
    
    if (!courseName) {
        alert('Course name is required');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'update');
    formData.append('course_id', courseId);
    formData.append('course_name', courseName);
    formData.append('course_code', courseCode);
    formData.append('instructor', instructor);
    formData.append('day_of_week', dayOfWeek);
    formData.append('start_time', startTime);
    formData.append('end_time', endTime);
    formData.append('location', location);
    
    fetch('./api/manage_categories.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('editCourseModal').style.display = 'none';
            loadCategories();
        } else {
            alert('Error updating course: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error updating course:', error);
        alert('Error updating course');
    });
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
    document.getElementById('changePasswordModal').style.display = 'block';
    document.getElementById('changePasswordValidationError').style.display = 'none';
    document.getElementById('changePasswordSuccessMessage').style.display = 'none';
    document.getElementById('changePasswordForm').reset();
});


document.getElementById('closeChangePasswordModal').addEventListener('click', function() {
    document.getElementById('changePasswordModal').style.display = 'none';
});

document.getElementById('cancelChangePassword').addEventListener('click', function() {
    document.getElementById('changePasswordModal').style.display = 'none';
});

window.addEventListener('click', function(event) {
    const changePasswordModal = document.getElementById('changePasswordModal');
    if (event.target === changePasswordModal) {
        changePasswordModal.style.display = 'none';
    }
});

document.getElementById('changePasswordForm').addEventListener('submit', function(event) {
    event.preventDefault();
    
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const errorDiv = document.getElementById('changePasswordValidationError');
    const errorMsg = document.getElementById('changePasswordErrorMessage');
    const successDiv = document.getElementById('changePasswordSuccessMessage');
    

    if (!currentPassword || !newPassword || !confirmPassword) {
        errorMsg.textContent = 'All fields are required.';
        errorDiv.style.display = 'block';
        successDiv.style.display = 'none';
        return;
    }
    
    if (newPassword.length < 6) {
        errorMsg.textContent = 'New password must be at least 6 characters long.';
        errorDiv.style.display = 'block';
        successDiv.style.display = 'none';
        return;
    }
    
    if (newPassword !== confirmPassword) {
        errorMsg.textContent = 'New passwords do not match.';
        errorDiv.style.display = 'block';
        successDiv.style.display = 'none';
        return;
    }
    
    const formData = new FormData();
    formData.append('currentPassword', currentPassword);
    formData.append('newPassword', newPassword);
    formData.append('confirmPassword', confirmPassword);
    
    fetch('./api/change_password.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            errorDiv.style.display = 'none';
            successDiv.style.display = 'block';
            document.getElementById('changePasswordForm').reset();
            setTimeout(() => {
                document.getElementById('changePasswordModal').style.display = 'none';
            }, 2000);
        } else {
            errorMsg.textContent = data.error || 'Error changing password.';
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

document.getElementById('contactBtn').addEventListener('click', function() {
    document.getElementById('contactModal').style.display = 'block';
});


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


window.addEventListener('load', function() {
    const urlParams = new URLSearchParams(window.location.search);
    if(urlParams.has('error')) {
        document.getElementById('contactModal').style.display = 'block';
    }
    

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


window.addEventListener('load', function() {
    document.getElementById('emailNotifModal').style.display = 'none';
    document.getElementById('emailUnsubscribeModal').style.display = 'none';
    
    fetch('./api/manage_email_notifs.php?action=status')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                emailNotifsEnabled = data.enabled;
                updateEmailNotifButton();
            }
        })
        .catch(error => {
            console.error('Error loading email notification status:', error);
            emailNotifsEnabled = false;
            updateEmailNotifButton();
        });
});

function updateEmailNotifButton() {
    const btn = document.getElementById('emailNotifsBtn');
    if (emailNotifsEnabled) {
        btn.textContent = 'turn off email notifs';
    } else {
        btn.textContent = 'request email notifs';
    }
}

document.getElementById('emailNotifsBtn').addEventListener('click', function() {
    if (emailNotifsEnabled) {
        document.getElementById('emailUnsubscribeModal').style.display = 'block';
    } else {
        document.getElementById('emailNotifModal').style.display = 'block';
    }
});


document.getElementById('closeEmailNotifModal').addEventListener('click', function() {
    document.getElementById('emailNotifModal').style.display = 'none';
});

document.getElementById('cancelEmailNotif').addEventListener('click', function() {
    document.getElementById('emailNotifModal').style.display = 'none';
});

document.getElementById('confirmEmailNotif').addEventListener('click', function() {
    const formData = new FormData();
    formData.append('action', 'enable');
    
    const btn = document.getElementById('confirmEmailNotif');
    const originalText = btn.textContent;
    btn.textContent = 'Confirming...';
    btn.disabled = true;
    
    fetch('./api/manage_email_notifs.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            emailNotifsEnabled = true;
            updateEmailNotifButton();
            document.getElementById('emailNotifModal').style.display = 'none';
        } else {
            alert('Error enabling email notifications: ' + (data.error || 'Unknown error'));
            btn.textContent = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error enabling email notifications:', error);
        alert('Error enabling email notifications');
        btn.textContent = originalText;
        btn.disabled = false;
    });
});

window.addEventListener('click', function(event) {
    const emailModal = document.getElementById('emailNotifModal');
    if (event.target === emailModal) {
        emailModal.style.display = 'none';
    }
});


document.getElementById('closeEmailUnsubscribeModal').addEventListener('click', function() {
    document.getElementById('emailUnsubscribeModal').style.display = 'none';
});

document.getElementById('cancelUnsubscribe').addEventListener('click', function() {
    document.getElementById('emailUnsubscribeModal').style.display = 'none';
});

document.getElementById('confirmUnsubscribe').addEventListener('click', function() {
    const formData = new FormData();
    formData.append('action', 'disable');
    
    const btn = document.getElementById('confirmUnsubscribe');
    const originalText = btn.textContent;
    btn.textContent = 'Unsubscribing...';
    btn.disabled = true;
    
    fetch('./api/manage_email_notifs.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            emailNotifsEnabled = false;
            updateEmailNotifButton();
            document.getElementById('emailUnsubscribeModal').style.display = 'none';
        } else {
            alert('Error disabling email notifications: ' + (data.error || 'Unknown error'));
            btn.textContent = originalText;
            btn.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error disabling email notifications:', error);
        alert('Error disabling email notifications');
        btn.textContent = originalText;
        btn.disabled = false;
    });
});

window.addEventListener('click', function(event) {
    const unsubscribeModal = document.getElementById('emailUnsubscribeModal');
    if (event.target === unsubscribeModal) {
        unsubscribeModal.style.display = 'none';
    }
});

document.getElementById('logoutBtn').addEventListener('click', function() {
    window.location.href = './api/logout.php';
});


function loadCategories() {
    const categoriesList = document.getElementById('categoriesList');
    categoriesList.innerHTML = '<p style="color: #999; font-style: italic;">Loading courses...</p>';
    
    fetch('./api/manage_categories.php?action=get')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.categories) {
                displayCategories(data.categories);
            } else {
                categoriesList.innerHTML = '<p style="color: #999; font-style: italic;">No courses yet. Add one below!</p>';
            }
        })
        .catch(error => {
            console.error('Error loading courses:', error);
            categoriesList.innerHTML = '<p style="color: red;">Error loading courses</p>';
        });
}

function displayCategories(categories) {
    const categoriesList = document.getElementById('categoriesList');
    categoriesList.innerHTML = '';
    
    if (categories.length === 0) {
        categoriesList.innerHTML = '<p style="color: #999; font-style: italic;">No courses yet. Add one below!</p>';
        return;
    }
    
    categories.forEach(category => {
        const categoryDiv = document.createElement('div');
        categoryDiv.style.cssText = 'display: flex; justify-content: space-between; align-items: center; padding: 10px; background-color: #f0f0f0; border-radius: 6px; margin-bottom: 8px; border-left: 4px solid #d5b4ff; cursor: pointer; transition: background-color 0.2s;';
        categoryDiv.onmouseover = function() { this.style.backgroundColor = '#e8e8e8'; };
        categoryDiv.onmouseout = function() { this.style.backgroundColor = '#f0f0f0'; };
        
        const categoryInfo = document.createElement('div');
        categoryInfo.style.cssText = 'flex: 1; cursor: pointer;';
        
        const categoryName = document.createElement('div');
        categoryName.textContent = category.name;
        categoryName.style.cssText = 'font-weight: 500; color: #223340; font-size: 15px;';
        
        const categoryMeta = document.createElement('div');
        categoryMeta.style.cssText = 'font-size: 12px; color: #666; margin-top: 2px;';
        let metaText = '';
        if (category.course_code) metaText += category.course_code + ' ';
        if (category.instructor) metaText += '• ' + category.instructor;
        categoryMeta.textContent = metaText;
        
        categoryInfo.appendChild(categoryName);
        if (metaText) categoryInfo.appendChild(categoryMeta);
        
        categoryInfo.onclick = function(e) {
            e.stopPropagation();
            editCourse(category.id);
        };
        
        const deleteBtn = document.createElement('button');
        deleteBtn.textContent = 'Delete';
        deleteBtn.style.cssText = 'background-color: #ffd6e7; border: 1px solid #223340; border-radius: 4px; padding: 4px 12px; cursor: pointer; font-size: 12px;';
        deleteBtn.onclick = function(e) {
            e.stopPropagation();
            deleteCategory(category.id);
        };
        
        categoryDiv.appendChild(categoryInfo);
        categoryDiv.appendChild(deleteBtn);
        categoriesList.appendChild(categoryDiv);
    });
}

function deleteCategory(categoryId) {
    if (confirm('Are you sure you want to delete this course?')) {
        fetch('./api/manage_categories.php?action=delete&id=' + categoryId, {
            method: 'POST'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadCategories();
            } else {
                alert('Error deleting course: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error deleting course:', error);
            alert('Error deleting course');
        });
    }
}

document.getElementById('addCategoryBtn').addEventListener('click', function() {
    const categoryInput = document.getElementById('newCategoryInput');
    const categoryName = categoryInput.value.trim();
    
    if (!categoryName) {
        alert('Please enter a course name');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'add');
    formData.append('name', categoryName);
    formData.append('code', '');
    formData.append('instructor', '');
    
    fetch('./api/manage_categories.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            categoryInput.value = '';
            loadCategories();
        } else {
            alert('Error adding course: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error adding course:', error);
        alert('Error adding course');
    });
});