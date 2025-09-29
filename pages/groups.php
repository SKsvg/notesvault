<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['api']) && $_GET['api'] === 'get_groups') {
    $current_user_email = $_SESSION['user_email'] ?? null;
    if (!$current_user_email) {
        echo json_encode([]);
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$current_user_email]);
    $user_id = $stmt->fetchColumn();
    if (!$user_id) {
        echo json_encode([]);
        exit;
    }

    $stmt = $pdo->prepare("
        SELECT g.id, g.name, u.email as creator,
               (SELECT GROUP_CONCAT(u2.email SEPARATOR ',') FROM group_members gm JOIN users u2 ON gm.user_id = u2.id WHERE gm.group_id = g.id) as members_str
        FROM groups g
        JOIN users u ON g.created_by = u.id
        WHERE g.created_by = ? OR EXISTS (
            SELECT 1 FROM group_members gm WHERE gm.group_id = g.id AND gm.user_id = ?
        )
    ");
    $stmt->execute([$user_id, $user_id]);
    $groups = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Merge with JSON groups not in DB
    $groupsFile = '../data/groups.json';
    if (file_exists($groupsFile)) {
        $jsonGroups = json_decode(file_get_contents($groupsFile), true) ?: [];
        $dbNames = array_column($groups, 'name');

        foreach ($jsonGroups as $key => $jsonGroup) {
            $name = null;
            $creator = $jsonGroup['creator'] ?? 'Unknown';
            $members = $jsonGroup['members'] ?? [];

            if (isset($jsonGroup['name'])) {
                $name = $jsonGroup['name'];
            } elseif (is_string($key) && !is_numeric($key)) {
                $name = $key;
            }

            if ($name && !in_array($name, $dbNames) && in_array($current_user_email, $members)) {
                $groups[] = [
                    'id' => 0,
                    'name' => $name,
                    'creator' => $creator,
                    'members_str' => implode(',', $members)
                ];
            }
        }
    }

    // Parse members back to array
    foreach ($groups as &$group) {
        $group['members'] = $group['members_str'] ? explode(',', $group['members_str']) : [];
        unset($group['members_str']);
    }

    echo json_encode($groups);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $groupName = trim($data['groupName'] ?? '');
    $creator = trim($data['creator'] ?? '');

    if (!$groupName || !$creator) {
        echo json_encode(['success' => false, 'message' => 'Group name and creator required.']);
        exit;
    }

    // Ensure creator matches the logged-in user
    $current_user_email = $_SESSION['user_email'] ?? null;
    if (!$current_user_email || $creator !== $current_user_email) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized to create group.']);
        exit;
    }

    // Check if group exists
    $stmt = $pdo->prepare("SELECT id FROM groups WHERE name = ?");
    $stmt->execute([$groupName]);
    if ($stmt->fetchColumn()) {
        echo json_encode(['success' => false, 'message' => 'Group already exists.']);
        exit;
    }

    // Get creator id
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$creator]);
    $creatorId = $stmt->fetchColumn();
    if (!$creatorId) {
        echo json_encode(['success' => false, 'message' => 'Creator not found.']);
        exit;
    }

    // Create group
    $stmt = $pdo->prepare("INSERT INTO groups (name, created_by) VALUES (?, ?)");
    $stmt->execute([$groupName, $creatorId]);
    $groupId = $pdo->lastInsertId();

    // Add creator as member
    $stmt = $pdo->prepare("INSERT INTO group_members (group_id, user_id) VALUES (?, ?)");
    $stmt->execute([$groupId, $creatorId]);

    echo json_encode(['success' => true, 'message' => 'Group created.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request.']);
?>
