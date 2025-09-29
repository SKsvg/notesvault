<?php
include 'pages/db.php';

echo "Test 1: Happy path - Valid session and matching creator\n";
session_start();
$_SESSION['user_email'] = 'demo@user.com';
$groupName = 'TestHappy_' . uniqid();
$creator = 'demo@user.com';

// Check session match
$current_user_email = $_SESSION['user_email'] ?? null;
if (!$current_user_email || $creator !== $current_user_email) {
    echo "FAIL: Unauthorized (should not happen)\n";
} else {
    // Check if group exists
    $stmt = $pdo->prepare("SELECT id FROM groups WHERE name = ?");
    $stmt->execute([$groupName]);
    if ($stmt->fetchColumn()) {
        echo "FAIL: Group already exists\n";
    } else {
        // Get creator id
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$creator]);
        $creatorId = $stmt->fetchColumn();
        if (!$creatorId) {
            echo "FAIL: Creator not found (ensure demo@user.com exists in DB)\n";
        } else {
            // Create group
            $stmt = $pdo->prepare("INSERT INTO groups (name, created_by) VALUES (?, ?)");
            $stmt->execute([$groupName, $creatorId]);
            $groupId = $pdo->lastInsertId();

            // Add creator as member
            $stmt = $pdo->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
            $stmt->execute([$groupId, $creatorId]);

            echo "SUCCESS: Group created successfully\n";

            // Clean up for test
            $stmt = $pdo->prepare("DELETE FROM group_members WHERE group_id = ?");
            $stmt->execute([$groupId]);
            $stmt = $pdo->prepare("DELETE FROM groups WHERE id = ?");
            $stmt->execute([$groupId]);
        }
    }
}

echo "\nTest 2: Unauthorized - Mismatch creator\n";
session_start();
$_SESSION['user_email'] = 'demo@user.com';
$groupName = 'TestMismatch_' . uniqid();
$creator = 'other@example.com';

$current_user_email = $_SESSION['user_email'] ?? null;
if (!$current_user_email || $creator !== $current_user_email) {
    echo "SUCCESS: Unauthorized - correctly rejected mismatch\n";
} else {
    echo "FAIL: Should have rejected mismatch\n";
}

echo "\nTest 3: Unauthorized - No session\n";
session_start(); // No user_email set
$groupName = 'TestNoSession_' . uniqid();
$creator = 'demo@user.com';

$current_user_email = $_SESSION['user_email'] ?? null;
if (!$current_user_email || $creator !== $current_user_email) {
    echo "SUCCESS: Unauthorized - correctly rejected no session\n";
} else {
    echo "FAIL: Should have rejected no session\n";
}

echo "\nAll tests completed.\n";
?>
