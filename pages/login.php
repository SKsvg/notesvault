<?php
session_start(); // Start the session
require __DIR__ . '/db.php'; // Include the database connection file with absolute path

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $response['message'] = 'Please enter both email and password.';
    } else {
        try {
            // Fetch the user from the database by email
            $stmt = $pdo->prepare("SELECT id, name, password FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Password is correct, create a session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $email;
                $_SESSION['logged_in'] = true;

                $response['success'] = true;
                $response['message'] = 'Login successful!';
            } else {
                $response['message'] = 'Invalid email or password.';
            }
        } catch (PDOException $e) {
            // Include the exception message for debugging
            $response['message'] = 'Database error: ' . $e->getMessage();
        }
    }
} else {
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?>