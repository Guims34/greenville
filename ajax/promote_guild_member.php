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

    // Vérifier que l'utilisateur est le chef de la guilde
    $stmt = $db->prepare("
        SELECT 1 FROM guild_members 
        WHERE guild_id = ? AND user_id = ? AND role = 'leader'
    ");
    $stmt->execute([$guild_id, $_SESSION['user_id']]);
    if (!$stmt->fetch()) {
        throw new Exception('Vous devez être le chef de la guilde');
    }

    // Récupérer le rôle actuel du membre
    $stmt = $db->prepare("
        SELECT role FROM guild_members 
        WHERE guild_id = ? AND user_id = ?
    ");
    $stmt->execute([$guild_id, $user_id]);
    $current_role = $stmt->fetchColumn();

    if (!$current_role) {
        throw new Exception('Membre non trouvé');
    }

    // Promouvoir ou rétrograder
    $new_role = $current_role === 'member' ? 'officer' : 'member';
    
    $stmt = $db->prepare("
        UPDATE guild_members 
        SET role = ? 
        WHERE guild_id = ? AND user_id = ?
    ");
    $stmt->execute([$new_role, $guild_id, $user_id]);

    // Ajouter un log
    $action = $new_role === 'officer' ? 'promote' : 'demote';
    $stmt = $db->prepare("
        INSERT INTO guild_logs (guild_id, user_id, action, details) 
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $guild_id, 
        $_SESSION['user_id'],
        $action,
        $new_role === 'officer' ? 'A promu un membre au rang d\'officier' : 'A rétrogradé un officier au rang de membre'
    ]);

    $db->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $db->rollBack();
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}