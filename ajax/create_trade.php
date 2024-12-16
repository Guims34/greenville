<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

$receiver_id = filter_input(INPUT_POST, 'receiver_id', FILTER_SANITIZE_NUMBER_INT);
$items = $_POST['items'] ?? [];

if (!$receiver_id || empty($items)) {
    http_response_code(400);
    echo json_encode(['error' => 'Paramètres invalides']);
    exit;
}

try {
    $db->beginTransaction();

    // Vérifier que le destinataire existe
    $stmt = $db->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$receiver_id]);
    if (!$stmt->fetch()) {
        throw new Exception('Destinataire non trouvé');
    }

    // Créer l'échange
    $stmt = $db->prepare("
        INSERT INTO trades (sender_id, receiver_id, status) 
        VALUES (?, ?, 'pending')
    ");
    $stmt->execute([$_SESSION['user_id'], $receiver_id]);
    $trade_id = $db->lastInsertId();

    // Ajouter les items
    foreach ($items as $item) {
        // Vérifier que l'utilisateur possède l'item
        $stmt = $db->prepare("
            SELECT quantity 
            FROM user_inventory 
            WHERE user_id = ? AND item_id = ?
        ");
        $stmt->execute([$_SESSION['user_id'], $item['id']]);
        $inventory = $stmt->fetch();

        if (!$inventory || $inventory['quantity'] < $item['quantity']) {
            throw new Exception('Item non disponible en quantité suffisante');
        }

        // Ajouter l'item à l'échange
        $stmt = $db->prepare("
            INSERT INTO trade_items (trade_id, user_id, item_id, quantity) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$trade_id, $_SESSION['user_id'], $item['id'], $item['quantity']]);
    }

    $db->commit();
    echo json_encode(['success' => true, 'trade_id' => $trade_id]);

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}