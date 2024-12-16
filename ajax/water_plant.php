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
    // Vérifier que la plante appartient à l'utilisateur
    $stmt = $db->prepare("SELECT id, water_level FROM plants WHERE id = ? AND user_id = ?");
    $stmt->execute([$plant_id, $_SESSION['user_id']]);
    $plant = $stmt->fetch();

    if (!$plant) {
        throw new Exception('Plante non trouvée');
    }

    if ($plant['water_level'] >= 100) {
        throw new Exception('La plante est déjà bien hydratée');
    }

    // Mettre à jour le niveau d'eau
    $stmt = $db->prepare("
        UPDATE plants 
        SET water_level = 100, 
            last_watered = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$plant_id]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}