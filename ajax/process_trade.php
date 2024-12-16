<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

$action = $_POST['action'] ?? '';
$trade_id = filter_input(INPUT_POST, 'trade_id', FILTER_SANITIZE_NUMBER_INT);

if (!$trade_id || !in_array($action, ['accept', 'reject', 'cancel'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Paramètres invalides']);
    exit;
}

try {
    $db->beginTransaction();

    // Vérifier l'échange
    $stmt = $db->prepare("
        SELECT t.*, ti.* 
        FROM trades t
        LEFT JOIN trade_items ti ON t.id = ti.trade_id
        WHERE t.id = ? AND t.status = 'pending'
    ");
    $stmt->execute([$trade_id]);
    $trade = $stmt->fetch();

    if (!$trade) {
        throw new Exception('Échange non trouvé ou déjà traité');
    }

    // Vérifier les permissions
    if ($action === 'accept' && $trade['receiver_id'] !== $_SESSION['user_id']) {
        throw new Exception('Vous ne pouvez pas accepter cet échange');
    }
    if ($action === 'cancel' && $trade['sender_id'] !== $_SESSION['user_id']) {
        throw new Exception('Vous ne pouvez pas annuler cet échange');
    }

    if ($action === 'accept') {
        // Vérifier que les items sont toujours disponibles
        $stmt = $db->prepare("
            SELECT ti.*, ui.quantity as available_quantity
            FROM trade_items ti
            JOIN user_inventory ui ON ti.item_id = ui.item_id AND ti.user_id = ui.user_id
            WHERE ti.trade_id = ?
        ");
        $stmt->execute([$trade_id]);
        $items = $stmt->fetchAll();

        foreach ($items as $item) {
            if ($item['quantity'] > $item['available_quantity']) {
                throw new Exception('Un ou plusieurs items ne sont plus disponibles');
            }
        }

        // Transférer les items
        foreach ($items as $item) {
            // Retirer les items du vendeur
            $stmt = $db->prepare("
                UPDATE user_inventory 
                SET quantity = quantity - ? 
                WHERE user_id = ? AND item_id = ?
            ");
            $stmt->execute([$item['quantity'], $item['user_id'], $item['item_id']]);

            // Ajouter les items à l'acheteur
            $stmt = $db->prepare("
                INSERT INTO user_inventory (user_id, item_id, quantity) 
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE quantity = quantity + ?
            ");
            $receiver_id = $item['user_id'] === $trade['sender_id'] ? $trade['receiver_id'] : $trade['sender_id'];
            $stmt->execute([$receiver_id, $item['item_id'], $item['quantity'], $item['quantity']]);
        }
    }

    // Mettre à jour le statut de l'échange
    $status = match($action) {
        'accept' => 'accepted',
        'reject' => 'rejected',
        'cancel' => 'cancelled'
    };

    $stmt = $db->prepare("
        UPDATE trades 
        SET status = ?, completed_at = NOW() 
        WHERE id = ?
    ");
    $stmt->execute([$status, $trade_id]);

    $db->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}