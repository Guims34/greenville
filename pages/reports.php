<?php
requireAuth();

// Vérifier que l'utilisateur est modérateur ou admin
if (!isModerator()) {
    header('Location: index.php?page=dashboard');
    exit;
}

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

// Filtres
$status = isset($_GET['status']) ? $_GET['status'] : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';

// Construction de la requête
$where_clauses = ['1=1'];
$params = [];

if ($status) {
    $where_clauses[] = 'r.status = ?';
    $params[] = $status;
}

if ($type) {
    $where_clauses[] = 'r.report_type = ?';
    $params[] = $type;
}

$where_sql = implode(' AND ', $where_clauses);

// Récupérer le nombre total de signalements
$stmt = $db->prepare("
    SELECT COUNT(*) 
    FROM reports r
    WHERE $where_sql
");
$stmt->execute($params);
$total_reports = $stmt->fetchColumn();
$total_pages = ceil($total_reports / $per_page);

// Récupérer les signalements
$stmt = $db->prepare("
    SELECT r.*, 
           u1.username as reporter_name,
           u2.username as reported_name
    FROM reports r
    JOIN users u1 ON r.reporter_id = u1.id
    JOIN users u2 ON r.reported_id = u2.id
    WHERE $where_sql
    ORDER BY r.created_at DESC
    LIMIT ? OFFSET ?
");

$params[] = $per_page;
$params[] = $offset;
$stmt->execute($params);
$reports = $stmt->fetchAll();

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $report_id = filter_input(INPUT_POST, 'report_id', FILTER_SANITIZE_NUMBER_INT);
    $action = $_POST['action'];
    $reason = trim($_POST['reason'] ?? '');

    try {
        $db->beginTransaction();

        // Mettre à jour le statut du signalement
        $stmt = $db->prepare("
            UPDATE reports 
            SET status = ?, 
                handled_by = ?,
                handled_at = NOW(),
                mod_comment = ?
            WHERE id = ?
        ");
        $stmt->execute([$action, $_SESSION['user_id'], $reason, $report_id]);

        // Si action punitive, créer un log de modération
        if (in_array($action, ['warn', 'mute', 'ban'])) {
            $stmt = $db->prepare("
                INSERT INTO moderation_logs (
                    mod_id, target_id, action_type, reason
                ) VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['user_id'],
                $_POST['reported_id'],
                $action,
                $reason
            ]);
        }

        $db->commit();
        header('Location: index.php?page=reports&status=' . urlencode($status));
        exit;
    } catch (Exception $e) {
        $db->rollBack();
        $error = "Erreur lors du traitement du signalement";
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Signalements</h2>
        <a href="index.php?page=admin" class="text-gray-600 hover:text-gray-800">
            ← Retour à l'administration
        </a>
    </div>

    <?php if (isset($error)): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <!-- Filtres -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="hidden" name="page" value="reports">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Statut</label>
                <select name="status" class="w-full rounded-md border-gray-300">
                    <option value="">Tous</option>
                    <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>En attente</option>
                    <option value="resolved" <?php echo $status === 'resolved' ? 'selected' : ''; ?>>Résolu</option>
                    <option value="dismissed" <?php echo $status === 'dismissed' ? 'selected' : ''; ?>>Rejeté</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                <select name="type" class="w-full rounded-md border-gray-300">
                    <option value="">Tous</option>
                    <option value="spam" <?php echo $type === 'spam' ? 'selected' : ''; ?>>Spam</option>
                    <option value="harassment" <?php echo $type === 'harassment' ? 'selected' : ''; ?>>Harcèlement</option>
                    <option value="inappropriate" <?php echo $type === 'inappropriate' ? 'selected' : ''; ?>>Contenu inapproprié</option>
                    <option value="other" <?php echo $type === 'other' ? 'selected' : ''; ?>>Autre</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600">
                    Filtrer
                </button>
            </div>
        </form>
    </div>

    <!-- Liste des signalements -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Signalé par</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utilisateur signalé</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Raison</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($reports as $report): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('d/m/Y H:i', strtotime($report['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs rounded-full <?php
                                echo match($report['report_type']) {
                                    'spam' => 'bg-yellow-100 text-yellow-800',
                                    'harassment' => 'bg-red-100 text-red-800',
                                    'inappropriate' => 'bg-orange-100 text-orange-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            ?>">
                                <?php echo ucfirst($report['report_type']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($report['reporter_name']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($report['reported_name']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-900">
                                <?php echo htmlspecialchars($report['reason']); ?>
                            </p>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs rounded-full <?php
                                echo match($report['status']) {
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'resolved' => 'bg-green-100 text-green-800',
                                    'dismissed' => 'bg-gray-100 text-gray-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            ?>">
                                <?php echo ucfirst($report['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <?php if ($report['status'] === 'pending'): ?>
                                <button 
                                    onclick="openActionModal(<?php echo htmlspecialchars(json_encode($report)); ?>)"
                                    class="text-emerald-600 hover:text-emerald-900"
                                >
                                    Traiter
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
                        href="?page=reports&status=<?php echo urlencode($status); ?>&type=<?php echo urlencode($type); ?>&page=<?php echo $i; ?>"
                        class="relative inline-flex items-center px-4 py-2 border <?php echo $i === $page ? 'bg-emerald-50 border-emerald-500 text-emerald-600' : 'border-gray-300 bg-white text-gray-500 hover:bg-gray-50'; ?>"
                    >
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </nav>
        </div>
    <?php endif; ?>
</div>

<!-- Modal d'action -->
<div id="actionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Traiter le signalement</h3>
                <button onclick="closeActionModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" class="space-y-4">
                <input type="hidden" name="report_id" id="report_id">
                <input type="hidden" name="reported_id" id="reported_id">
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Action</label>
                    <select name="action" class="w-full rounded-md border-gray-300" required>
                        <option value="dismissed">Rejeter le signalement</option>
                        <option value="warn">Avertir l'utilisateur</option>
                        <option value="mute">Mute temporaire</option>
                        <option value="ban">Bannissement</option>
                    </select>
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

                <div class="flex justify-end space-x-3">
                    <button 
                        type="button"
                        onclick="closeActionModal()"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                    >
                        Annuler
                    </button>
                    <button 
                        type="submit"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700"
                    >
                        Confirmer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openActionModal(report) {
    document.getElementById('report_id').value = report.id;
    document.getElementById('reported_id').value = report.reported_id;
    document.getElementById('actionModal').classList.remove('hidden');
}

function closeActionModal() {
    document.getElementById('actionModal').classList.add('hidden');
}
</script>