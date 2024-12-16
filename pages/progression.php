<?php
requireAuth();

// Récupérer les informations de niveau
$stmt = $db->prepare("
    SELECT u.*, l.level as current_level
    FROM users u 
    LEFT JOIN levels l ON l.xp_required <= u.experience
    WHERE u.id = ?
    ORDER BY l.level DESC
    LIMIT 1
");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Calculer la progression
$stmt = $db->prepare("
    SELECT xp_required as next_level_xp
    FROM levels 
    WHERE level = ? + 1
");
$stmt->execute([$user['current_level']]);
$nextLevel = $stmt->fetch();

$progress = [
    'xp_progress' => $user['experience'] - ($user['current_level_xp'] ?? 0),
    'xp_needed' => ($nextLevel['next_level_xp'] ?? 100) - ($user['current_level_xp'] ?? 0),
    'progress_percent' => 0
];

if ($progress['xp_needed'] > 0) {
    $progress['progress_percent'] = min(100, ($progress['xp_progress'] / $progress['xp_needed']) * 100);
}

// Récupérer les succès
$stmt = $db->prepare("
    SELECT a.*, ua.progress, ua.completed, ua.completed_at
    FROM achievements a
    LEFT JOIN user_achievements ua ON a.id = ua.achievement_id AND ua.user_id = ?
    ORDER BY a.category, a.difficulty
");
$stmt->execute([$_SESSION['user_id']]);
$achievements = $stmt->fetchAll();

// Récupérer les statistiques
$stmt = $db->prepare("SELECT * FROM user_stats WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Niveau et progression -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold">Niveau <?php echo $user['current_level'] ?? 1; ?></h2>
                <div class="text-sm text-gray-500">
                    <?php echo number_format($user['experience'] ?? 0); ?> XP total
                </div>
            </div>
            
            <div class="mb-4">
                <div class="flex justify-between text-sm text-gray-600 mb-1">
                    <span>Progression vers niveau <?php echo ($user['current_level'] ?? 1) + 1; ?></span>
                    <span>
                        <?php echo number_format($progress['xp_progress']); ?>/<?php echo number_format($progress['xp_needed']); ?> XP
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div 
                        class="bg-emerald-500 h-2 rounded-full" 
                        style="width: <?php echo max(0, min(100, $progress['progress_percent'])); ?>%"
                    ></div>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                <div>
                    <span class="block text-2xl font-bold text-emerald-600">
                        <?php echo number_format($stats['plants_harvested']); ?>
                    </span>
                    <span class="text-gray-500">Plantes récoltées</span>
                </div>
                <div>
                    <span class="block text-2xl font-bold text-emerald-600">
                        <?php echo number_format($stats['trades_completed']); ?>
                    </span>
                    <span class="text-gray-500">Échanges réalisés</span>
                </div>
                <div>
                    <span class="block text-2xl font-bold text-emerald-600">
                        <?php echo number_format($stats['missions_completed']); ?>
                    </span>
                    <span class="text-gray-500">Missions complétées</span>
                </div>
                <div>
                    <span class="block text-2xl font-bold text-emerald-600">
                        <?php echo number_format($stats['achievements_completed']); ?>
                    </span>
                    <span class="text-gray-500">Succès débloqués</span>
                </div>
            </div>
        </div>

        <!-- Succès -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-6">Succès</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($achievements as $achievement): ?>
                    <div class="border rounded-lg p-4 <?php echo $achievement['completed'] ? 'bg-emerald-50 border-emerald-200' : ''; ?>">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl"><?php echo $achievement['icon']; ?></span>
                                    <h3 class="font-semibold">
                                        <?php echo htmlspecialchars($achievement['title']); ?>
                                    </h3>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">
                                    <?php echo htmlspecialchars($achievement['description']); ?>
                                </p>
                            </div>
                            <span class="inline-block px-2 py-1 text-xs rounded-full <?php
                                echo match($achievement['difficulty']) {
                                    'easy' => 'bg-green-100 text-green-800',
                                    'medium' => 'bg-yellow-100 text-yellow-800',
                                    'hard' => 'bg-orange-100 text-orange-800',
                                    'legendary' => 'bg-purple-100 text-purple-800'
                                };
                            ?>">
                                <?php echo ucfirst($achievement['difficulty']); ?>
                            </span>
                        </div>

                        <?php if ($achievement['completed']): ?>
                            <div class="flex items-center justify-between text-sm text-emerald-600">
                                <span>Complété !</span>
                                <span><?php echo date('d/m/Y', strtotime($achievement['completed_at'])); ?></span>
                            </div>
                        <?php else: ?>
                            <div class="mt-2">
                                <div class="flex justify-between text-sm text-gray-600 mb-1">
                                    <span>Progression</span>
                                    <span><?php echo number_format($achievement['progress'] ?? 0); ?>%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div 
                                        class="bg-emerald-500 h-2 rounded-full" 
                                        style="width: <?php echo max(0, min(100, $achievement['progress'] ?? 0)); ?>%"
                                    ></div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="mt-2 text-sm text-gray-500">
                            Récompenses:
                            <div class="flex items-center gap-4">
                                <span><?php echo number_format($achievement['xp_reward']); ?> XP</span>
                                <span><?php echo number_format($achievement['coins_reward']); ?> 🪙</span>
                                <?php if ($achievement['premium_coins_reward']): ?>
                                    <span><?php echo number_format($achievement['premium_coins_reward']); ?> 💎</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>