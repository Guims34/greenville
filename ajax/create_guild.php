<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

// Vérifier si l'utilisateur n'est pas déjà dans une guilde
$stmt = $db->prepare("
    SELECT 1 FROM guild_members 
    WHERE user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
if ($stmt->fetch()) {
    http_response_code(400);
    echo json_encode(['error' => 'Vous êtes déjà membre d\'une guilde']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$description = trim($_POST['description'] ?? '');

if (empty($name) || empty($description)) {
    http_response_code(400);
    echo json_encode(['error' => 'Tous les champs sont requis']);
    exit;
}

try {
    $db->beginTransaction();

    // Créer la guilde
    $stmt = $db->prepare("
        INSERT INTO guilds (name, description, leader_id) 
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$name, $description, $_SESSION['user_id']]);
    $guild_id = $db->lastInsertId();

    // Ajouter le créateur comme leader
    $stmt = $db->prepare("
        INSERT INTO guild_members (guild_id, user_id, role) 
        VALUES (?, ?, 'leader')
    ");
    $stmt->execute([$guild_id, $_SESSION['user_id']]);

    // Ajouter un log
    $stmt = $db->prepare("
        INSERT INTO guild_logs (guild_id, user_id, action, details) 
        VALUES (?, ?, 'create', 'Guilde créée')
    ");
    $stmt->execute([$guild_id, $_SESSION['user_id']]);

    $db->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de la création de la guilde']);
}