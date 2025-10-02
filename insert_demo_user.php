<?php
require 'pages/db.php'; // Include database connection

$email = 'chathumeeransisi567@gmail.com';
$name = 'Demo User'; // Assuming a name
$password = 'Ransisi22*';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Check if user already exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    echo "User already exists.\n";
    exit;
}

// Insert the user
$stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
if ($stmt->execute([$name, $email, $hashed_password])) {
    echo "Demo user inserted successfully.\n";
} else {
    echo "Failed to insert user.\n";
}
?>
