<?php
require 'pages/db.php';

$creator_email = 'test1@example.com';
$group_name = 'Test Group 1';

try {
    // Get creator ID
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$creator_email]);
    $creator_id = $stmt->fetchColumn();

    if (!$creator_id) {
        die("Creator not found.");
    }

    // Check if group exists
    $stmt = $pdo->prepare("SELECT id FROM groups WHERE name = ?");
    $stmt->execute([$group_name]);
    if ($stmt->fetchColumn()) {
        die("Group already exists.");
    }

    // Insert group
    $stmt = $pdo->prepare("INSERT INTO groups (name, created_by) VALUES (?, ?)");
    $stmt->execute([$group_name, $creator_id]);
    $group_id = $pdo->lastInsertId();

    // Add creator as member
    $stmt = $pdo->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
    $stmt->execute([$group_id, $creator_id]);

    echo "Test group created successfully.";
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
