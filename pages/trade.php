<?php
requireAuth();

// Récupérer l'item à échanger si spécifié
$item_id = filter_input(INPUT_GET, 'item', FILTER_SANITIZE_NUMBER_INT);
$selected_item = null;

if ($item_id) {
    $stmt = $db->prepare("
        SELECT i.*, s.name, s.description, s.type, s.rarity
        FROM user_inventory i
        JOIN shop_items s ON i.item_id = s.id
        WHERE i.user_id = ? AND i.item_id = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $item_id]);
    $selected_item = $stmt->fetch();
}

// Récupérer les échanges en cours
$stmt = $db->prepare("
    SELECT t.*, 
           u1.username as sender_name,
           u2.username as receiver_name
    FROM trades t
    JOIN users u1 ON t.sender_id = u1.id
    JOIN users u2 ON t.receiver_id = u2.id
    WHERE t.sender_id = ? OR t.receiver_id = ?
    ORDER BY t.created_at DESC
");
$stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
$trades = $stmt->fetchAll();

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['create_trade'])) {
            $receiver_id = filter_input(INPUT_POST, 'receiver_id', FILTER_SANITIZE_NUMBER_INT);
            $items = isset($_POST['items']) ? $_POST['items'] : [];
            
            if (empty($items)) {
                throw new Exception("Sélectionnez au moins un item à échanger");
            }

            $db->beginTransaction();

            // Créer l'échange
            $stmt = $db->prepare("
                INSERT INTO trades (sender_id, receiver_id, status) 
                VALUES (?, ?, 'pending')
            ");
            $stmt->execute([$_SESSION['user_id'], $receiver_id]);
            $trade_id = $db->lastInsertId();

            // Ajouter les items
            $stmt = $db->prepare("
                INSERT INTO trade_items (trade_id, user_id, item_id, quantity) 
                VALUES (?, ?, ?, ?)
            ");

            foreach ($items as $item) {
                $stmt->execute([
                    $trade_id,
                    $_SESSION['user_id'],
                    $item['id'],
                    $item['quantity']
                ]);
            }

            $db->commit();
            $_SESSION['success_message'] = "Proposition d'échange envoyée !";
            header('Location: index.php?page=trade');
            exit;

        } elseif (isset($_POST['accept_trade'])) {
            $trade_id = filter_input(INPUT_POST, 'trade_id', FILTER_SANITIZE_NUMBER_INT);
            
            $db->beginTransaction();

            // Vérifier l'échange
            $stmt = $db->prepare("
                SELECT * FROM trades 
                WHERE id = ? AND receiver_id = ? AND status = 'pending'
            ");
            $stmt->execute([$trade_id, $_SESSION['user_id']]);
            $trade = $stmt->fetch();

            if (!$trade) {
                throw new Exception("Échange invalide");
            }

            // Récupérer les items
            $stmt = $db->prepare("
                SELECT * FROM trade_items 
                WHERE trade_id = ?
            ");
            $stmt->execute([$trade_id]);
            $items = $stmt->fetchAll();

            // Transférer les items
            $stmt = $db->prepare("
                UPDATE user_inventory 
                SET quantity = quantity - ? 
                WHERE user_id = ? AND item_id = ?
            ");

            $stmt2 = $db->prepare("
                INSERT INTO user_inventory (user_id, item_id, quantity)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE quantity = quantity + ?
            ");

            foreach ($items as $item) {
                // Retirer les items du vendeur
                $stmt->execute([
                    $item['quantity'],
                    $item['user_id'],
                    $item['item_id']
                ]);

                // Ajouter les items à l'acheteur
                $receiver_id = $trade['receiver_id'];
                if ($item['user_id'] === $trade['receiver_id']) {
                    $receiver_id = $trade['sender_id'];
                }

                $stmt2->execute([
                    $receiver_id,
                    $item['item_id'],
                    $item['quantity'],
                    $item['quantity']
                ]);
            }

            // Finaliser l'échange
            $stmt = $db->prepare("
                UPDATE trades 
                SET status = 'accepted', 
                    completed_at = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$trade_id]);

            $db->commit();
            $_SESSION['success_message'] = "Échange accepté !";
            header('Location: index.php?page=trade');
            exit;

        } elseif (isset($_POST['reject_trade'])) {
            $trade_id = filter_input(INPUT_POST, 'trade_id', FILTER_SANITIZE_NUMBER_INT);
            
            $stmt = $db->prepare("
                UPDATE trades 
                SET status = 'rejected', 
                    completed_at = NOW() 
                WHERE id = ? AND receiver_id = ? AND status = 'pending'
            ");
            $stmt->execute([$trade_id, $_SESSION['user_id']]);
            
            $_SESSION['success_message'] = "Échange refusé";
            header('Location: index.php?page=trade');
            exit;
        }
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $error = $e->getMessage();
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold">Échanges</h1>
        <a href="index.php?page=inventory" class="text-emerald-600 hover:text-emerald-700">
            Retour à l'inventaire →
        </a>
    </div>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php 
            echo htmlspecialchars($_SESSION['success_message']);
            unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Formulaire de nouvel échange -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold mb-4">Proposer un échange</h2>
        
        <form method="POST" class="space-y-6">
            <input type="hidden" name="create_trade" value="1">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Joueur destinataire
                </label>
                <select 
                    name="receiver_id" 
                    required
                    class="w-full rounded-md border-gray-300"
                >
                    <option value="">Sélectionner un joueur</option>
                    <?php
                    $stmt = $db->prepare("
                        SELECT id, username 
                        FROM users 
                        WHERE id != ? 
                        ORDER BY username
                    ");
                    $stmt->execute([$_SESSION['user_id']]);
                    while ($user = $stmt->fetch()) {
                        echo '<option value="' . $user['id'] . '">' . 
                             htmlspecialchars($user['username']) . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Items à échanger
                </label>
                <div class="space-y-4">
                    <?php
                    $stmt = $db->prepare("
                        SELECT i.*, s.name, s.type, s.rarity
                        FROM user_inventory i
                        JOIN shop_items s ON i.item_id = s.id
                        WHERE i.user_id = ? AND i.quantity > 0
                        ORDER BY s.type, s.rarity, s.name
                    ");
                    $stmt->execute([$_SESSION['user_id']]);
                    while ($item = $stmt->fetch()):
                    ?>
                        <div class="flex items-center gap-4">
                            <input 
                                type="checkbox" 
                                name="items[<?php echo $item['id']; ?>][id]" 
                                value="<?php echo $item['id']; ?>"
                                class="rounded border-gray-300 text-emerald-600"
                                <?php echo $selected_item && $selected_item['id'] === $item['id'] ? 'checked' : ''; ?>
                            >
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </span>
                                    <span class="text-sm text-gray-500">
                                        (Disponible: <?php echo $item['quantity']; ?>)
                                    </span>
                                </div>
                            </div>
                            <input 
                                type="number" 
                                name="items[<?php echo $item['id']; ?>][quantity]" 
                                min="1" 
                                max="<?php echo $item['quantity']; ?>" 
                                value="1"
                                class="w-20 rounded-md border-gray-300"
                            >
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="flex justify-end">
                <button 
                    type="submit"
                    class="px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600"
                >
                    Proposer l'échange
                </button>
            </div>
        </form>
    </div>

    <!-- Liste des échanges -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4">Échanges en cours</h2>

        <?php if (empty($trades)): ?>
            <p class="text-gray-500 text-center py-4">
                Aucun échange en cours
            </p>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($trades as $trade): ?>
                    <div class="border rounded-lg p-4">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <div class="text-sm text-gray-500">
                                    <?php if ($trade['sender_id'] === $_SESSION['user_id']): ?>
                                        Échange proposé à <?php echo htmlspecialchars($trade['receiver_name']); ?>
                                    <?php else: ?>
                                        Proposition de <?php echo htmlspecialchars($trade['sender_name']); ?>
                                    <?php endif; ?>
                                </div>
                                <div class="text-sm text-gray-500">
                                    <?php echo date('d/m/Y H:i', strtotime($trade['created_at'])); ?>
                                </div>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full <?php
                                echo match($trade['status']) {
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'accepted' => 'bg-green-100 text-green-800',
                                    'rejected' => 'bg-red-100 text-red-800',
                                    'cancelled' => 'bg-gray-100 text-gray-800'
                                };
                            ?>">
                                <?php echo match($trade['status']) {
                                    'pending' => 'En attente',
                                    'accepted' => 'Accepté',
                                    'rejected' => 'Refusé',
                                    'cancelled' => 'Annulé'
                                }; ?>
                            </span>
                        </div>

                        <!-- Items de l'échange -->
                        <div class="space-y-2 mb-4">
                            <?php
                            $stmt = $db->prepare("
                                SELECT ti.*, s.name, u.username
                                FROM trade_items ti
                                JOIN shop_items s ON ti.item_id = s.id
                                JOIN users u ON ti.user_id = u.id
                                WHERE ti.trade_id = ?
                            ");
                            $stmt->execute([$trade['id']]);
                            $trade_items = $stmt->fetchAll();

                            foreach ($trade_items as $item):
                            ?>
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="font-medium">
                                        <?php echo htmlspecialchars($item['username']); ?>
                                    </span>
                                    <span class="text-gray-500">propose</span>
                                    <span class="font-medium">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </span>
                                    <span class="text-gray-500">
                                        (x<?php echo $item['quantity']; ?>)
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if ($trade['status'] === 'pending'): ?>
                            <?php if ($trade['receiver_id'] === $_SESSION['user_id']): ?>
                                <div class="flex justify-end space-x-2">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="reject_trade" value="1">
                                        <input type="hidden" name="trade_id" value="<?php echo $trade['id']; ?>">
                                        <button 
                                            type="submit"
                                            class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600"
                                        >
                                            Refuser
                                        </button>
                                    </form>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="accept_trade" value="1">
                                        <input type="hidden" name="trade_id" value="<?php echo $trade['id']; ?>">
                                        <button 
                                            type="submit"
                                            class="px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600"
                                        >
                                            Accepter
                                        </button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <div class="flex justify-end">
                                    <form method="POST">
                                        <input type="hidden" name="cancel_trade" value="1">
                                        <input type="hidden" name="trade_id" value="<?php echo $trade['id']; ?>">
                                        <button 
                                            type="submit"
                                            class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600"
                                        >
                                            Annuler
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Mise à jour automatique des échanges
setInterval(() => {
    fetch('ajax/get_trades.php')
        .then(response => response.json())
        .then(data => {
            if (data.refresh) {
                location.reload();
            }
        });
}, 5000);
</script>