<?php
// Start the session on every page
session_start();

// Include your new PDO database connection file.
// The path is 'up one directory' from the /pages folder.
require_once __DIR__ . '/db.php';

// Initialize $user to null.
$user = null;

// Check if the user is logged in
if (isset($_SESSION['user_email'])) {
    try {
        $email = $_SESSION['user_email'];

        // 1. Prepare the statement using the $pdo object
        $stmt = $pdo->prepare("SELECT u.id, u.name, u.email, e.phone, e.institution, e.branch, e.year, e.student_id, e.profile_pic
                               FROM users u
                               LEFT JOIN edit_users e ON u.id = e.user_id
                               WHERE u.email = ?");

        // 2. Execute the statement, passing the email directly
        $stmt->execute([$email]);

        // 3. Fetch the user data. No need for get_result()!
        // This works because you set the default fetch mode in your connection file.
        $user = $stmt->fetch();

    } catch (PDOException $e) {
        // If there's a database error during the query, you can handle it here
        // For now, we can just stop the script.
        die("Error fetching user data: " . $e->getMessage());
    }
}
?>