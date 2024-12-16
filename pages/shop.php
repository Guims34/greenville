<?php
requireAuth();

// Récupérer les informations de l'utilisateur
$stmt = $db->prepare("SELECT coins, premium_coins, level FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Récupérer les items de la boutique
$stmt = $db->query("
    SELECT * FROM shop_items 
    WHERE active = true 
    ORDER BY level_required ASC, type ASC, rarity ASC
");
$items = $stmt->fetchAll();

// Organiser les items par catégorie
$categories = [
    'equipment' => ['name' => 'Équipements', 'items' => []],
    'boost' => ['name' => 'Boosts', 'items' => []],
    'cosmetic' => ['name' => 'Cosmétiques', 'items' => []]
];

foreach ($items as $item) {
    $categories[$item['type']]['items'][] = $item;
}

// Traitement des achats
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buy_item'])) {
    try {
        $item_id = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT);
        $quantity = filter_input(INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT) ?: 1;
        $currency = $_POST['currency'] === 'premium' ? 'premium' : 'coins';

        // Vérifier l'item
        $stmt = $db->prepare("SELECT * FROM shop_items WHERE id = ? AND active = true");
        $stmt->execute([$item_id]);
        $item = $stmt->fetch();

        if (!$item) {
            throw new Exception("Item non trouvé");
        }

        // Vérifier le niveau requis
        if ($user['level'] < $item['level_required']) {
            throw new Exception("Niveau insuffisant");
        }

        // Calculer le prix total
        $price = $currency === 'premium' ? $item['premium_price'] : $item['price'];
        $total_price = $price * $quantity;

        // Vérifier le stock
        if ($item['stock'] !== -1 && $item['stock'] < $quantity) {
            throw new Exception("Stock insuffisant");
        }

        // Vérifier les fonds
        $user_currency = $currency === 'premium' ? $user['premium_coins'] : $user['coins'];
        if ($user_currency < $total_price) {
            throw new Exception("Fonds insuffisants");
        }

        // Débuter la transaction
        $db->beginTransaction();

        // Mettre à jour les fonds
        $stmt = $db->prepare("
            UPDATE users 
            SET " . ($currency === 'premium' ? 'premium_coins' : 'coins') . " = " . ($currency === 'premium' ? 'premium_coins' : 'coins') . " - ? 
            WHERE id = ?
        ");
        $stmt->execute([$total_price, $_SESSION['user_id']]);

        // Mettre à jour le stock
        if ($item['stock'] !== -1) {
            $stmt = $db->prepare("UPDATE shop_items SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$quantity, $item_id]);
        }

        // Ajouter à l'inventaire
        $stmt = $db->prepare("
            INSERT INTO user_inventory (user_id, item_id, quantity) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE quantity = quantity + ?
        ");
        $stmt->execute([$_SESSION['user_id'], $item_id, $quantity, $quantity]);

        // Enregistrer la transaction
        $stmt = $db->prepare("
            INSERT INTO transactions (user_id, item_id, quantity, price_paid, currency_type, transaction_type) 
            VALUES (?, ?, ?, ?, ?, 'purchase')
        ");
        $stmt->execute([$_SESSION['user_id'], $item_id, $quantity, $total_price, $currency]);

        $db->commit();
        $_SESSION['success_message'] = "Achat effectué avec succès !";
        header('Location: index.php?page=shop');
        exit;

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
        <h1 class="text-2xl font-bold">Boutique</h1>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2">
                <span class="text-yellow-500">🪙</span>
                <span class="font-medium"><?php echo number_format($user['coins']); ?></span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-purple-500">💎</span>
                <span class="font-medium"><?php echo number_format($user['premium_coins']); ?></span>
            </div>
        </div>
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

    <!-- Onglets des catégories -->
    <div class="mb-8">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <?php foreach ($categories as $type => $category): ?>
                    <button 
                        onclick="switchTab('<?php echo $type; ?>')"
                        class="category-tab whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm <?php echo $type === 'equipment' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>"
                        data-tab="<?php echo $type; ?>"
                    >
                        <?php echo $category['name']; ?>
                    </button>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>

    <!-- Contenu des catégories -->
    <?php foreach ($categories as $type => $category): ?>
        <div 
            id="tab-<?php echo $type; ?>" 
            class="category-content <?php echo $type === 'equipment' ? '' : 'hidden'; ?>"
        >
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($category['items'] as $item): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </h3>
                                    <span class="inline-block px-2 py-1 text-xs rounded-full <?php
                                        echo match($item['rarity']) {
                                            'common' => 'bg-gray-100 text-gray-800',
                                            'uncommon' => 'bg-green-100 text-green-800',
                                            'rare' => 'bg-blue-100 text-blue-800',
                                            'epic' => 'bg-purple-100 text-purple-800',
                                            'legendary' => 'bg-yellow-100 text-yellow-800'
                                        };
                                    ?>">
                                        <?php echo ucfirst($item['rarity']); ?>
                                    </span>
                                </div>
                                <?php if ($item['level_required'] > $user['level']): ?>
                                    <span class="text-red-600 text-sm">
                                        Niveau <?php echo $item['level_required']; ?> requis
                                    </span>
                                <?php endif; ?>
                            </div>

                            <p class="text-gray-600 text-sm mb-4">
                                <?php echo htmlspecialchars($item['description']); ?>
                            </p>

                            <div class="space-y-4">
                                <?php if ($item['price']): ?>
                                    <form method="POST" class="flex items-center gap-2">
                                        <input type="hidden" name="buy_item" value="1">
                                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                        <input type="hidden" name="currency" value="coins">
                                        
                                        <input 
                                            type="number" 
                                            name="quantity" 
                                            value="1" 
                                            min="1" 
                                            max="99"
                                            class="w-20 rounded-md border-gray-300"
                                        >
                                        
                                        <button 
                                            type="submit"
                                            class="flex-1 bg-emerald-500 text-white px-4 py-2 rounded-lg hover:bg-emerald-600 disabled:opacity-50"
                                            <?php echo $item['level_required'] > $user['level'] ? 'disabled' : ''; ?>
                                        >
                                            Acheter <?php echo number_format($item['price']); ?> 🪙
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($item['premium_price']): ?>
                                    <form method="POST" class="flex items-center gap-2">
                                        <input type="hidden" name="buy_item" value="1">
                                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                        <input type="hidden" name="currency" value="premium">
                                        
                                        <input 
                                            type="number" 
                                            name="quantity" 
                                            value="1" 
                                            min="1" 
                                            max="99"
                                            class="w-20 rounded-md border-gray-300"
                                        >
                                        
                                        <button 
                                            type="submit"
                                            class="flex-1 bg-purple-500 text-white px-4 py-2 rounded-lg hover:bg-purple-600 disabled:opacity-50"
                                            <?php echo $item['level_required'] > $user['level'] ? 'disabled' : ''; ?>
                                        >
                                            Acheter <?php echo number_format($item['premium_price']); ?> 💎
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
function switchTab(tabName) {
    // Masquer tous les contenus
    document.querySelectorAll('.category-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Réinitialiser tous les onglets
    document.querySelectorAll('.category-tab').forEach(tab => {
        tab.classList.remove('border-emerald-500', 'text-emerald-600');
        tab.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Afficher le contenu sélectionné
    document.getElementById('tab-' + tabName).classList.remove('hidden');
    
    // Activer l'onglet sélectionné
    const activeTab = document.querySelector(`.category-tab[data-tab="${tabName}"]`);
    activeTab.classList.remove('border-transparent', 'text-gray-500');
    activeTab.classList.add('border-emerald-500', 'text-emerald-600');
}
</script>