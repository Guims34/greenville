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
    // Vérifier les missions expirées
    $stmt = $db->prepare("
        SELECT COUNT(*) as count
        FROM user_missions
        WHERE user_id = ? AND expires_at <= NOW()
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $result = $stmt->fetch();

    // Si des missions sont expirées, générer de nouvelles missions
    if ($result['count'] > 0) {
        generateDailyMissions($_SESSION['user_id']);
        echo json_encode(['refresh' => true]);
    } else {
        echo json_encode(['refresh' => false]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur']);
}