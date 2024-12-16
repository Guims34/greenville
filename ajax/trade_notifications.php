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
    // Récupérer les notifications non lues
    $stmt = $db->prepare("
        SELECT tn.*, t.sender_id, t.receiver_id, 
               u1.username as sender_name, 
               u2.username as receiver_name
        FROM trade_notifications tn
        JOIN trades t ON tn.trade_id = t.id
        JOIN users u1 ON t.sender_id = u1.id
        JOIN users u2 ON t.receiver_id = u2.id
        WHERE tn.user_id = ? AND tn.read = FALSE
        ORDER BY tn.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $notifications = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'notifications' => $notifications
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur']);
}