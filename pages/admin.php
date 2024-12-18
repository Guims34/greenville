<?php
// Vérifier l'authentification avant tout output
if (!isAdmin()) {
    $_SESSION['error_message'] = "Accès non autorisé";
    header('Location: index.php');
    exit;
}

// Définir la constante pour les actions admin
define('ADMIN_ACTIONS', true);

// Traiter les actions POST avant tout output
$redirect_url = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'includes/admin/actions.php';
    $redirect_url = handleAdminActions();
}

// Si une redirection est nécessaire, l'effectuer maintenant
if ($redirect_url) {
    header("Location: $redirect_url");
    exit;
}

// Statistiques globales
$stats = $db->query("
    SELECT 
        (SELECT COUNT(*) FROM users WHERE email != 'admin@greenville.com') as total_users,
        (SELECT COUNT(*) FROM users WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)) as new_users_24h,
        (SELECT COUNT(*) FROM plants) as total_plants,
        (SELECT COUNT(*) FROM trades WHERE status = 'completed') as total_trades,
        (SELECT COUNT(*) FROM guilds) as total_guilds
")->fetch();

// Récupérer la liste des utilisateurs
$stmt = $db->query("
    SELECT u.*, 
           (SELECT COUNT(*) FROM plants WHERE user_id = u.id) as plants_count,
           (SELECT COUNT(*) FROM trades WHERE sender_id = u.id OR receiver_id = u.id) as trades_count
    FROM users u 
    WHERE u.email != 'admin@greenville.com'
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();

// Définir le titre de la page
$page_title = "Administration";

// Inclure le header
require_once 'includes/header.php';
?>


<div class="container mx-auto px-4 py-8">
    <!-- Dashboard d'administration -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold mb-6">Dashboard d'Administration</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6">
            <!-- Statistiques globales -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="font-semibold text-gray-600 mb-2">Utilisateurs</h3>
                <p class="text-2xl font-bold"><?php echo number_format($stats['total_users']); ?></p>
                <p class="text-sm text-gray-500">+<?php echo $stats['new_users_24h']; ?> en 24h</p>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="font-semibold text-gray-600 mb-2">Plantes</h3>
                <p class="text-2xl font-bold"><?php echo number_format($stats['total_plants']); ?></p>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="font-semibold text-gray-600 mb-2">Échanges</h3>
                <p class="text-2xl font-bold"><?php echo number_format($stats['total_trades']); ?></p>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="font-semibold text-gray-600 mb-2">Guildes</h3>
                <p class="text-2xl font-bold"><?php echo number_format($stats['total_guilds']); ?></p>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="font-semibold text-gray-600 mb-2">Actions rapides</h3>
                <div class="space-y-2">
                    <a href="?page=admin_plants" class="block text-sm text-emerald-600 hover:text-emerald-700">
                        → Gérer les plantes
                    </a>
                    <a href="?page=admin_missions" class="block text-sm text-emerald-600 hover:text-emerald-700">
                        → Gérer les missions
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des utilisateurs -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-6 border-b">
            <h2 class="text-xl font-bold">Gestion des Utilisateurs</h2>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
                <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
                <?php 
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Utilisateur
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Statistiques
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Activité
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($users as $user): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            <?php echo htmlspecialchars($user['username']); ?>
                                            <?php if ($user['status'] === 'suspended'): ?>
                                                <span class="text-xs text-red-600 ml-2">(Suspendu)</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($user['email']); ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm">
                                    <div>Niveau <?php echo $user['level']; ?></div>
                                    <div><?php echo number_format($user['coins']); ?> 🪙</div>
                                    <div><?php echo number_format($user['premium_coins']); ?> 💎</div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm">
                                    <div><?php echo $user['plants_count']; ?> plantes</div>
                                    <div><?php echo $user['trades_count']; ?> échanges</div>
                                    <div class="text-gray-500">
                                        Inscrit le <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <div class="space-y-2">
                                    <!-- Actions principales -->
                                    <div class="flex justify-end space-x-2">
    <?php if ($user['status'] === 'active'): ?>
        <button 
            onclick="openSuspendModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')"
            class="text-yellow-600 hover:text-yellow-900"
        >
            Suspendre
        </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="activate">
                                                <button type="submit" class="text-green-600 hover:text-green-900">
                                                    Réactiver
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <form method="POST" class="inline" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="text-red-600 hover:text-red-900">
                                                Supprimer
                                            </button>
                                        </form>
                                    </div>

                                    <!-- Actions avancées -->
                                    <div class="flex justify-end gap-2">
                                        <button 
                                            onclick="openAddCoinsModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')"
                                            class="text-emerald-600 hover:text-emerald-700"
                                        >
                                            Ajouter 🪙
                                        </button>
                                        <button 
                                            onclick="openAddPremiumModal(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['username']); ?>')"
                                            class="text-purple-600 hover:text-purple-700"
                                        >
                                            Ajouter 💎
                                        </button>
                                    </div>

                                    <!-- Gestion des rôles -->
                                    <div class="flex justify-end">
                                        <?php if ($user['user_role'] === 'moderator'): ?>
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="remove_mod">
                                                <button type="submit" class="text-blue-600 hover:text-blue-900">
                                                    Retirer Mod
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="promote_mod">
                                                <button type="submit" class="text-blue-600 hover:text-blue-900">
                                                    Promouvoir Mod
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal d'ajout de pièces -->
<div id="addCoinsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Ajouter des pièces à <span id="coinsUsername"></span></h3>
                <button onclick="closeAddCoinsModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add_coins">
                <input type="hidden" name="user_id" id="coinsUserId">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Montant</label>
                    <input 
                        type="number" 
                        name="amount"
                        required
                        min="1"
                        class="mt-1 block w-full rounded-md border-gray-300"
                    >
                </div>

                <div class="flex justify-end space-x-3">
                    <button 
                        type="button"
                        onclick="closeAddCoinsModal()"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                    >
                        Annuler
                    </button>
                    <button 
                        type="submit"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700"
                    >
                        Ajouter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal d'ajout de pièces premium -->
<div id="addPremiumModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Ajouter des pièces premium à <span id="premiumUsername"></span></h3>
                <button onclick="closeAddPremiumModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="add_premium">
                <input type="hidden" name="user_id" id="premiumUserId">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700">Montant</label>
                    <input 
                        type="number" 
                        name="amount"
                        required
                        min="1"
                        class="mt-1 block w-full rounded-md border-gray-300"
                    >
                </div>

                <div class="flex justify-end space-x-3">
                    <button 
                        type="button"
                        onclick="closeAddPremiumModal()"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                    >
                        Annuler
                    </button>
                    <button 
                        type="submit"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700"
                    >
                        Ajouter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="suspendModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Suspendre <span id="suspendUsername"></span></h3>
                <button onclick="closeSuspendModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="suspend">
                <input type="hidden" name="user_id" id="suspendUserId">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Type de suspension</label>
                    <select name="suspension_type" id="suspensionType" class="w-full rounded-md border-gray-300" onchange="toggleDurationField()">
                        <option value="temporary">Temporaire</option>
                        <option value="permanent">Permanente</option>
                    </select>
                </div>

                <div id="durationField">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Durée (jours)</label>
                    <input 
                        type="number" 
                        name="duration" 
                        min="1" 
                        value="1"
                        class="w-full rounded-md border-gray-300"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Raison</label>
                    <textarea 
                        name="reason"
                        rows="3"
                        required
                        class="w-full rounded-md border-gray-300"
                    ></textarea>
                </div>

                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        name="ban_ip" 
                        id="banIp"
                        class="rounded border-gray-300 text-emerald-600"
                    >
                    <label for="banIp" class="ml-2 text-sm text-gray-700">
                        Bloquer également l'adresse IP
                    </label>
                </div>

                <div class="flex justify-end space-x-3">
                    <button 
                        type="button"
                        onclick="closeSuspendModal()"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                    >
                        Annuler
                    </button>
                    <button 
                        type="submit"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700"
                    >
                        Suspendre
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openSuspendModal(userId, username) {
    document.getElementById('suspendUserId').value = userId;
    document.getElementById('suspendUsername').textContent = username;
    document.getElementById('suspendModal').classList.remove('hidden');
}

function closeSuspendModal() {
    document.getElementById('suspendModal').classList.add('hidden');
}

function toggleDurationField() {
    const type = document.getElementById('suspensionType').value;
    const durationField = document.getElementById('durationField');
    durationField.style.display = type === 'temporary' ? 'block' : 'none';
}
</script>
<script>
function openAddCoinsModal(userId, username) {
    document.getElementById('coinsUserId').value = userId;
    document.getElementById('coinsUsername').textContent = username;
    document.getElementById('addCoinsModal').classList.remove('hidden');
}

function closeAddCoinsModal() {
    document.getElementById('addCoinsModal').classList.add('hidden');
}

function openAddPremiumModal(userId, username) {
    document.getElementById('premiumUserId').value = userId;
    document.getElementById('premiumUsername').textContent = username;
    document.getElementById('addPremiumModal').classList.remove('hidden');
}

function closeAddPremiumModal() {
    document.getElementById('addPremiumModal').classList.add('hidden');
}
</script>