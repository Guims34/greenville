<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

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

$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if (empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'Message vide']);
    exit;
}

// Limiter la longueur du message
$message = substr($message, 0, 500);

try {
    $stmt = $db->prepare("
        INSERT INTO chat_messages (user_id, message) 
        VALUES (:user_id, :message)
    ");
    
    $stmt->execute([
        'user_id' => $_SESSION['user_id'],
        'message' => $message
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur']);
}