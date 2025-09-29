<?php
include 'pages/db.php';

$email = 'test2@example.com';
$name = 'Test User 2';
$password = password_hash('password', PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->execute([$name, $email, $password]);
    echo "Test user 2 inserted successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
