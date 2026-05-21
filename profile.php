<?php

    session_start();
    include "acc/connect.php";

    if(!isset($_SESSION["user_id"])) {
        header("Location: acc/login.php");
        exit();
    }

    $timeout_duration = 1800;

    if(isset($_SESSION['last_activity'])) {
        $idle_time = time() - $_SESSION['last_activity'];
        if($idle_time > $timeout_duration) {
            session_unset();
            session_destroy();
            header("Location: index.php");
            exit;
        }
    }

    $_SESSION['last_activity'] = time();
        
?>

<!DOCTYPE html>
<html lang = "en">
    <head>
        <meta charset = "UTF-8">
        <meta name = "viewport" content = "width=device-width, initial-scale=1.0">
        <title>Profile</title>
        <link rel = "stylesheet" href = "style.css">
        <link rel = 'icon' href = 'assets/GREEN_FOLDER.png'>
    </head>

    <body>
        <div align = "center">
            <div class= "writeHeader">
        </div>

        <div class= "profile-dash">
            <div class = 'images'>
                <img src = 'assets/PFPDEFAULT.png' class = 'profile'>
                <img src= "assets/HELLOBUBBLE.png" class = 'bubble'>
            </div>

            <div class = 'box1'>
                <p id = 'editProfileBtn'>edit profile</p>
                <p id = 'editCategoriesBtn'>edit courses</p>
                <p id = "importBtn">import SONIS schedule</p>
                <p id = "emailNotifsBtn">request email notifs</p>
                <div class = "contact-us">
                    <p id = 'contactBtn'>contact us</p>
                </div>
            </div>

            <div class = 'box2'>
                <p id = 'friendsBtn'>friends</p>
                <p id = "changePasswordBtn">change password</p>
                <p id = "logoutBtn">log out</p>
            </div>
        </div>

        <div id="contactModal">
            <div id="contactModalContent">
                <div id="contactModalHeader">
                    <h2>contact us</h2>
                    <span id="closeContactModal">&times;</span>
                </div>
                <div id="contactValidationError" style="display: none;">
                    <p id="contactErrorMessage">All fields are required.</p>
                </div>
                <?php
                    if(isset($_GET['error'])) {
                        if($_GET['error'] === 'emptyfields') {
                            echo '<p id="contactErrorMessage">All fields are required.</p>';
                        } elseif($_GET['error'] === 'emailFailed') {
                            $details = isset($_GET['details']) ? htmlspecialchars($_GET['details']) : 'Unknown error';
                            echo '<p id="contactErrorMessage">Failed to send email. Error: ' . $details . '</p>';
                        }
                    }

                    if(isset($_GET['success'])) {
                        echo '<p id="contactSuccessMessage">Your message has been sent!</p>';
                    }
                    ?>
                <form id="contactForm" action = "./api/contact.php" method="POST">
                    <label for="contactSubject">Subject:</label>
                    <input type="text" id="contactSubject" name="subject" value="<?php echo isset($_POST['subject']) ? htmlspecialchars($_POST['subject']) : ''; ?>">

                    <label for="contactEmail">Your Email:</label>
                    <input type="email" id="contactEmail" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    
                    <label for="contactMessage">Message:</label>
                    <textarea id="contactMessage" name="message" rows="5"><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                    
                    <button type="submit" name = "submit">Send</button>
                    <button type="button" id="cancelContact">Cancel</button>
                </form>
            </div>
        </div>

        <div id="emailNotifModal">
            <div id="emailNotifModalContent">
                <div id="emailNotifModalHeader">
                    <h2>Email Notifications</h2>
                    <span id="closeEmailNotifModal">&times;</span>
                </div>
                <div id="emailNotifModalBody">
                    <p>By confirming, you agree to receive email notifications for:</p>
                    <ul id="emailNotifList">
                        <li>Class reminders when it's time for your classes</li>
                        <li>Upcoming assignments and deadlines</li>
                        <li>Task reminders and notifications</li>
                        <li>Goal deadline alerts</li>
                    </ul>
                    <p id="emailNotifFooter">You can manage these notifications anytime from your profile settings.</p>
                    <div class="email-notif-button-group">
                        <button type="button" id="cancelEmailNotif" class="btn btn-danger">Cancel</button>
                        <button type="button" id="confirmEmailNotif" class="btn btn-primary">Confirm</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="emailUnsubscribeModal">
            <div id="emailUnsubscribeModalContent">
                <div id="emailUnsubscribeModalHeader">
                    <h2>Unsubscribe from Email Notifications</h2>
                    <span id="closeEmailUnsubscribeModal">&times;</span>
                </div>
                <div id="emailUnsubscribeModalBody">
                    <p>Are you sure you want to unsubscribe from email notifications?</p>
                    <p id="emailUnsubscribeSubtitle">You will no longer receive emails for:</p>
                    <ul id="emailUnsubscribeList">
                        <li>Class reminders</li>
                        <li>Upcoming assignments and deadlines</li>
                        <li>Task reminders and notifications</li>
                        <li>Goal deadline alerts</li>
                    </ul>
                    <p id="emailUnsubscribeFooter">You can re-enable notifications anytime.</p>
                    <div class="email-unsubscribe-button-group">
                        <button type="button" id="cancelUnsubscribe" class="btn btn-primary">Keep Notifications</button>
                        <button type="button" id="confirmUnsubscribe" class="btn btn-danger">Unsubscribe</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="importModal">
            <div id="importModalContent">
                <div id="importModalHeader">
                    <h2>Import SONIS Schedule</h2>
                    <span id="closeImportModal">&times;</span>
                </div>
                <div id="importValidationError" style="display: none;">
                    <p id="importErrorMessage"></p>
                </div>
                <div id="importSuccessMessage" style="display: none;">
                    <p>Your schedule has been imported successfully!</p>
                </div>
                <form id="importForm" enctype="multipart/form-data">
                    <label for="pdfFile">Select PDF File:</label>
                    <input type="file" id="pdfFile" name="pdfFile" accept=".pdf" required>
                    <p style="font-size: 12px; color: #666; margin-top: 5px;">Please upload your SONIS schedule PDF file</p>
                    
                    <button type="submit" name="submit">Upload and Import</button>
                    <button type="button" id="cancelImport">Cancel</button>
                </form>
            </div>
        </div>

        <div id="categoryModal" class="category-modal">
            <div class="category-modal-content">
                <h3>Edit Courses</h3>
                <button class="close-modal" id="closeCategoryModal">&times;</button>
                <div id="categoriesList"></div>
                <input type="text" id="newCategoryInput" placeholder="Add new course..." maxlength="100">
                <button id="addCategoryBtn" class="btn btn-primary" style="width: 100%; margin-top: 10px;">Add Course</button>
            </div>
        </div>

        <div id="editCourseModal" class="category-modal">
            <div class="edit-course-modal-content">
                <h3>Edit Course</h3>
                <button class="close-modal" id="closeEditCourseModal">&times;</button>
                <form id="editCourseForm" class="edit-course-form">
                    <input type="hidden" id="editCourseId">
                    
                    <label for="editCourseName" class="edit-course-form-label">Course Name:</label>
                    <input type="text" id="editCourseName" maxlength="100" class="edit-course-form-input">
                    
                    <label for="editCourseCode" class="edit-course-form-label">Course Code:</label>
                    <input type="text" id="editCourseCode" maxlength="20" class="edit-course-form-input">
                    
                    <label for="editInstructor" class="edit-course-form-label">Instructor:</label>
                    <input type="text" id="editInstructor" maxlength="100" class="edit-course-form-input">
                    
                    <hr class="edit-course-schedule-divider">
                    <h4 class="edit-course-schedule-title">Class Schedule (Optional)</h4>
                    
                    <label for="editDayOfWeek" class="edit-course-form-label">Day of Week:</label>
                    <select id="editDayOfWeek" class="edit-course-form-select">
                        <option value="">Select a day</option>
                        <option value="Monday">Monday</option>
                        <option value="Tuesday">Tuesday</option>
                        <option value="Wednesday">Wednesday</option>
                        <option value="Thursday">Thursday</option>
                        <option value="Friday">Friday</option>
                        <option value="Saturday">Saturday</option>
                        <option value="Sunday">Sunday</option>
                    </select>
                    
                    <label for="editStartTime" class="edit-course-form-label">Start Time:</label>
                    <input type="time" id="editStartTime" class="edit-course-form-input">
                    
                    <label for="editEndTime" class="edit-course-form-label">End Time:</label>
                    <input type="time" id="editEndTime" class="edit-course-form-input">
                    
                    <label for="editLocation" class="edit-course-form-label">Location:</label>
                    <input type="text" id="editLocation" maxlength="100" class="edit-course-form-input">
                    
                    <div class="edit-course-button-group">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <button type="button" id="cancelEditCourse" class="btn btn-danger">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="changePasswordModal" class="category-modal">
            <div class="category-modal-content">
                <h3>Change Password</h3>
                <button class="close-modal" id="closeChangePasswordModal">&times;</button>
                <div id="changePasswordValidationError" class="modal-message modal-error">
                    <p id="changePasswordErrorMessage"></p>
                </div>
                <div id="changePasswordSuccessMessage" class="modal-message modal-success">
                    <p>Password changed successfully!</p>
                </div>
                <form id="changePasswordForm">
                    <label for="currentPassword">Current Password:</label>
                    <input type="password" id="currentPassword" name="currentPassword" required>
                    
                    <label for="newPassword">New Password:</label>
                    <input type="password" id="newPassword" name="newPassword" required>
                    
                    <label for="confirmPassword">Confirm New Password:</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required>
                    
                    <div class="change-password-button-group">
                        <button type="submit" class="btn btn-primary">Change Password</button>
                        <button type="button" id="cancelChangePassword" class="btn btn-danger">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <script src = "js/navbar.js" defer></script>
        <script src = "js/profile.js" defer></script>
    </body>
</html>