<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

$item_id = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT);

if (!$item_id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID d\'item invalide']);
    exit;
}

try {
    $db->beginTransaction();

    // Vérifier que l'utilisateur possède l'item
    $stmt = $db->prepare("
        SELECT i.*, s.type 
        FROM user_inventory i
        JOIN shop_items s ON i.item_id = s.id
        WHERE i.user_id = ? AND i.item_id = ? AND i.quantity > 0
    ");
    $stmt->execute([$_SESSION['user_id'], $item_id]);
    $item = $stmt->fetch();

    if (!$item) {
        throw new Exception('Item non trouvé dans votre inventaire');
    }

    if ($item['type'] !== 'boost') {
        throw new Exception('Cet item ne peut pas être utilisé');
    }

    // Retirer une unité de l'item
    $stmt = $db->prepare("
        UPDATE user_inventory 
        SET quantity = quantity - 1 
        WHERE user_id = ? AND item_id = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $item_id]);

    // Appliquer l'effet du boost
    // TODO: Implémenter les effets spécifiques selon le type de boost

    $db->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}