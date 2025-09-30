<?php
// 1. Error Fix: Include central database connection
include 'db.php'; // Make sure db.php is configured correctly

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

    // Get members: Selects email and status
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
    // Log error but return empty array to client
    error_log("Load members error: " . $e->getMessage());
    echo json_encode([]);
}
?>