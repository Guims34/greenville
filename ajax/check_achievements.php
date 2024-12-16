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
    // Vérifier les succès
    checkAchievements($_SESSION['user_id']);

    // Récupérer les succès mis à jour
    $stmt = $db->prepare("
        SELECT a.*, ua.progress, ua.completed, ua.completed_at
        FROM achievements a
        LEFT JOIN user_achievements ua ON a.id = ua.achievement_id AND ua.user_id = ?
        ORDER BY a.category, a.difficulty
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $achievements = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'achievements' => $achievements
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur']);
}