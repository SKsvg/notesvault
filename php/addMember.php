<?php
session_start();
header('Content-Type: application/json');

// CRITICAL FIX: Ensure db.php is included BEFORE database connection logic
if (file_exists('db.php')) {
    include 'db.php'; 
} else {
    // Fail safe if db.php is missing
    echo json_encode(['success' => false, 'message' => 'Configuration error: db.php not found.']);
    exit;
}

// Read JSON input
$input = json_decode(file_get_contents("php://input"), true);
$groupName = trim($input['groupName'] ?? '');
$email = trim($input['email'] ?? '');

if (!$groupName || !$email) {
    echo json_encode(['success' => false, 'message' => 'Group name and email required.']);
    exit;
}

try {
    // 1. Registration Check: Get user ID from the 'users' table
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email=?");
    $stmt->execute([$email]);
    $userId = $stmt->fetchColumn();
    
    // Member not registered on the website
    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'User is not registered on the website. Cannot add member.']);
        exit;
    }

    // Get group ID
    $stmt = $pdo->prepare("SELECT id FROM groups WHERE name=?");
    $stmt->execute([$groupName]);
    $groupId = $stmt->fetchColumn();
    if (!$groupId) {
        echo json_encode(['success' => false, 'message' => 'Group not found.']);
        exit;
    }

    // Check existing membership (prevents duplicates)
    $stmt = $pdo->prepare("SELECT id FROM group_members WHERE group_id=? AND user_id=?");
    $stmt->execute([$groupId, $userId]);
    if ($stmt->fetchColumn()) {
        echo json_encode(['success' => false, 'message' => 'User already a member of this group.']);
        exit;
    }

    // Insert member
    $stmt = $pdo->prepare("INSERT INTO group_members (group_id, user_id, role, status) VALUES (?, ?, 'member', 'active')");
    $stmt->execute([$groupId, $userId]);

    // Optional: send email
    $subject = "Added to group: $groupName";
    $message = "Hello,\n\nYou have been added to the group '$groupName'.\n\nNotesVault Team";
    $headers = "From: noreply@notesvault.com\r\n";
    @mail($email, $subject, $message, $headers);

    echo json_encode(['success' => true, 'message' => "Member $email added successfully."]);
} catch (PDOException $e) {
    // This catches database errors and sends a clean JSON error response
    error_log("Add Member PDO Error: " . $e->getMessage()); 
    echo json_encode(['success' => false, 'message' => 'Database error. Check server logs.']);
} catch (Exception $e) {
    // This catches general exceptions
    error_log("Add Member General Error: " . $e->getMessage()); 
    echo json_encode(['success' => false, 'message' => 'An unexpected error occurred.']);
}
?>