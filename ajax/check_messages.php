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
    // Compter les messages non lus
    $stmt = $db->prepare("
        SELECT COUNT(*) as unread
        FROM private_messages
        WHERE receiver_id = ? AND read_at IS NULL
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'unread' => (int)$result['unread']
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur']);
}