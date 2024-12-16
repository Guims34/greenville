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
$guild_id = filter_input(INPUT_POST, 'guild_id', FILTER_SANITIZE_NUMBER_INT);

if (!$user_id || !$guild_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Paramètres invalides']);
    exit;
}

try {
    $db->beginTransaction();

    // Vérifier les permissions
    $stmt = $db->prepare("
        SELECT role FROM guild_members 
        WHERE guild_id = ? AND user_id = ?
    ");
    $stmt->execute([$guild_id, $_SESSION['user_id']]);
    $current_user_role = $stmt->fetchColumn();

    if (!$current_user_role || ($current_user_role !== 'leader' && $current_user_role !== 'officer')) {
        throw new Exception('Permissions insuffisantes');
    }

    // Vérifier le rôle du membre à exclure
    $stmt = $db->prepare("
        SELECT role FROM guild_members 
        WHERE guild_id = ? AND user_id = ?
    ");
    $stmt->execute([$guild_id, $user_id]);
    $member_role = $stmt->fetchColumn();

    if (!$member_role) {
        throw new Exception('Membre non trouvé');
    }

    // Un officier ne peut pas exclure un autre officier ou le chef
    if ($current_user_role === 'officer' && $member_role !== 'member') {
        throw new Exception('Vous ne pouvez pas exclure ce membre');
    }

    // Le chef ne peut pas être exclu
    if ($member_role === 'leader') {
        throw new Exception('Le chef ne peut pas être exclu');
    }

    // Exclure le membre
    $stmt = $db->prepare("
        DELETE FROM guild_members 
        WHERE guild_id = ? AND user_id = ?
    ");
    $stmt->execute([$guild_id, $user_id]);

    // Ajouter un log
    $stmt = $db->prepare("
        INSERT INTO guild_logs (guild_id, user_id, action, details) 
        VALUES (?, ?, 'kick', 'A exclu un membre')
    ");
    $stmt->execute([$guild_id, $_SESSION['user_id']]);

    $db->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $db->rollBack();
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}