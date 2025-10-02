<?php
include 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $groupName = trim($data['groupName'] ?? '');
    $email = trim($data['email'] ?? '');
    if (!$groupName || !$email) {
        echo json_encode(['success' => false, 'message' => 'Group name and email required.']);
        exit;
    }

    // Get user id
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $userId = $stmt->fetchColumn();
    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }

    // Get group id
    $stmt = $pdo->prepare("SELECT id FROM groups WHERE name = ?");
    $stmt->execute([$groupName]);
    $groupId = $stmt->fetchColumn();
    if (!$groupId) {
        echo json_encode(['success' => false, 'message' => 'Group not found.']);
        exit;
    }

    // Check if already member
    $stmt = $pdo->prepare("SELECT id FROM group_members WHERE group_id = ? AND user_id = ?");
    $stmt->execute([$groupId, $userId]);
    if ($stmt->fetchColumn()) {
        echo json_encode(['success' => false, 'message' => 'Already a member.']);
        exit;
    }

    // Add to members
    $stmt = $pdo->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
    $stmt->execute([$groupId, $userId]);

    echo json_encode(['success' => true, 'message' => 'Joined group.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request.']);
?>
