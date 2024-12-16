<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

$notification_id = filter_input(INPUT_POST, 'notification_id', FILTER_SANITIZE_NUMBER_INT);

if (!$notification_id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de notification invalide']);
    exit;
}

try {
    $stmt = $db->prepare("
        UPDATE notifications 
        SET is_read = TRUE 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$notification_id, $_SESSION['user_id']]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur']);
}