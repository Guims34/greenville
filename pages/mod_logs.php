<?php
requireAuth();

// Vérifier que l'utilisateur est modérateur ou admin
if (!isModerator()) {
    header('Location: index.php?page=dashboard');
    exit;
}

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 50;
$offset = ($page - 1) * $per_page;

// Filtres
$type = isset($_GET['type']) ? $_GET['type'] : '';
$user = isset($_GET['user']) ? $_GET['user'] : '';
$date = isset($_GET['date']) ? $_GET['date'] : '';

// Construction de la requête
$where_clauses = ['1=1'];
$params = [];

if ($type) {
    $where_clauses[] = 'ml.action_type = ?';
    $params[] = $type;
}

if ($user) {
    $where_clauses[] = '(u1.username LIKE ? OR u2.username LIKE ?)';
    $params[] = "%$user%";
    $params[] = "%$user%";
}

if ($date) {
    $where_clauses[] = 'DATE(ml.created_at) = ?';
    $params[] = $date;
}

$where_sql = implode(' AND ', $where_clauses);

// Récupérer le nombre total de logs
$stmt = $db->prepare("
    SELECT COUNT(*) 
    FROM moderation_logs ml
    LEFT JOIN users u1 ON ml.mod_id = u1.id
    LEFT JOIN users u2 ON ml.target_id = u2.id
    WHERE $where_sql
");
$stmt->execute($params);
$total_logs = $stmt->fetchColumn();
$total_pages = ceil($total_logs / $per_page);

// Récupérer les logs
$stmt = $db->prepare("
    SELECT ml.*, 
           u1.username as mod_name,
           u2.username as target_name
    FROM moderation_logs ml
    LEFT JOIN users u1 ON ml.mod_id = u1.id
    LEFT JOIN users u2 ON ml.target_id = u2.id
    WHERE $where_sql
    ORDER BY ml.created_at DESC
    LIMIT ? OFFSET ?
");

$params[] = $per_page;
$params[] = $offset;
$stmt->execute($params);
$logs = $stmt->fetchAll();
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Logs de Modération</h2>
        <a href="index.php?page=admin" class="text-gray-600 hover:text-gray-800">
            ← Retour à l'administration
        </a>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="hidden" name="page" value="mod_logs">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Type d'action</label>
                <select name="type" class="w-full rounded-md border-gray-300">
                    <option value="">Tous</option>
                    <option value="warn" <?php echo $type === 'warn' ? 'selected' : ''; ?>>Avertissement</option>
                    <option value="mute" <?php echo $type === 'mute' ? 'selected' : ''; ?>>Mute</option>
                    <option value="ban" <?php echo $type === 'ban' ? 'selected' : ''; ?>>Bannissement</option>
                    <option value="delete" <?php echo $type === 'delete' ? 'selected' : ''; ?>>Suppression</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Utilisateur</label>
                <input 
                    type="text" 
                    name="user" 
                    value="<?php echo htmlspecialchars($user); ?>"
                    placeholder="Nom d'utilisateur"
                    class="w-full rounded-md border-gray-300"
                >
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                <input 
                    type="date" 
                    name="date" 
                    value="<?php echo $date; ?>"
                    class="w-full rounded-md border-gray-300"
                >
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600">
                    Filtrer
                </button>
            </div>
        </form>
    </div>

    <!-- Liste des logs -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Modérateur</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisateur</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Raison</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($logs as $log): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('d/m/Y H:i', strtotime($log['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($log['mod_name']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs rounded-full <?php
                                echo match($log['action_type']) {
                                    'warn' => 'bg-yellow-100 text-yellow-800',
                                    'mute' => 'bg-blue-100 text-blue-800',
                                    'ban' => 'bg-red-100 text-red-800',
                                    'delete' => 'bg-gray-100 text-gray-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            ?>">
                                <?php echo ucfirst($log['action_type']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900">
                                <?php echo htmlspecialchars($log['target_name']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-900">
                                <?php echo htmlspecialchars($log['reason']); ?>
                            </p>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($total_pages > 1): ?>
        <div class="mt-6 flex justify-center">
            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a 
                        href="?page=mod_logs&type=<?php echo urlencode($type); ?>&user=<?php echo urlencode($user); ?>&date=<?php echo urlencode($date); ?>&page=<?php echo $i; ?>"
                        class="relative inline-flex items-center px-4 py-2 border <?php echo $i === $page ? 'bg-emerald-50 border-emerald-500 text-emerald-600' : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50'; ?>"
                    >
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>
