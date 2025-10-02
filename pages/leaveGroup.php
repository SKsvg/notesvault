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

    if ($groupId) {
        // Delete from members if group exists in database
        $stmt = $pdo->prepare("DELETE FROM group_members WHERE group_id = ? AND user_id = ?");
        $stmt->execute([$groupId, $userId]);
    }

    // Also remove from JSON
    $groupsFile = '../data/groups.json';
    if (file_exists($groupsFile)) {
        $groups = json_decode(file_get_contents($groupsFile), true);
        if (isset($groups[$groupName]['members']) && in_array($email, $groups[$groupName]['members'])) {
            $groups[$groupName]['members'] = array_diff($groups[$groupName]['members'], [$email]);
            file_put_contents($groupsFile, json_encode($groups));
        }
    }

    echo json_encode(['success' => true, 'message' => 'Left group.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request.']);
?>
