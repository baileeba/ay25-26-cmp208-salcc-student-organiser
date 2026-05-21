DROP DATABASE IF EXISTS studsort_db;
CREATE DATABASE studsort_db;
USE studsort_db;


CREATE TABLE users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  username VARCHAR(50) NOT NULL UNIQUE,
  email VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  reset_token VARCHAR(64),
  reset_token_expiry DATETIME,
  email_notifications_enabled BOOLEAN DEFAULT 0
);


CREATE TABLE courses (
  course_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  course_code VARCHAR(20) NOT NULL,
  course_name VARCHAR(100) NOT NULL,
  instructor VARCHAR(100),
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);


CREATE TABLE class_schedule (
  schedule_id INT AUTO_INCREMENT PRIMARY KEY,
  course_id INT NOT NULL,
  day_of_week ENUM('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  location VARCHAR(100),
  FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
);


CREATE TABLE groups (
  group_id INT AUTO_INCREMENT PRIMARY KEY,
  group_name VARCHAR(100) NOT NULL,
  description TEXT,
  course_id INT,
  user_id INT NOT NULL,
  max_members INT DEFAULT 5,
  FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE SET NULL,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);


CREATE TABLE group_members (
  member_id INT AUTO_INCREMENT PRIMARY KEY,
  group_id INT NOT NULL,
  user_id INT NOT NULL,
  role ENUM('leader','member') DEFAULT 'member',
  FOREIGN KEY (group_id) REFERENCES groups(group_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  UNIQUE (group_id, user_id)
);


CREATE TABLE assignments (
  assignment_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  course_id INT NOT NULL,
  title VARCHAR(150) NOT NULL,
  description TEXT,
  due_date DATE NOT NULL,
  due_time TIME,
  priority ENUM('small','medium','large') DEFAULT 'medium',
  status ENUM('not_started','in_progress','completed') DEFAULT 'not_started',
  weight_percentage DECIMAL(5,2),
  is_group_assignment BOOLEAN DEFAULT FALSE,
  group_id INT,
  color VARCHAR(7) DEFAULT '#3498db',
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE,
  FOREIGN KEY (group_id) REFERENCES groups(group_id) ON DELETE SET NULL
);


CREATE TABLE events (
  event_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  course_id INT NOT NULL,
  event_type ENUM('exam','test','quiz','presentation','project_deadline','other') NOT NULL,
  title VARCHAR(150) NOT NULL,
  description TEXT,
  event_date DATE NOT NULL,
  start_time TIME,
  end_time TIME,
  location VARCHAR(100),
  weight_percentage DECIMAL(5,2),
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (course_id) REFERENCES courses(course_id) ON DELETE CASCADE
);


CREATE TABLE goals (
  goal_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  title VARCHAR(150) NOT NULL,
  description TEXT,
  target_date DATE,
  progress_percentage INT DEFAULT 0,
  status ENUM('active','completed','abandoned') DEFAULT 'active',
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);


CREATE TABLE friend_requests (
  request_id INT AUTO_INCREMENT PRIMARY KEY,
  sender_id INT NOT NULL,
  receiver_id INT NOT NULL,
  status ENUM('pending','accepted','declined') DEFAULT 'pending',
  FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (receiver_id) REFERENCES users(user_id) ON DELETE CASCADE,
  UNIQUE (sender_id, receiver_id)
);


CREATE TABLE friendships (
  friendship_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id_1 INT NOT NULL,
  user_id_2 INT NOT NULL,
  FOREIGN KEY (user_id_1) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id_2) REFERENCES users(user_id) ON DELETE CASCADE,
  UNIQUE (user_id_1, user_id_2)
);


CREATE TABLE reminders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  reminder_date DATE NOT NULL,
  reminder_time TIME,
  reminder_text VARCHAR(255) NOT NULL,
  reminder_type ENUM('assignment','event','goal','custom') DEFAULT 'custom',
  related_id INT,
  reminder_color VARCHAR(7) DEFAULT '#3498db',
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);


CREATE TABLE notifications (
  notification_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  email VARCHAR(100) NOT NULL,
  subject VARCHAR(150) NOT NULL,
  message TEXT NOT NULL,
  type ENUM('reminder','assignment','event','goal','friend_request','group') DEFAULT 'reminder',
  related_id INT,
  send_date DATE NOT NULL,
  send_time TIME,
  is_sent BOOLEAN DEFAULT FALSE,

  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  INDEX (user_id, send_date, is_sent)
);


DELIMITER $$

CREATE TRIGGER delete_friend_request_on_accepted
AFTER UPDATE ON friend_requests
FOR EACH ROW
BEGIN
    IF NEW.status IN ('accepted', 'declined') THEN
        DELETE FROM friend_requests WHERE request_id = NEW.request_id;
    END IF;
END$$

DELIMITER ;