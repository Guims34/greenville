<?php
requireAuth();

// Récupérer le classement global
$stmt = $db->prepare("
    SELECT u.id, u.username, u.level, u.experience,
           (SELECT COUNT(*) + 1 FROM users u2 WHERE u2.experience > u.experience) as rank
    FROM users u
    ORDER BY u.experience DESC
    LIMIT 100
");
$stmt->execute();
$rankings = $stmt->fetchAll();

// Récupérer le rang du joueur actuel
$stmt = $db->prepare("
    SELECT 
        (SELECT COUNT(*) + 1 FROM users u2 WHERE u2.experience > u.experience) as rank,
        u.experience,
        u.level
    FROM users u 
    WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$player_rank = $stmt->fetch();
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- En-tête avec le rang du joueur -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-2xl font-bold mb-4">Votre Classement</h2>
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <span class="block text-3xl font-bold text-emerald-600">
                        #<?php echo number_format($player_rank['rank']); ?>
                    </span>
                    <span class="text-gray-500">Position</span>
                </div>
                <div>
                    <span class="block text-3xl font-bold text-emerald-600">
                        <?php echo number_format($player_rank['experience']); ?>
                    </span>
                    <span class="text-gray-500">XP Total</span>
                </div>
                <div>
                    <span class="block text-3xl font-bold text-emerald-600">
                        <?php echo $player_rank['level']; ?>
                    </span>
                    <span class="text-gray-500">Niveau</span>
                </div>
            </div>
        </div>

        <!-- Tableau de classement -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="p-6 border-b">
                <h2 class="text-xl font-bold">Classement Global</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Position
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Joueur
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Niveau
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                XP
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($rankings as $index => $player): ?>
                            <tr class="<?php echo $player['id'] === $_SESSION['user_id'] ? 'bg-emerald-50' : ''; ?>">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm <?php
                                        echo match($index) {
                                            0 => 'text-yellow-500 font-bold',
                                            1 => 'text-gray-400 font-bold',
                                            2 => 'text-amber-600 font-bold',
                                            default => 'text-gray-900'
                                        };
                                    ?>">
                                        #<?php echo $player['rank']; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?php echo htmlspecialchars($player['username']); ?>
                                        <?php if ($player['id'] === $_SESSION['user_id']): ?>
                                            <span class="text-xs text-emerald-600 ml-2">(Vous)</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php echo $player['level']; ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <?php echo number_format($player['experience']); ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>