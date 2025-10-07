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
  `uploader_id` varchar(50) DEFAULT NULL,
  `is_trashed` TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `profile_pic_path` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `groups` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100),
  `description` TEXT,
  `created_by` INT(11) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE group_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('member','admin') DEFAULT 'member',
    joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending','active') DEFAULT 'pending',
    UNIQUE KEY unique_membership (group_id, user_id),
    FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

CREATE TABLE group_quizzes (
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
  FOREIGN KEY (quiz_id) REFERENCES group_quizzes(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE `edit_users` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `institution` varchar(255) DEFAULT NULL,
  `branch` varchar(255) DEFAULT NULL,
  `year` varchar(50) DEFAULT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `profile_pic` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
