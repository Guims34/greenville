<?php
requireAuth();

// Récupérer l'inventaire de l'utilisateur
$stmt = $db->prepare("
    SELECT i.*, s.name, s.description, s.type, s.rarity
    FROM user_inventory i
    JOIN shop_items s ON i.item_id = s.id
    WHERE i.user_id = ?
    ORDER BY s.type, s.rarity, s.name
");
$stmt->execute([$_SESSION['user_id']]);
$inventory = $stmt->fetchAll();

// Organiser les items par type
$categories = [
    'equipment' => ['name' => 'Équipements', 'items' => []],
    'boost' => ['name' => 'Boosts', 'items' => []],
    'cosmetic' => ['name' => 'Cosmétiques', 'items' => []]
];

foreach ($inventory as $item) {
    $categories[$item['type']]['items'][] = $item;
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold">Inventaire</h1>
        <a href="index.php?page=shop" class="text-emerald-600 hover:text-emerald-700">
            Visiter la boutique →
        </a>
    </div>

    <!-- Onglets des catégories -->
    <div class="mb-8">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <?php foreach ($categories as $type => $category): ?>
                    <button 
                        onclick="switchTab('<?php echo $type; ?>')"
                        class="inventory-tab whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm <?php echo $type === 'equipment' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'; ?>"
                        data-tab="<?php echo $type; ?>"
                    >
                        <?php echo $category['name']; ?>
                        <span class="ml-2 px-2 py-0.5 text-xs rounded-full bg-gray-100">
                            <?php echo count($category['items']); ?>
                        </span>
                    </button>
                <?php endforeach; ?>
            </nav>
        </div>
    </div>

    <!-- Contenu des catégories -->
    <?php foreach ($categories as $type => $category): ?>
        <div 
            id="inventory-<?php echo $type; ?>" 
            class="inventory-content <?php echo $type === 'equipment' ? '' : 'hidden'; ?>"
        >
            <?php if (empty($category['items'])): ?>
                <div class="text-center py-8 text-gray-500">
                    Aucun item dans cette catégorie
                </div>
            <?php else: ?>
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
                                    <span class="text-gray-600">
                                        x<?php echo $item['quantity']; ?>
                                    </span>
                                </div>

                                <p class="text-gray-600 text-sm mb-4">
                                    <?php echo htmlspecialchars($item['description']); ?>
                                </p>

                                <div class="flex justify-end space-x-2">
                                    <?php if ($type === 'boost'): ?>
                                        <button 
                                            onclick="useItem(<?php echo $item['id']; ?>)"
                                            class="px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600"
                                        >
                                            Utiliser
                                        </button>
                                    <?php elseif ($type === 'cosmetic'): ?>
                                        <button 
                                            onclick="equipItem(<?php echo $item['id']; ?>)"
                                            class="px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600"
                                        >
                                            Équiper
                                        </button>
                                    <?php endif; ?>
                                    
                                    <button 
                                        onclick="tradeItem(<?php echo $item['id']; ?>)"
                                        class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
                                    >
                                        Échanger
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<script>
function switchTab(tabName) {
    // Masquer tous les contenus
    document.querySelectorAll('.inventory-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Réinitialiser tous les onglets
    document.querySelectorAll('.inventory-tab').forEach(tab => {
        tab.classList.remove('border-emerald-500', 'text-emerald-600');
        tab.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Afficher le contenu sélectionné
    document.getElementById('inventory-' + tabName).classList.remove('hidden');
    
    // Activer l'onglet sélectionné
    const activeTab = document.querySelector(`.inventory-tab[data-tab="${tabName}"]`);
    activeTab.classList.remove('border-transparent', 'text-gray-500');
    activeTab.classList.add('border-emerald-500', 'text-emerald-600');
}

function useItem(itemId) {
    if (!confirm('Voulez-vous utiliser cet item ?')) return;
    
    fetch('ajax/use_item.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `item_id=${itemId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Une erreur est survenue');
        }
    });
}

function equipItem(itemId) {
    if (!confirm('Voulez-vous équiper cet item ?')) return;
    
    fetch('ajax/equip_item.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `item_id=${itemId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Une erreur est survenue');
        }
    });
}

function tradeItem(itemId) {
    // Rediriger vers la page d'échange
    window.location.href = `index.php?page=trade&item=${itemId}`;
}
</script>