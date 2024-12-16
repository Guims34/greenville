<?php
requireAuth();

$guild_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (!$guild_id) {
    header('Location: index.php?page=guilds');
    exit;
}

// Récupérer les informations de la guilde
$stmt = $db->prepare("
    SELECT g.*, 
           COUNT(gm.id) as member_count,
           u.username as leader_name
    FROM guilds g
    LEFT JOIN guild_members gm ON g.id = gm.guild_id
    JOIN users u ON g.leader_id = u.id
    WHERE g.id = ?
    GROUP BY g.id
");
$stmt->execute([$guild_id]);
$guild = $stmt->fetch();

if (!$guild) {
    header('Location: index.php?page=guilds');
    exit;
}

// Récupérer le rôle de l'utilisateur dans la guilde
$stmt = $db->prepare("
    SELECT role 
    FROM guild_members 
    WHERE guild_id = ? AND user_id = ?
");
$stmt->execute([$guild_id, $_SESSION['user_id']]);
$member_role = $stmt->fetchColumn();

// Récupérer les membres
$stmt = $db->prepare("
    SELECT u.id, u.username, u.level, gm.role, gm.contribution_points,
           u.last_activity
    FROM guild_members gm
    JOIN users u ON gm.user_id = u.id
    WHERE gm.guild_id = ?
    ORDER BY gm.role DESC, gm.contribution_points DESC
");
$stmt->execute([$guild_id]);
$members = $stmt->fetchAll();

// Récupérer les événements en cours
$stmt = $db->prepare("
    SELECT * 
    FROM guild_events 
    WHERE guild_id = ? 
    AND end_date > NOW()
    ORDER BY start_date ASC
");
$stmt->execute([$guild_id]);
$events = $stmt->fetchAll();

// Récupérer les logs récents
$stmt = $db->prepare("
    SELECT gl.*, u.username
    FROM guild_logs gl
    JOIN users u ON gl.user_id = u.id
    WHERE gl.guild_id = ?
    ORDER BY gl.created_at DESC
    LIMIT 10
");
$stmt->execute([$guild_id]);
$logs = $stmt->fetchAll();
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- En-tête -->
        <div class="flex justify-between items-start mb-8">
            <div>
                <div class="flex items-center gap-4 mb-2">
                    <a href="index.php?page=guilds" class="text-gray-600 hover:text-gray-800">
                        ← Retour aux guildes
                    </a>
                    <h1 class="text-2xl font-bold"><?php echo htmlspecialchars($guild['name']); ?></h1>
                </div>
                <p class="text-gray-600"><?php echo htmlspecialchars($guild['description']); ?></p>
            </div>
            <?php if ($member_role === 'leader'): ?>
                <button 
                    onclick="openGuildSettings()"
                    class="px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600"
                >
                    Paramètres
                </button>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Informations principales -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Statistiques -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold mb-4">Statistiques</h2>
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <span class="block text-2xl font-bold text-emerald-600">
                                <?php echo $guild['level']; ?>
                            </span>
                            <span class="text-gray-500">Niveau</span>
                        </div>
                        <div>
                            <span class="block text-2xl font-bold text-emerald-600">
                                <?php echo number_format($guild['experience']); ?>
                            </span>
                            <span class="text-gray-500">Expérience</span>
                        </div>
                        <div>
                            <span class="block text-2xl font-bold text-emerald-600">
                                <?php echo $guild['member_count']; ?>/<?php echo $guild['member_limit']; ?>
                            </span>
                            <span class="text-gray-500">Membres</span>
                        </div>
                    </div>
                </div>

                <!-- Événements -->
                <?php if (!empty($events)): ?>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-lg font-semibold mb-4">Événements en cours</h2>
                        <div class="space-y-4">
                            <?php foreach ($events as $event): ?>
                                <div class="border rounded-lg p-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <h3 class="font-medium">
                                                <?php echo htmlspecialchars($event['title']); ?>
                                            </h3>
                                            <p class="text-sm text-gray-500">
                                                <?php echo htmlspecialchars($event['description']); ?>
                                            </p>
                                        </div>
                                        <span class="inline-block px-2 py-1 text-xs rounded-full <?php
                                            echo match($event['event_type']) {
                                                'challenge' => 'bg-blue-100 text-blue-800',
                                                'competition' => 'bg-purple-100 text-purple-800',
                                                'social' => 'bg-green-100 text-green-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                        ?>">
                                            <?php echo ucfirst($event['event_type']); ?>
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        Fin: <?php echo date('d/m/Y H:i', strtotime($event['end_date'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Logs -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold mb-4">Activité récente</h2>
                    <div class="space-y-2">
                        <?php foreach ($logs as $log): ?>
                            <div class="text-sm">
                                <span class="text-gray-500">
                                    <?php echo date('d/m H:i', strtotime($log['created_at'])); ?>
                                </span>
                                <span class="font-medium">
                                    <?php echo htmlspecialchars($log['username']); ?>
                                </span>
                                <?php echo htmlspecialchars($log['details']); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Liste des membres -->
            <div class="space-y-4">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold mb-4">Membres</h2>
                    <div class="space-y-4">
                        <?php foreach ($members as $member): ?>
                            <div class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                                            <span>👤</span>
                                        </div>
                                        <?php if (isUserOnline($member['last_activity'])): ?>
                                            <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="font-medium">
                                            <?php echo htmlspecialchars($member['username']); ?>
                                            <?php if ($member['id'] === $guild['leader_id']): ?>
                                                <span class="text-xs text-yellow-600">👑 Chef</span>
                                            <?php elseif ($member['role'] === 'officer'): ?>
                                                <span class="text-xs text-blue-600">⚔️ Officier</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            Niveau <?php echo $member['level']; ?>
                                        </div>
                                    </div>
                                </div>
                                <?php if (
                                    ($member_role === 'leader' && $member['id'] !== $_SESSION['user_id']) ||
                                    ($member_role === 'officer' && $member['role'] === 'member')
                                ): ?>
                                    <div class="flex gap-2">
                                        <?php if ($member_role === 'leader'): ?>
                                            <button 
                                                onclick="promoteMember(<?php echo $member['id']; ?>)"
                                                class="p-2 text-blue-500 hover:text-blue-700"
                                                title="<?php echo $member['role'] === 'officer' ? 'Rétrograder' : 'Promouvoir'; ?>"
                                            >
                                                <?php echo $member['role'] === 'officer' ? '⬇️' : '⬆️'; ?>
                                            </button>
                                        <?php endif; ?>
                                        <button 
                                            onclick="kickMember(<?php echo $member['id']; ?>)"
                                            class="p-2 text-red-500 hover:text-red-700"
                                            title="Exclure"
                                        >
                                            ❌
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if ($member_role === 'leader' || $member_role === 'officer'): ?>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h2 class="text-lg font-semibold mb-4">Gestion</h2>
                        <div class="space-y-4">
                            <?php if ($member_role === 'leader'): ?>
                                <button 
                                    onclick="openGuildSettings()"
                                    class="w-full px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600"
                                >
                                    Paramètres de la guilde
                                </button>
                            <?php endif; ?>
                            <button 
                                onclick="createEvent()"
                                class="w-full px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
                            >
                                Créer un événement
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
async function promoteMember(userId) {
    if (!confirm('Voulez-vous modifier le rôle de ce membre ?')) return;
    
    try {
        const response = await fetch('ajax/promote_guild_member.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `user_id=${userId}&guild_id=<?php echo $guild_id; ?>`
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Une erreur est survenue');
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Une erreur est survenue');
    }
}

async function kickMember(userId) {
    if (!confirm('Voulez-vous vraiment exclure ce membre ?')) return;
    
    try {
        const response = await fetch('ajax/kick_guild_member.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `user_id=${userId}&guild_id=<?php echo $guild_id; ?>`
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Une erreur est survenue');
        }
    } catch (error) {
        console.error('Erreur:', error);
        alert('Une erreur est survenue');
    }
}

function openGuildSettings() {
    // TODO: Implémenter la modal des paramètres
    alert('Fonctionnalité à venir');
}

function createEvent() {
    // TODO: Implémenter la modal de création d'événement
    alert('Fonctionnalité à venir');
}
</script>