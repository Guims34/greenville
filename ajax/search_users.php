<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

try {
    // Rechercher les utilisateurs qui ne sont pas déjà amis
$stmt = $db->prepare("
    SELECT DISTINCT u.id, u.username, u.level
    FROM users u
    WHERE u.id != ? 
    AND u.username LIKE ?
    AND u.status = 'active'
    AND NOT EXISTS (
        SELECT 1 FROM friendships f 
        WHERE (f.sender_id = ? AND f.receiver_id = u.id)
        OR (f.sender_id = u.id AND f.receiver_id = ?)
    )
    LIMIT 10
");

    
    $stmt->execute([
        $_SESSION['user_id'],
        $_SESSION['user_id'],
        $_SESSION['user_id'],
        $_SESSION['user_id'],
        $_SESSION['user_id'],
        "%$query%"
    ]);
    
    $users = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'users' => $users
    ]);

} catch (Exception $e) {
    error_log("Erreur recherche utilisateurs: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur']);
}