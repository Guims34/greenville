<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$plant_id = filter_input(INPUT_POST, 'plant_id', FILTER_SANITIZE_NUMBER_INT);

if (!$plant_id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de plante invalide']);
    exit;
}

try {
    $db->beginTransaction();

    // Vérifier que la plante appartient à l'utilisateur et est prête à être récoltée
    $stmt = $db->prepare("
        SELECT p.*, s.xp_reward 
        FROM plants p
        JOIN strains s ON p.strain = s.id
        WHERE p.id = ? AND p.user_id = ?
    ");
    $stmt->execute([$plant_id, $_SESSION['user_id']]);
    $plant = $stmt->fetch();

    if (!$plant) {
        throw new Exception('Plante non trouvée');
    }

    // Vérifier si la plante est mature
    $created = new DateTime($plant['created_at']);
    $now = new DateTime();
    $age = $created->diff($now)->days;
    
    if ($age < $plant['growth_time']) {
        throw new Exception('La plante n\'est pas encore prête à être récoltée');
    }

    // Calculer la récompense basée sur la santé de la plante
    $reward_multiplier = $plant['health'] / 100;
    $coins_reward = floor(1000 * $reward_multiplier);
    $xp_reward = floor($plant['xp_reward'] * $reward_multiplier);

    // Mettre à jour l'utilisateur avec les récompenses
    $stmt = $db->prepare("
        UPDATE users 
        SET coins = coins + ?, 
            experience = experience + ? 
        WHERE id = ?
    ");
    $stmt->execute([$coins_reward, $xp_reward, $_SESSION['user_id']]);

    // Supprimer la plante
    $stmt = $db->prepare("DELETE FROM plants WHERE id = ?");
    $stmt->execute([$plant_id]);

    $db->commit();

    echo json_encode([
        'success' => true,
        'rewards' => [
            'coins' => $coins_reward,
            'xp' => $xp_reward
        ]
    ]);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}