<?php
include 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $groupName = trim($data['groupName'] ?? '');
    if (!$groupName) {
        echo json_encode(['success' => false, 'message' => 'Group name required.']);
        exit;
    }

    // Get group id
    $stmt = $pdo->prepare("SELECT id FROM groups WHERE name = ?");
    $stmt->execute([$groupName]);
    $groupId = $stmt->fetchColumn();

    if ($groupId) {
        // Delete from database if exists
        // Delete quiz responses
        $stmt = $pdo->prepare("DELETE qr FROM quiz_responses qr JOIN quizzes q ON qr.quiz_id = q.id WHERE q.group_id = ?");
        $stmt->execute([$groupId]);

        // Delete meeting participants
        $stmt = $pdo->prepare("DELETE mp FROM meeting_participants mp JOIN meetings m ON mp.meeting_id = m.id WHERE m.group_id = ?");
        $stmt->execute([$groupId]);

        // Delete quizzes
        $stmt = $pdo->prepare("DELETE FROM quizzes WHERE group_id = ?");
        $stmt->execute([$groupId]);

        // Delete chats
        $stmt = $pdo->prepare("DELETE FROM chats WHERE group_id = ?");
        $stmt->execute([$groupId]);

        // Delete group notes
        $stmt = $pdo->prepare("DELETE FROM group_notes WHERE group_id = ?");
        $stmt->execute([$groupId]);

        // Delete meetings
        $stmt = $pdo->prepare("DELETE FROM meetings WHERE group_id = ?");
        $stmt->execute([$groupId]);

        // Delete members
        $stmt = $pdo->prepare("DELETE FROM group_members WHERE group_id = ?");
        $stmt->execute([$groupId]);

        // Delete group
        $stmt = $pdo->prepare("DELETE FROM groups WHERE id = ?");
        $stmt->execute([$groupId]);
    }

    // Also delete from JSON files
    $groupsFile = '../data/groups.json';
    if (file_exists($groupsFile)) {
        $groups = json_decode(file_get_contents($groupsFile), true);
        if (isset($groups[$groupName])) {
            unset($groups[$groupName]);
            file_put_contents($groupsFile, json_encode($groups));
        }
    }

    // Delete per-group JSON files
    $filesToDelete = [
        "../data/{$groupName}_chat.json",
        "../data/{$groupName}_notes.json",
        "../data/{$groupName}_games.json",
        "../data/{$groupName}_meeting.json"
    ];
    foreach ($filesToDelete as $file) {
        if (file_exists($file)) {
            unlink($file);
        }
    }

    echo json_encode(['success' => true, 'message' => 'Group and all related data deleted.']);
    exit;
}

echo json_encode(['success' => false, 'message' => 'Invalid request.']);
