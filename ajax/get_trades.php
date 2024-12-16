<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

try {
    $stmt = $db->prepare("
        SELECT COUNT(*) as count
        FROM trades 
        WHERE (sender_id = ? OR receiver_id = ?)
        AND status = 'pending'
    ");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $result = $stmt->fetch();

    echo json_encode([
        'refresh' => $result['count'] > 0
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur']);
}