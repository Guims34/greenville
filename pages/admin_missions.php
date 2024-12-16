<?php
requireAuth();

if (!isAdmin()) {
    header('Location: index.php?page=dashboard');
    exit;
}

// Traitement de l'ajout/modification de mission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['add_mission'])) {
            // Validation des données
            $title = sanitizeString($_POST['title']);
            $description = sanitizeString($_POST['description']);
            $type = sanitizeString($_POST['type']);
            $target_value = filter_input(INPUT_POST, 'target_value', FILTER_SANITIZE_NUMBER_INT);
            $xp_reward = filter_input(INPUT_POST, 'xp_reward', FILTER_SANITIZE_NUMBER_INT);
            $coins_reward = filter_input(INPUT_POST, 'coins_reward', FILTER_SANITIZE_NUMBER_INT);

            if (empty($title) || empty($description) || empty($type) || !$target_value || !$xp_reward || !$coins_reward) {
                throw new Exception("Tous les champs sont requis");
            }

            // Insertion de la nouvelle mission
            $stmt = $db->prepare("
                INSERT INTO daily_missions (title, description, type, target_value, xp_reward, coins_reward)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$title, $description, $type, $target_value, $xp_reward, $coins_reward]);

            $_SESSION['success_message'] = "Mission ajoutée avec succès";
            header('Location: index.php?page=admin_missions');
            exit;
        }

        if (isset($_POST['edit_mission'])) {
            $mission_id = filter_input(INPUT_POST, 'mission_id', FILTER_SANITIZE_NUMBER_INT);
            $title = sanitizeString($_POST['title']);
            $description = sanitizeString($_POST['description']);
            $type = sanitizeString($_POST['type']);
            $target_value = filter_input(INPUT_POST, 'target_value', FILTER_SANITIZE_NUMBER_INT);
            $xp_reward = filter_input(INPUT_POST, 'xp_reward', FILTER_SANITIZE_NUMBER_INT);
            $coins_reward = filter_input(INPUT_POST, 'coins_reward', FILTER_SANITIZE_NUMBER_INT);

            if (!$mission_id || empty($title) || empty($description) || empty($type) || !$target_value || !$xp_reward || !$coins_reward) {
                throw new Exception("Tous les champs sont requis");
            }

            // Mise à jour de la mission
            $stmt = $db->prepare("
                UPDATE daily_missions 
                SET title = ?, description = ?, type = ?, target_value = ?, xp_reward = ?, coins_reward = ?
                WHERE id = ?
            ");
            $stmt->execute([$title, $description, $type, $target_value, $xp_reward, $coins_reward, $mission_id]);

            $_SESSION['success_message'] = "Mission mise à jour avec succès";
            header('Location: index.php?page=admin_missions');
            exit;
        }

        if (isset($_POST['delete_mission'])) {
            $mission_id = filter_input(INPUT_POST, 'mission_id', FILTER_SANITIZE_NUMBER_INT);
            
            if (!$mission_id) {
                throw new Exception("ID de mission invalide");
            }

            // Suppression de la mission
            $stmt = $db->prepare("DELETE FROM daily_missions WHERE id = ?");
            $stmt->execute([$mission_id]);

            $_SESSION['success_message'] = "Mission supprimée avec succès";
            header('Location: index.php?page=admin_missions');
            exit;
        }

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Récupération des missions
$stmt = $db->query("SELECT * FROM daily_missions ORDER BY type, title");
$missions = $stmt->fetchAll();
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Gestion des Missions</h2>
        <a href="index.php?page=admin" class="text-gray-600 hover:text-gray-800">
            ← Retour à l'administration
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

    <!-- Formulaire d'ajout de mission -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h3 class="text-lg font-semibold mb-4">Ajouter une mission</h3>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="add_mission" value="1">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Titre</label>
                    <input 
                        type="text" 
                        name="title" 
                        required
                        class="w-full rounded-md border-gray-300"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                    <select name="type" required class="w-full rounded-md border-gray-300">
                        <option value="cultivation">Culture</option>
                        <option value="trading">Échange</option>
                        <option value="social">Social</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <input 
                        type="text" 
                        name="description" 
                        required
                        placeholder="Utilisez {target} pour la valeur cible"
                        class="w-full rounded-md border-gray-300"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Valeur cible</label>
                    <input 
                        type="number" 
                        name="target_value" 
                        required
                        min="1"
                        class="w-full rounded-md border-gray-300"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Récompense XP</label>
                    <input 
                        type="number" 
                        name="xp_reward" 
                        required
                        min="1"
                        class="w-full rounded-md border-gray-300"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Récompense Pièces</label>
                    <input 
                        type="number" 
                        name="coins_reward" 
                        required
                        min="1"
                        class="w-full rounded-md border-gray-300"
                    >
                </div>
            </div>

            <div class="flex justify-end">
                <button 
                    type="submit"
                    class="px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600"
                >
                    Ajouter la mission
                </button>
            </div>
        </form>
    </div>

    <!-- Liste des missions -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Titre</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Objectif</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Récompenses</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($missions as $mission): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($mission['title']); ?>
                            </div>
                            <div class="text-sm text-gray-500">
                                <?php echo htmlspecialchars($mission['description']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs rounded-full <?php
                                echo match($mission['type']) {
                                    'cultivation' => 'bg-green-100 text-green-800',
                                    'trading' => 'bg-blue-100 text-blue-800',
                                    'social' => 'bg-purple-100 text-purple-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            ?>">
                                <?php echo ucfirst($mission['type']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo $mission['target_value']; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                <?php echo number_format($mission['xp_reward']); ?> XP
                            </div>
                            <div class="text-sm text-gray-500">
                                <?php echo number_format($mission['coins_reward']); ?> 🪙
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button 
                                onclick="editMission(<?php echo htmlspecialchars(json_encode($mission)); ?>)"
                                class="text-emerald-600 hover:text-emerald-900 mr-3"
                            >
                                Modifier
                            </button>
                            <form method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette mission ?');">
                                <input type="hidden" name="delete_mission" value="1">
                                <input type="hidden" name="mission_id" value="<?php echo $mission['id']; ?>">
                                <button 
                                    type="submit"
                                    class="text-red-600 hover:text-red-900"
                                >
                                    Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de modification -->
<div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Modifier la mission</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" class="space-y-4">
                <input type="hidden" name="edit_mission" value="1">
                <input type="hidden" name="mission_id" id="edit_mission_id">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Titre</label>
                    <input 
                        type="text" 
                        name="title" 
                        id="edit_title"
                        required
                        class="w-full rounded-md border-gray-300"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                    <select name="type" id="edit_type" required class="w-full rounded-md border-gray-300">
                        <option value="cultivation">Culture</option>
                        <option value="trading">Échange</option>
                        <option value="social">Social</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                    <input 
                        type="text" 
                        name="description" 
                        id="edit_description"
                        required
                        class="w-full rounded-md border-gray-300"
                    >
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Valeur cible</label>
                        <input 
                            type="number" 
                            name="target_value" 
                            id="edit_target_value"
                            required
                            min="1"
                            class="w-full rounded-md border-gray-300"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Récompense XP</label>
                        <input 
                            type="number" 
                            name="xp_reward" 
                            id="edit_xp_reward"
                            required
                            min="1"
                            class="w-full rounded-md border-gray-300"
                        >
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Récompense Pièces</label>
                        <input 
                            type="number" 
                            name="coins_reward" 
                            id="edit_coins_reward"
                            required
                            min="1"
                            class="w-full rounded-md border-gray-300"
                        >
                    </div>
                </div>

                <div class="flex justify-end space-x-3">
                    <button 
                        type="button"
                        onclick="closeEditModal()"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                    >
                        Annuler
                    </button>
                    <button 
                        type="submit"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700"
                    >
                        Sauvegarder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editMission(mission) {
    document.getElementById('edit_mission_id').value = mission.id;
    document.getElementById('edit_title').value = mission.title;
    document.getElementById('edit_type').value = mission.type;
    document.getElementById('edit_description').value = mission.description;
    document.getElementById('edit_target_value').value = mission.target_value;
    document.getElementById('edit_xp_reward').value = mission.xp_reward;
    document.getElementById('edit_coins_reward').value = mission.coins_reward;
    
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>