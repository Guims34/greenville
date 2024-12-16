<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

$mission_id = filter_input(INPUT_POST, 'mission_id', FILTER_SANITIZE_NUMBER_INT);

if (!$mission_id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de mission invalide']);
    exit;
}

try {
    $db->beginTransaction();

    // Vérifier que la mission est complétée et non réclamée
    $stmt = $db->prepare("
        SELECT m.*, um.completed, um.claimed
        FROM daily_missions m
        JOIN user_missions um ON m.id = um.mission_id
        WHERE m.id = ? AND um.user_id = ? AND um.completed = TRUE AND um.claimed = FALSE
    ");
    $stmt->execute([$mission_id, $_SESSION['user_id']]);
    $mission = $stmt->fetch();

    if (!$mission) {
        throw new Exception('Mission non disponible');
    }

    // Donner les récompenses
    $stmt = $db->prepare("
        UPDATE users 
        SET coins = coins + ?,
            experience = experience + ?
        WHERE id = ?
    ");
    $stmt->execute([
        $mission['coins_reward'],
        $mission['xp_reward'],
        $_SESSION['user_id']
    ]);

    // Marquer comme réclamée
    $stmt = $db->prepare("
        UPDATE user_missions 
        SET claimed = TRUE 
        WHERE mission_id = ? AND user_id = ?
    ");
    $stmt->execute([$mission_id, $_SESSION['user_id']]);

    // Mettre à jour les statistiques
    $stmt = $db->prepare("
        UPDATE user_stats 
        SET missions_completed = missions_completed + 1,
            missions_completed_today = missions_completed_today + 1,
            missions_completed_week = missions_completed_week + 1
        WHERE user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);

    $db->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}