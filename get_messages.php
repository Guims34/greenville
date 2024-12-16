<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

$lastTime = isset($_GET['last_time']) ? $_GET['last_time'] : '0';

try {
    $stmt = $db->prepare("
        SELECT m.*, u.username 
        FROM chat_messages m 
        JOIN users u ON m.user_id = u.id 
        WHERE m.created_at > :last_time
        ORDER BY m.created_at ASC
    ");
    
    $stmt->execute(['last_time' => $lastTime]);
    
    $messages = $stmt->fetchAll();
    
    // Ajouter un flag pour identifier les messages de l'utilisateur courant
    foreach ($messages as &$message) {
        $message['is_own'] = $message['user_id'] == $_SESSION['user_id'];
        $message['message'] = htmlspecialchars($message['message']);
    }
    
    echo json_encode($messages);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur']);
}