DROP DATABASE IF EXISTS studsort_db;
CREATE DATABASE studsort_db;
USE studsort_db;

CREATE TABLE users (
  `user_id` int AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL
);

CREATE TABLE reminders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `reminder_date` DATE NOT NULL,
    `reminder_text` VARCHAR(255) NOT NULL,
    `reminder_color` VARCHAR(7) NOT NULL DEFAULT '#3498db',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX (user_id, reminder_date)
);