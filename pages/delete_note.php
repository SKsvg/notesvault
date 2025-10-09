<?php
// Start a new session
session_start();

// Database connection details
$servername = "localhost:3307";
$username = "root";
$password = "insathMYSQL#123";
$dbname = "test3";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $note_id = intval($_GET['id']);

    // Debug log received note ID
    error_log("Delete Note ID received: " . $note_id);

    if ($note_id <= 0) {
        echo "Invalid note ID.";
        exit();
    }

    // Optional: Check if the user is the uploader or admin
    // For now, allow delete without check

    // Move the note to trash instead of deleting
    $sql = "UPDATE notes SET is_trashed = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $note_id);

    if ($stmt->execute()) {
        // Redirect back to notes page
        header("Location: notes.php");
        exit();
    } else {
        echo "Error moving note to trash.";
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}

$conn->close();
?>
