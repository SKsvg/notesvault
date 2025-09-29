/*
step 1 : go to the phpmyadmin
step 2 : create a database as "notesvault"
step 3 : go to the created database and go to the sql tab
step 4 : past the following codes and click go...

after creates changes database path following files...
db.php 
upload_notes.php
notes.php

$host = 'localhost'; // Replace with your MySQL host and port if needed
$dbname = 'notesvault';
$username = 'root'; // Replace with your MySQL username
$password = ''; // Replace with your MySQL password



*/
CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `branch` varchar(255) NOT NULL,
  `semester` int(11) NOT NULL,
  `subject_code` varchar(50) NOT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `uploader` varchar(100) DEFAULT 'Anonymous',
  `uploader_id` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_pic_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE groups (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  description TEXT,
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE group_members (
  id INT AUTO_INCREMENT PRIMARY KEY,
  group_id INT,
  user_id INT,
  role ENUM('member','admin') DEFAULT 'member',
  FOREIGN KEY (group_id) REFERENCES groups(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE chats (
  id INT AUTO_INCREMENT PRIMARY KEY,
  group_id INT,
  user_id INT,
  message TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (group_id) REFERENCES groups(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE group_notes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  group_id INT,
  user_id INT,
  title VARCHAR(200),
  content TEXT,
  file_path VARCHAR(255), -- for PDFs
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (group_id) REFERENCES groups(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE meetings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  group_id INT,
  user_id INT,
  title VARCHAR(200),
  meeting_time DATETIME,
  meeting_link VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (group_id) REFERENCES groups(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE meeting_participants (
  id INT AUTO_INCREMENT PRIMARY KEY,
  meeting_id INT,
  user_id INT,
  status ENUM('joined','left') DEFAULT 'joined',
  joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (meeting_id) REFERENCES meetings(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE quizzes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  group_id INT,
  user_id INT,
  question TEXT,
  answer VARCHAR(200),
  review TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (group_id) REFERENCES groups(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE quiz_responses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  quiz_id INT,
  user_id INT,
  response VARCHAR(200),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (quiz_id) REFERENCES quizzes(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);
