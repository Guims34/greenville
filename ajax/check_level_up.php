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
    // Récupérer le niveau actuel
    $stmt = $db->prepare("
        SELECT 
            (SELECT level FROM levels WHERE xp_required <= u.experience ORDER BY level DESC LIMIT 1) as current_level,
            u.experience
        FROM users u 
        WHERE u.id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    // Récupérer les informations du niveau suivant
    $stmt = $db->prepare("
        SELECT * FROM levels 
        WHERE level = ? + 1
    ");
    $stmt->execute([$user['current_level']]);
    $nextLevel = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'currentLevel' => $user['current_level'],
        'currentXP' => $user['experience'],
        'nextLevelXP' => $nextLevel ? $nextLevel['xp_required'] : null,
        'progress' => $nextLevel ? 
            (($user['experience'] - $nextLevel['xp_required']) / ($nextLevel['xp_required'] - $user['xp_required'])) * 100 
            : 100
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur']);
}