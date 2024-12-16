<?php
requireAuth();

// Récupérer les guildes disponibles
$stmt = $db->prepare("
    SELECT g.*, 
           COUNT(gm.id) as member_count,
           (SELECT COUNT(*) FROM guild_members WHERE guild_id = g.id) as current_members
    FROM guilds g
    LEFT JOIN guild_members gm ON g.id = gm.guild_id
    GROUP BY g.id
    ORDER BY g.level DESC, g.experience DESC
    LIMIT 50
");
$stmt->execute();
$guilds = $stmt->fetchAll();

// Vérifier si l'utilisateur est déjà dans une guilde
$stmt = $db->prepare("
    SELECT g.*, gm.role 
    FROM guilds g
    JOIN guild_members gm ON g.id = gm.guild_id
    WHERE gm.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$userGuild = $stmt->fetch();
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- En-tête -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold">Guildes</h1>
            <?php if (!$userGuild): ?>
                <button 
                    onclick="openCreateGuildModal()"
                    class="px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600"
                >
                    Créer une guilde
                </button>
            <?php endif; ?>
        </div>

        <?php if ($userGuild): ?>
            <!-- Guilde actuelle de l'utilisateur -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-xl font-bold mb-2"><?php echo htmlspecialchars($userGuild['name']); ?></h2>
                        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($userGuild['description']); ?></p>
                        <div class="flex items-center gap-4">
                            <span class="text-sm bg-emerald-100 text-emerald-800 px-2 py-1 rounded-full">
                                Niveau <?php echo $userGuild['level']; ?>
                            </span>
                            <span class="text-sm text-gray-500">
                                Votre rôle: <?php echo ucfirst($userGuild['role']); ?>
                            </span>
                        </div>
                    </div>
                    <a 
                        href="index.php?page=guild&id=<?php echo $userGuild['id']; ?>"
                        class="px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600"
                    >
                        Voir ma guilde
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Liste des guildes -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($guilds as $guild): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-semibold">
                                    <?php echo htmlspecialchars($guild['name']); ?>
                                </h3>
                                <span class="inline-block px-2 py-1 text-xs rounded-full bg-emerald-100 text-emerald-800">
                                    Niveau <?php echo $guild['level']; ?>
                                </span>
                            </div>
                            <div class="text-sm text-gray-500">
                                <?php echo $guild['current_members']; ?>/<?php echo $guild['member_limit']; ?> membres
                            </div>
                        </div>

                        <p class="text-gray-600 text-sm mb-4">
                            <?php echo htmlspecialchars($guild['description']); ?>
                        </p>

                        <?php if (!$userGuild): ?>
                            <button 
                                onclick="joinGuild(<?php echo $guild['id']; ?>)"
                                class="w-full px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 disabled:opacity-50"
                                <?php echo $guild['current_members'] >= $guild['member_limit'] ? 'disabled' : ''; ?>
                            >
                                <?php echo $guild['current_members'] >= $guild['member_limit'] ? 'Guilde pleine' : 'Rejoindre'; ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Modal de création de guilde -->
<div id="createGuildModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Créer une guilde</h3>
                <button onclick="closeCreateGuildModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="createGuildForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nom de la guilde</label>
                    <input 
                        type="text" 
                        name="name" 
                        required
                        maxlength="50"
                        class="mt-1 block w-full rounded-md border-gray-300"
                    >
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea 
                        name="description" 
                        rows="3"
                        required
                        class="mt-1 block w-full rounded-md border-gray-300"
                    ></textarea>
                </div>

                <div class="flex justify-end space-x-3">
                    <button 
                        type="button"
                        onclick="closeCreateGuildModal()"
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                    >
                        Annuler
                    </button>
                    <button 
                        type="submit"
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700"
                    >
                        Créer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCreateGuildModal() {
    document.getElementById('createGuildModal').classList.remove('hidden');
}

function closeCreateGuildModal() {
    document.getElementById('createGuildModal').classList.add('hidden');
}

document.getElementById('createGuildForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('ajax/create_guild.php', {
            method: 'POST',
            body: formData
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
});

async function joinGuild(guildId) {
    if (!confirm('Voulez-vous rejoindre cette guilde ?')) return;
    
    try {
        const response = await fetch('ajax/join_guild.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `guild_id=${guildId}`
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
</script>