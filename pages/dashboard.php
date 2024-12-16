<?php
requireAuth();

// Récupérer les notifications non lues
$stmt = $db->prepare("
    SELECT * FROM notifications 
    WHERE user_id = ? AND is_read = FALSE 
    ORDER BY created_at DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['user_id']]);
$notifications = $stmt->fetchAll();

// Récupérer les missions actives
$stmt = $db->prepare("
    SELECT m.*, um.progress, um.completed, um.claimed
    FROM daily_missions m
    JOIN user_missions um ON m.id = um.mission_id
    WHERE um.user_id = ? AND um.expires_at > NOW()
    ORDER BY um.completed ASC, m.type ASC
");
$stmt->execute([$_SESSION['user_id']]);
$missions = $stmt->fetchAll();

// Récupérer les statistiques
$stmt = $db->prepare("SELECT * FROM user_stats WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$stats = $stmt->fetch();

// Récupérer les plantes
$stmt = $db->prepare("
    SELECT p.*, s.name as strain_name, s.type as strain_type
    FROM plants p
    JOIN strains s ON p.strain = s.id
    WHERE p.user_id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$plants = $stmt->fetchAll();
?>

<div class="container mx-auto px-4 py-8">
    <!-- Notifications -->
    <?php if (!empty($notifications)): ?>
        <div class="mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold mb-4">Notifications</h2>
                <div class="space-y-4">
                    <?php foreach ($notifications as $notif): ?>
                        <div class="flex items-center gap-4 p-4 rounded-lg <?php
                            echo match($notif['type']) {
                                'success' => 'bg-green-50 text-green-800',
                                'warning' => 'bg-yellow-50 text-yellow-800',
                                'error' => 'bg-red-50 text-red-800',
                                default => 'bg-blue-50 text-blue-800'
                            };
                        ?>">
                            <div class="flex-1">
                                <h3 class="font-medium"><?php echo htmlspecialchars($notif['title']); ?></h3>
                                <p class="text-sm"><?php echo htmlspecialchars($notif['message']); ?></p>
                            </div>
                            <button onclick="markAsRead(<?php echo $notif['id']; ?>)" class="text-sm hover:underline">
                                Marquer comme lu
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Statistiques -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-lg font-semibold mb-4">Statistiques</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <span class="block text-2xl font-bold text-emerald-600">
                            <?php echo number_format($stats['plants_harvested'] ?? 0); ?>
                        </span>
                        <span class="text-gray-500">Plantes récoltées</span>
                    </div>
                    <div class="text-center">
                        <span class="block text-2xl font-bold text-emerald-600">
                            <?php echo number_format($stats['trades_completed'] ?? 0); ?>
                        </span>
                        <span class="text-gray-500">Échanges réalisés</span>
                    </div>
                    <div class="text-center">
                        <span class="block text-2xl font-bold text-emerald-600">
                            <?php echo number_format($stats['missions_completed'] ?? 0); ?>
                        </span>
                        <span class="text-gray-500">Missions complétées</span>
                    </div>
                    <div class="text-center">
                        <span class="block text-2xl font-bold text-emerald-600">
                            <?php echo number_format($stats['achievements_completed'] ?? 0); ?>
                        </span>
                        <span class="text-gray-500">Succès débloqués</span>
                    </div>
                </div>
            </div>

            <!-- Plantes -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold">Mes Plantes</h2>
                    <a href="?page=new_plant" class="text-emerald-600 hover:text-emerald-700">
                        Nouvelle plante
                    </a>
                </div>

                <?php if (empty($plants)): ?>
                    <p class="text-center text-gray-500 py-8">
                        Vous n'avez pas encore de plantes. Commencez à cultiver !
                    </p>
                <?php else: ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <?php foreach ($plants as $plant): ?>
                            <?php
                            $created = new DateTime($plant['created_at']);
                            $now = new DateTime();
                            $age = $created->diff($now)->days;
                            $progress = min(100, ($age / $plant['growth_time']) * 100);
                            ?>
                            <div class="border rounded-lg p-4">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="font-medium"><?php echo htmlspecialchars($plant['name']); ?></h3>
                                        <p class="text-sm text-gray-500">
                                            <?php echo htmlspecialchars($plant['strain_name']); ?>
                                        </p>
                                    </div>
                                    <span class="inline-block px-2 py-1 text-xs rounded-full <?php
                                        echo match($plant['strain_type']) {
                                            'Indica' => 'bg-purple-100 text-purple-800',
                                            'Sativa' => 'bg-yellow-100 text-yellow-800',
                                            'Hybrid' => 'bg-green-100 text-green-800'
                                        };
                                    ?>">
                                        <?php echo $plant['strain_type']; ?>
                                    </span>
                                </div>

                                <div class="space-y-2">
                                    <div>
                                        <div class="flex justify-between text-sm mb-1">
                                            <span>Progression</span>
                                            <span><?php echo number_format($progress, 0); ?>%</span>
                                        </div>
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="bg-emerald-500 h-2 rounded-full" style="width: <?php echo $progress; ?>%"></div>
                                        </div>
                                    </div>

                                    <div class="flex justify-between text-sm">
                                        <span>Santé</span>
                                        <span><?php echo $plant['health']; ?>%</span>
                                    </div>

                                    <div class="flex justify-between text-sm">
                                        <span>Hydratation</span>
                                        <span><?php echo $plant['water_level']; ?>%</span>
                                    </div>
                                </div>

                                <div class="mt-4 flex gap-2">
                                    <button 
                                        onclick="waterPlant(<?php echo $plant['id']; ?>)"
                                        class="flex-1 px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600"
                                    >
                                        Arroser
                                    </button>
                                    <a 
                                        href="?page=plant_details&id=<?php echo $plant['id']; ?>"
                                        class="flex-1 px-3 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 text-center"
                                    >
                                        Détails
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Missions -->
        <div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold">Missions du jour</h2>
                    <a href="?page=missions" class="text-emerald-600 hover:text-emerald-700">
                        Voir tout
                    </a>
                </div>

                <?php if (empty($missions)): ?>
                    <p class="text-center text-gray-500 py-4">
                        Aucune mission disponible
                    </p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($missions as $mission): ?>
                            <div class="border rounded-lg p-4">
                                <div class="flex justify-between items-start mb-2">
                                    <div>
                                        <h3 class="font-medium">
                                            <?php echo htmlspecialchars($mission['title']); ?>
                                        </h3>
                                        <p class="text-sm text-gray-500">
                                            <?php echo str_replace(
                                                '{target}', 
                                                $mission['target_value'], 
                                                htmlspecialchars($mission['description'])
                                            ); ?>
                                        </p>
                                    </div>
                                    <?php if ($mission['completed'] && !$mission['claimed']): ?>
                                        <button 
                                            onclick="claimReward(<?php echo $mission['id']; ?>)"
                                            class="px-2 py-1 bg-emerald-500 text-white text-sm rounded hover:bg-emerald-600"
                                        >
                                            Réclamer
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <div class="mt-2">
                                    <div class="flex justify-between text-sm mb-1">
                                        <span>Progression</span>
                                        <span><?php echo $mission['progress']; ?>/<?php echo $mission['target_value']; ?></span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div 
                                            class="bg-emerald-500 h-2 rounded-full" 
                                            style="width: <?php echo min(100, ($mission['progress'] / $mission['target_value']) * 100); ?>%"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function waterPlant(plantId) {
    fetch('ajax/water_plant.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `plant_id=${plantId}`
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

function markAsRead(notifId) {
    fetch('ajax/mark_notification_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `notification_id=${notifId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}
</script>