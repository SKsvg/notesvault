<?php
// loadmembers.php
include 'db.php'; 

header('Content-Type: application/json');

$groupName = $_GET['groupName'] ?? 'default';

try {
    // Get group ID
    $stmt = $pdo->prepare("SELECT id FROM groups WHERE name=?");
    $stmt->execute([$groupName]);
    $groupId = $stmt->fetchColumn();
    if (!$groupId) {
        echo json_encode([]);
        exit;
    }

    // Get members list
    $stmt = $pdo->prepare("
        SELECT u.email, gm.status 
        FROM group_members gm
        JOIN users u ON gm.user_id = u.id
        WHERE gm.group_id=?
    ");
    $stmt->execute([$groupId]);
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($members);
} catch (PDOException $e) {
    error_log("Load members error: " . $e->getMessage());
    echo json_encode([]);
}
