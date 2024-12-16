<?php
require_once '../config/database.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

$guild_id = filter_input(INPUT_POST, 'guild_id', FILTER_SANITIZE_NUMBER_INT);

if (!$guild_id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de guilde invalide']);
    exit;
}

try {
    $db->beginTransaction();

    // Vérifier si l'utilisateur n'est pas déjà dans une guilde
    $stmt = $db->prepare("
        SELECT 1 FROM guild_members 
        WHERE user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    if ($stmt->fetch()) {
        throw new Exception('Vous êtes déjà membre d\'une guilde');
    }

    // Vérifier si la guilde n'est pas pleine
    $stmt = $db->prepare("
        SELECT g.*, COUNT(gm.id) as member_count
        FROM guilds g
        LEFT JOIN guild_members gm ON g.id = gm.guild_id
        WHERE g.id = ?
        GROUP BY g.id
    ");
    $stmt->execute([$guild_id]);
    $guild = $stmt->fetch();

    if (!$guild) {
        throw new Exception('Guilde introuvable');
    }

    if ($guild['member_count'] >= $guild['member_limit']) {
        throw new Exception('Cette guilde est pleine');
    }

    // Ajouter le membre
    $stmt = $db->prepare("
        INSERT INTO guild_members (guild_id, user_id, role) 
        VALUES (?, ?, 'member')
    ");
    $stmt->execute([$guild_id, $_SESSION['user_id']]);

    // Ajouter un log
    $stmt = $db->prepare("
        INSERT INTO guild_logs (guild_id, user_id, action, details) 
        VALUES (?, ?, 'join', 'A rejoint la guilde')
    ");
    $stmt->execute([$guild_id, $_SESSION['user_id']]);

    $db->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $db->rollBack();
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}