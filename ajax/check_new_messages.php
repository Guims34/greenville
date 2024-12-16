<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

$user_id = filter_input(INPUT_GET, 'user', FILTER_SANITIZE_NUMBER_INT);

if (!$user_id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID utilisateur invalide']);
    exit;
}

try {
    // Vérifier s'il y a de nouveaux messages
    $stmt = $db->prepare("
        SELECT COUNT(*) as count
        FROM private_messages
        WHERE sender_id = ? AND receiver_id = ? AND read_at IS NULL
    ");
    $stmt->execute([$user_id, $_SESSION['user_id']]);
    $result = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'new_messages' => $result['count'] > 0
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur']);
}