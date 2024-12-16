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
    // Vérifier que l'utilisateur existe
    $stmt = $db->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Utilisateur non trouvé');
    }

    // Vérifier qu'il n'y a pas déjà une demande d'ami
    $stmt = $db->prepare("
        SELECT id FROM friendships 
        WHERE (sender_id = ? AND receiver_id = ?)
        OR (sender_id = ? AND receiver_id = ?)
    ");
    $stmt->execute([$_SESSION['user_id'], $user_id, $user_id, $_SESSION['user_id']]);
    if ($stmt->fetch()) {
        throw new Exception('Une demande d\'ami existe déjà');
    }

    // Créer la demande d'ami
    $stmt = $db->prepare("
        INSERT INTO friendships (sender_id, receiver_id, status) 
        VALUES (?, ?, 'pending')
    ");
    $stmt->execute([$_SESSION['user_id'], $user_id]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}