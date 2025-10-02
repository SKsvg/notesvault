<?php
header('Content-Type: application/json');

// Read JSON input
$input = json_decode(file_get_contents("php://input"), true);
$email = trim($input['email'] ?? '');

if (!$email) {
    echo json_encode(['exists' => false, 'message' => 'No email provided']);
    exit;
}

// Database connection (replace with your DB credentials)
$pdo = new PDO("mysql:host=localhost;dbname=your_db;charset=utf8", "db_user", "db_pass");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Check if user exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE email=?");
$stmt->execute([$email]);
$userId = $stmt->fetchColumn();

if ($userId) {
    echo json_encode(['exists' => true]);
} else {
    echo json_encode(['exists' => false, 'message' => 'User not registered']);
}
?>
