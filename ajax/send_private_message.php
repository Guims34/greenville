<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => ' <boltAction type="file" filePath="ajax/send_private_message.php">Non autorisé']);
    exit;
}

$receiver_id = filter_input(INPUT_POST, 'receiver_id', FILTER_SANITIZE_NUMBER_INT);
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if (!$receiver_id || empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'Paramètres invalides']);
    exit;
}

try {
    // Vérifier que le destinataire existe et est ami
    $stmt = $db->prepare("
        SELECT 1 FROM friendships 
        WHERE ((sender_id = ? AND receiver_id = ?) 
        OR (sender_id = ? AND receiver_id = ?))
        AND status = 'accepted'
    ");
    $stmt->execute([
        $_SESSION['user_id'], 
        $receiver_id, 
        $receiver_id, 
        $_SESSION['user_id']
    ]);
    
    if (!$stmt->fetch()) {
        throw new Exception('Vous devez être amis pour envoyer un message');
    }

    // Envoyer le message
    $stmt = $db->prepare("
        INSERT INTO private_messages (sender_id, receiver_id, message) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$_SESSION['user_id'], $receiver_id, $message]);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}