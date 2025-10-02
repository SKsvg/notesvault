<?php
session_start();
header("Content-Type: application/json");
include 'db.php';

// Get current user
$current_user = $_SESSION['user_email'] ?? null;
if (!$current_user) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents("php://input"), true);
if (!$input || !isset($input['groupName']) || !isset($input['email'])) {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

$groupName = trim($input['groupName']);
$email = trim($input['email']);

if (empty($groupName) || empty($email)) {
    echo json_encode(["success" => false, "message" => "Group name and email are required"]);
    exit;
}

// Prevent adding yourself
if ($email === $current_user) {
    echo json_encode(["success" => false, "message" => "You cannot add yourself to the group"]);
    exit;
}

// Check if user exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$userId = $stmt->fetchColumn();

if (!$userId) {
    echo json_encode(["success" => false, "message" => "this mail is not in notesvault member"]);
    exit;
}

// Check if group exists
$stmt = $pdo->prepare("SELECT id FROM groups WHERE name = ?");
$stmt->execute([$groupName]);
$groupId = $stmt->fetchColumn();

if (!$groupId) {
    echo json_encode(["success" => false, "message" => "Group not found"]);
    exit;
}

// Check if already member
$stmt = $pdo->prepare("SELECT id FROM group_members WHERE group_id = ? AND user_id = ?");
$stmt->execute([$groupId, $userId]);
$existing = $stmt->fetchColumn();

if ($existing) {
    echo json_encode(["success" => false, "message" => "User is already a member of this group"]);
    exit;
}

// Add to members
$stmt = $pdo->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
$stmt->execute([$groupId, $userId]);

// Send email invitation
$subject = "Invitation to join study group: $groupName";
$message = "Hello,\n\nYou have been invited to join the study group '$groupName' on NotesVault.\n\nPlease log in to your account to access the group.\n\nBest regards,\nNotesVault Team";
$headers = "From: noreply@notesvault.com\r\n";

if (mail($email, $subject, $message, $headers)) {
    echo json_encode(["success" => true, "message" => "Invitation sent to $email and added to group"]);
} else {
    echo json_encode(["success" => true, "message" => "Added to group, but email could not be sent (check server configuration)"]);
}
?>

