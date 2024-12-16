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
    // Générer de nouvelles missions si nécessaire
    generateDailyMissions($_SESSION['user_id']);

    // Récupérer les missions actives
    $stmt = $db->prepare("
        SELECT m.*, um.progress, um.completed, um.expires_at
        FROM daily_missions m
        JOIN user_missions um ON m.id = um.mission_id
        WHERE um.user_id = ? AND um.expires_at > NOW()
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $missions = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'missions' => $missions
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur']);
}