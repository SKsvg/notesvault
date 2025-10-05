<?php
session_start();
header("Content-Type: application/json");
include 'db.php';

// Check if user logged in
$current_user = $_SESSION['user_email'] ?? null;
if (!$current_user) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

// Decode JSON input
$input = json_decode(file_get_contents("php://input"), true);
if (!$input || empty($input['groupName']) || empty($input['email'])) {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

$groupName = trim($input['groupName']);
$email     = trim($input['email']);

// Prevent adding yourself
if (strcasecmp($email, $current_user) === 0) {
    echo json_encode(["success" => false, "message" => "You cannot add yourself to the group"]);
    exit;
}

try {
    // 1. Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $userId = $stmt->fetchColumn();

    if (!$userId) {
        echo json_encode(["success" => false, "message" => "This email is not a NotesVault member"]);
        exit;
    }

    // 2. Check if group exists
    $stmt = $pdo->prepare("SELECT id FROM groups WHERE name = ?");
    $stmt->execute([$groupName]);
    $groupId = $stmt->fetchColumn();

    if (!$groupId) {
        echo json_encode(["success" => false, "message" => "Group not found"]);
        exit;
    }

    // 3. Check if already a member
    $stmt = $pdo->prepare("SELECT id FROM group_members WHERE group_id = ? AND user_id = ?");
    $stmt->execute([$groupId, $userId]);
    if ($stmt->fetchColumn()) {
        echo json_encode(["success" => false, "message" => "User is already a member of this group"]);
        exit;
    }

    // 4. Add user to group
    $insert = $pdo->prepare("INSERT INTO group_members (group_id, user_id, status) VALUES (?, ?, 'member')");
    if ($insert->execute([$groupId, $userId])) {
        echo json_encode(["success" => true, "message" => "Successfully added to group"]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to add user"]);
    }

} catch (PDOException $e) {
    error_log("Add member error: " . $e->getMessage());
    echo json_encode(["success" => false, "message" => "Server error"]);
}
