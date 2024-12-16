<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

$user_id = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);

if (!$user_id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID utilisateur invalide']);
    exit;
}

try {
    // Vérifier que la demande existe
    $stmt = $db->prepare("
        SELECT id FROM friendships 
        WHERE sender_id = ? AND receiver_id = ? AND status = 'pending'
    ");
    $stmt->execute([$user_id, $_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        throw new Exception('Demande d\'ami non trouvée');
    }

    // Accepter la demande
    $stmt = $db->prepare("
        UPDATE friendships 
        SET status = 'accepted', updated_at = NOW()
        WHERE sender_id = ? AND receiver_id = ?
    ");
    $stmt->execute([$user_id, $_SESSION['user_id']]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}