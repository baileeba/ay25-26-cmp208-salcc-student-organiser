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