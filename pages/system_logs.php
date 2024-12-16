<?php
requireAuth();

// Vérifier que l'utilisateur est admin
if (!isAdmin()) {
    header('Location: index.php?page=dashboard');
    exit;
}

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 50;
$offset = ($page - 1) * $per_page;

// Filtres
$level = isset($_GET['level']) ? $_GET['level'] : '';
$component = isset($_GET['component']) ? $_GET['component'] : '';
$date = isset($_GET['date']) ? $_GET['date'] : '';

// Construction de la requête
$where_clauses = ['1=1'];
$params = [];

if ($level) {
    $where_clauses[] = 'level = ?';
    $params[] = $level;
}

if ($component) {
    $where_clauses[] = 'component = ?';
    $params[] = $component;
}

if ($date) {
    $where_clauses[] = 'DATE(created_at) = ?';
    $params[] = $date;
}

$where_sql = implode(' AND ', $where_clauses);

// Récupérer le nombre total de logs
$stmt = $db->prepare("
    SELECT COUNT(*) 
    FROM system_logs
    WHERE $where_sql
");
$stmt->execute($params);
$total_logs = $stmt->fetchColumn();
$total_pages = ceil($total_logs / $per_page);

// Récupérer les logs
$stmt = $db->prepare("
    SELECT * 
    FROM system_logs
    WHERE $where_sql
    ORDER BY created_at DESC
    LIMIT ? OFFSET ?
");

$params[] = $per_page;
$params[] = $offset;
$stmt->execute($params);
$logs = $stmt->fetchAll();

// Récupérer les composants uniques pour le filtre
$stmt = $db->query("SELECT DISTINCT component FROM system_logs ORDER BY component");
$components = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Logs Système</h2>
        <a href="index.php?page=admin" class="text-gray-600 hover:text-gray-800">
            ← Retour à l'administration
        </a>
    </div>

    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <input type="hidden" name="page" value="system_logs">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Niveau</label>
                <select name="level" class="w-full rounded-md border-gray-300">
                    <option value="">Tous</option>
                    <option value="info" <?php echo $level === 'info' ? 'selected' : ''; ?>>Info</option>
                    <option value="warning" <?php echo $level === 'warning' ? 'selected' : ''; ?>>Warning</option>
                    <option value="error" <?php echo $level === 'error' ? 'selected' : ''; ?>>Error</option>
                    <option value="critical" <?php echo $level === 'critical' ? 'selected' : ''; ?>>Critical</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Composant</label>
                <select name="component" class="w-full rounded-md border-gray-300">
                    <option value="">Tous</option>
                    <?php foreach ($components as $comp): ?>
                        <option value="<?php echo htmlspecialchars($comp); ?>" <?php echo $component === $comp ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($comp); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
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
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Niveau</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Composant</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Détails</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($logs as $log): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs rounded-full <?php
                                echo match($log['level']) {
                                    'info' => 'bg-blue-100 text-blue-800',
                                    'warning' => 'bg-yellow-100 text-yellow-800',
                                    'error' => 'bg-red-100 text-red-800',
                                    'critical' => 'bg-purple-100 text-purple-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            ?>">
                                <?php echo ucfirst($log['level']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($log['component']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-900">
                                <?php echo htmlspecialchars($log['message']); ?>
                            </p>
                        </td>
                        <td class="px-6 py-4">
                            <?php if ($log['details']): ?>
                                <button 
                                    onclick="showDetails(<?php echo htmlspecialchars(json_encode($log['details'])); ?>)"
                                    class="text-emerald-600 hover:text-emerald-900 text-sm"
                                >
                                    Voir détails
                                </button>
                            <?php endif; ?>
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
                        href="?page=system_logs&level=<?php echo urlencode($level); ?>&component=<?php echo urlencode($component); ?>&date=<?php echo urlencode($date); ?>&page=<?php echo $i; ?>"
                        class="relative inline-flex items-center px-4 py-2 border <?php echo $i === $page ? 'bg-emerald-50 border-emerald-500 text-emerald-600' : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50'; ?>"
                    >
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

<!-- Modal des détails -->
<div id="detailsModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Détails du log</h3>
                <button onclick="closeDetailsModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="detailsContent" class="overflow-x-auto">
                <pre class="text-sm bg-gray-50 p-4 rounded-lg"></pre>
            </div>
            <div class="mt-4 flex justify-end">
                <button 
                    onclick="closeDetailsModal()"
                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
                >
                    Fermer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function showDetails(details) {
    const modal = document.getElementById('detailsModal');
    const content = document.getElementById('detailsContent').querySelector('pre');
    
    content.textContent = JSON.stringify(details, null, 2);
    modal.classList.remove('hidden');
}

function closeDetailsModal() {
    document.getElementById('detailsModal').classList.add('hidden');
}
</script>