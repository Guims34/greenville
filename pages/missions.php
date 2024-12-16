<?php
requireAuth();

require_once 'includes/missions_helper.php';

// Générer de nouvelles missions si nécessaire
generateDailyMissions($db, $_SESSION['user_id']);

// Récupérer les missions actives
$missions = getUserMissions($db, $_SESSION['user_id']);

// Récupérer les statistiques de l'utilisateur
$stmt = $db->prepare("SELECT * FROM user_stats WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-2xl font-bold">Missions Quotidiennes</h1>
        <div class="text-sm text-gray-500">
            Prochaine réinitialisation dans : <span id="reset-timer"></span>
        </div>
    </div>

    <?php if (empty($missions)): ?>
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <p class="text-gray-500">Aucune mission disponible pour le moment.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($missions as $mission): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6">
                        <!-- En-tête de la mission -->
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="font-semibold text-lg">
                                    <?php echo htmlspecialchars($mission['title']); ?>
                                </h3>
                                <span class="inline-block px-2 py-1 text-xs rounded-full <?php
                                    echo match($mission['type']) {
                                        'cultivation' => 'bg-green-100 text-green-800',
                                        'trading' => 'bg-blue-100 text-blue-800',
                                        'social' => 'bg-purple-100 text-purple-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                ?>">
                                    <?php echo ucfirst($mission['type']); ?>
                                </span>
                            </div>
                            <?php if ($mission['completed']): ?>
                                <span class="text-emerald-500">✓ Complété</span>
                            <?php endif; ?>
                        </div>

                        <!-- Description et objectif -->
                        <p class="text-gray-600 text-sm mb-4">
                            <?php 
                            $description = str_replace(
                                '{target}', 
                                $mission['target_value'], 
                                htmlspecialchars($mission['description'])
                            );
                            echo $description;
                            ?>
                        </p>

                        <!-- Barre de progression -->
                        <div class="mb-4">
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>Progression</span>
                                <span><?php echo $mission['progress']; ?>/<?php echo $mission['target_value']; ?></span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div 
                                    class="bg-emerald-500 h-2 rounded-full transition-all duration-500" 
                                    style="width: <?php echo min(100, ($mission['progress'] / $mission['target_value']) * 100); ?>%"
                                ></div>
                            </div>
                        </div>

                        <!-- Récompenses -->
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-1">
                                    <span class="text-yellow-500">🪙</span>
                                    <span class="font-medium"><?php echo number_format($mission['coins_reward']); ?></span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <span class="text-purple-500">✨</span>
                                    <span class="font-medium"><?php echo number_format($mission['xp_reward']); ?> XP</span>
                                </div>
                            </div>
                            <?php if ($mission['completed']): ?>
                                <button 
                                    onclick="claimReward(<?php echo $mission['id']; ?>)"
                                    class="px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 disabled:opacity-50"
                                    <?php echo $mission['claimed'] ? 'disabled' : ''; ?>
                                >
                                    <?php echo $mission['claimed'] ? 'Réclamé' : 'Réclamer'; ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Statistiques des missions -->
    <div class="mt-8 bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold mb-4">Statistiques</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <span class="text-gray-500">Missions complétées aujourd'hui</span>
                <p class="text-2xl font-bold"><?php echo $stats['missions_completed_today'] ?? 0; ?></p>
            </div>
            <div>
                <span class="text-gray-500">Missions complétées cette semaine</span>
                <p class="text-2xl font-bold"><?php echo $stats['missions_completed_week'] ?? 0; ?></p>
            </div>
            <div>
                <span class="text-gray-500">Total des missions complétées</span>
                <p class="text-2xl font-bold"><?php echo $stats['missions_completed'] ?? 0; ?></p>
            </div>
        </div>
    </div>
</div>

<script>
// Mise à jour du timer de réinitialisation
function updateResetTimer() {
    const now = new Date();
    const tomorrow = new Date(now);
    tomorrow.setDate(tomorrow.getDate() + 1);
    tomorrow.setHours(0, 0, 0, 0);
    
    const diff = tomorrow - now;
    const hours = Math.floor(diff / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    
    document.getElementById('reset-timer').textContent = 
        `${hours}h ${minutes}m`;
}

// Réclamer une récompense
function claimReward(missionId) {
    fetch('ajax/claim_mission.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `mission_id=${missionId}`
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

// Mettre à jour le timer toutes les minutes
updateResetTimer();
setInterval(updateResetTimer, 60000);

// Vérifier les missions toutes les 5 minutes
setInterval(() => {
    fetch('ajax/check_missions.php')
        .then(response => response.json())
        .then(data => {
            if (data.refresh) {
                location.reload();
            }
        });
}, 300000);
</script>