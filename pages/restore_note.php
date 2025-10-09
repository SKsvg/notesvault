<?php
// Start a new session
session_start();

// Database connection details
$servername = "localhost:3307";
$username = "root";
$password = "insathMYSQL#123";
$dbname = "notesvault";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $note_id = intval($_GET['id']);

    if ($note_id <= 0) {
        echo "Invalid note ID.";
        exit();
    }

    // Restore the note from trash
    $sql = "UPDATE notes SET is_trashed = 0 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $note_id);

    if ($stmt->execute()) {
        // Redirect back to bin page
        header("Location: bin.php");
        exit();
    } else {
        echo "Error restoring note.";
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}

$conn->close();
?>
