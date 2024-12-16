<?php
requireAuth();

// Récupérer les informations de l'utilisateur
$stmt = $db->prepare("
    SELECT u.*, 
           (SELECT COUNT(*) FROM friendships WHERE (sender_id = u.id OR receiver_id = u.id) AND status = 'accepted') as friends_count,
           (SELECT COUNT(*) FROM private_messages WHERE receiver_id = u.id AND read_at IS NULL) as unread_messages,
           gm.guild_id,
           g.name as guild_name,
           g.level as guild_level
    FROM users u
    LEFT JOIN guild_members gm ON u.id = gm.user_id
    LEFT JOIN guilds g ON gm.guild_id = g.id
    WHERE u.id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Récupérer les demandes d'amitié en attente
$stmt = $db->prepare("
    SELECT u.id, u.username, u.level, f.created_at
    FROM friendships f
    JOIN users u ON f.sender_id = u.id
    WHERE f.receiver_id = ? AND f.status = 'pending'
    ORDER BY f.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$pending_requests = $stmt->fetchAll();

// Récupérer les classements
$stmt = $db->prepare("
    SELECT l.*, u.username
    FROM leaderboards l
    JOIN users u ON l.user_id = u.id
    WHERE l.category = 'experience'
    ORDER BY l.score DESC
    LIMIT 10
");
$stmt->execute();
$top_players = $stmt->fetchAll();
?>

<div class="container mx-auto px-4 py-8">
    <!-- En-tête avec stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Profil -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-16 h-16 bg-emerald-100 rounded-full flex items-center justify-center">
                    <span class="text-2xl">👤</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold"><?php echo htmlspecialchars($user['username']); ?></h2>
                    <p class="text-gray-500">Niveau <?php echo $user['level']; ?></p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 text-center">
                <div>
                    <span class="block text-2xl font-bold"><?php echo $user['friends_count']; ?></span>
                    <span class="text-gray-500">Amis</span>
                </div>
                <div>
                    <span class="block text-2xl font-bold"><?php echo $user['unread_messages']; ?></span>
                    <span class="text-gray-500">Messages</span>
                </div>
            </div>
        </div>

        <!-- Guilde -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <?php if ($user['guild_id']): ?>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold"><?php echo htmlspecialchars($user['guild_name']); ?></h3>
                    <span class="px-2 py-1 bg-emerald-100 text-emerald-800 rounded-full text-sm">
                        Niveau <?php echo $user['guild_level']; ?>
                    </span>
                </div>
                <a 
                    href="index.php?page=guild&id=<?php echo $user['guild_id']; ?>"
                    class="block w-full text-center bg-emerald-500 text-white px-4 py-2 rounded-lg hover:bg-emerald-600"
                >
                    Voir ma guilde
                </a>
            <?php else: ?>
                <div class="text-center">
                    <p class="text-gray-500 mb-4">Vous n'êtes pas membre d'une guilde</p>
                    <a 
                        href="index.php?page=guilds"
                        class="block w-full bg-emerald-500 text-white px-4 py-2 rounded-lg hover:bg-emerald-600"
                    >
                        Rejoindre une guilde
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Classement -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Top Joueurs</h3>
            <div class="space-y-2">
                <?php foreach (array_slice($top_players, 0, 5) as $index => $player): ?>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 text-center font-bold <?php
                                echo match($index) {
                                    0 => 'text-yellow-500',
                                    1 => 'text-gray-400',
                                    2 => 'text-amber-600',
                                    default => 'text-gray-600'
                                };
                            ?>"><?php echo $index + 1; ?></span>
                            <span class="<?php echo $player['user_id'] === $_SESSION['user_id'] ? 'font-bold' : ''; ?>">
                                <?php echo htmlspecialchars($player['username']); ?>
                            </span>
                        </div>
                        <span class="text-gray-500">
                            <?php echo number_format($player['score']); ?> XP
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
            <a 
                href="index.php?page=leaderboard"
                class="block text-center text-emerald-600 hover:text-emerald-700 mt-4"
            >
                Voir tout le classement →
            </a>
        </div>
    </div>

    <!-- Contenu principal -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Liste d'amis -->
        <div class="md:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold">Amis</h3>
                    <button 
                        onclick="openFriendSearch()"
                        class="text-emerald-600 hover:text-emerald-700"
                    >
                        Ajouter des amis
                    </button>
                </div>

                <?php
                // Récupérer la liste d'amis
                $stmt = $db->prepare("
                    SELECT u.id, u.username, u.level,
                           CASE WHEN u.last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE) THEN 1 ELSE 0 END as is_online
                    FROM users u
                    JOIN friendships f ON (f.sender_id = u.id OR f.receiver_id = u.id)
                    WHERE (f.sender_id = ? OR f.receiver_id = ?)
                    AND f.status = 'accepted'
                    AND u.id != ?
                    ORDER BY is_online DESC, u.username ASC
                ");
                $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id'], $_SESSION['user_id']]);
                $friends = $stmt->fetchAll();
                ?>

                <?php if (empty($friends)): ?>
                    <p class="text-center text-gray-500 py-8">
                        Vous n'avez pas encore d'amis. Commencez à en ajouter !
                    </p>
                <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <?php foreach ($friends as $friend): ?>
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="relative">
                                        <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                                            <span>👤</span>
                                        </div>
                                        <?php if ($friend['is_online']): ?>
                                            <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <h4 class="font-medium">
                                            <?php echo htmlspecialchars($friend['username']); ?>
                                        </h4>
                                        <p class="text-sm text-gray-500">
                                            Niveau <?php echo $friend['level']; ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button 
                                        onclick="sendMessage(<?php echo $friend['id']; ?>, '<?php echo htmlspecialchars($friend['username']); ?>')"
                                        class="p-2 text-gray-500 hover:text-gray-700"
                                        title="Envoyer un message"
                                    >
                                        ✉️
                                    </button>
                                    <button 
                                        onclick="removeFriend(<?php echo $friend['id']; ?>)"
                                        class="p-2 text-red-500 hover:text-red-700"
                                        title="Retirer des amis"
                                    >
                                        ❌
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Demandes d'amitié -->
        <div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold mb-4">Demandes d'amitié</h3>
                
                <?php if (empty($pending_requests)): ?>
                    <p class="text-center text-gray-500 py-4">
                        Aucune demande en attente
                    </p>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($pending_requests as $request): ?>
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                                        <span>👤</span>
                                    </div>
                                    <div>
                                        <h4 class="font-medium">
                                            <?php echo htmlspecialchars($request['username']); ?>
                                        </h4>
                                        <p class="text-sm text-gray-500">
                                            Niveau <?php echo $request['level']; ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button 
                                        onclick="acceptFriend(<?php echo $request['id']; ?>)"
                                        class="p-2 text-emerald-500 hover:text-emerald-700"
                                        title="Accepter"
                                    >
                                        ✅
                                    </button>
                                    <button 
                                        onclick="rejectFriend(<?php echo $request['id']; ?>)"
                                        class="p-2 text-red-500 hover:text-red-700"
                                        title="Refuser"
                                    >
                                        ❌
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal de recherche d'amis -->
<div id="friendSearchModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Rechercher des amis</h3>
                <button onclick="closeFriendSearch()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="mb-4">
                <input 
                    type="text" 
                    id="friendSearch"
                    placeholder="Rechercher par nom d'utilisateur..."
                    class="w-full rounded-md border-gray-300"
                    oninput="searchUsers(this.value)"
                >
            </div>

            <div id="searchResults" class="space-y-4 max-h-96 overflow-y-auto">
                <!-- Les résultats seront ajoutés ici -->
            </div>
        </div>
    </div>
</div>

<!-- Modal de messages -->
<div id="messageModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold">Message à <span id="messageRecipient"></span></h3>
                <button onclick="closeMessageModal()" class="text-gray-400 hover:text-gray-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form id="messageForm" onsubmit="sendPrivateMessage(event)" class="space-y-4">
                <input type="hidden" id="messageReceiverId">
                <textarea 
                    id="messageContent"
                    rows="4"
                    required
                    placeholder="Votre message..."
                    class="w-full rounded-md border-gray-300"
                ></textarea>

                <div class="flex justify-end">
                    <button 
                        type="submit"
                        class="px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600"
                    >
                        Envoyer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openFriendSearch() {
    document.getElementById('friendSearchModal').classList.remove('hidden');
}

function closeFriendSearch() {
    document.getElementById('friendSearchModal').classList.add('hidden');
}

function searchUsers(query) {
    if (query.length < 3) {
        document.getElementById('searchResults').innerHTML = '<p class="text-gray-500 text-center">Entrez au moins 3 caractères</p>';
        return;
    }

    fetch('ajax/search_users.php?q=' + encodeURIComponent(query))
        .then(response => response.json())
        .then(data => {
            const results = document.getElementById('searchResults');
            results.innerHTML = '';

            if (data.length === 0) {
                results.innerHTML = '<p class="text-gray-500 text-center">Aucun utilisateur trouvé</p>';
                return;
            }

            data.forEach(user => {
                results.innerHTML += `
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                                <span>👤</span>
                            </div>
                            <div>
                                <h4 class="font-medium">${user.username}</h4>
                                <p class="text-sm text-gray-500">Niveau ${user.level}</p>
                            </div>
                        </div>
                        <button 
                            onclick="addFriend(${user.id})"
                            class="px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600"
                        >
                            Ajouter
                        </button>
                    </div>
                `;
            });
        });
}

function addFriend(userId) {
    fetch('ajax/add_friend.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `user_id=${userId}`
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

function acceptFriend(userId) {
    fetch('ajax/accept_friend.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `user_id=${userId}`
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

function rejectFriend(userId) {
    if (!confirm('Êtes-vous sûr de vouloir refuser cette demande ?')) return;

    fetch('ajax/reject_friend.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `user_id=${userId}`
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

function removeFriend(userId) {
    if (!confirm('Êtes-vous sûr de vouloir retirer cet ami ?')) return;

    fetch('ajax/remove_friend.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `user_id=${userId}`
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

function sendMessage(userId, username) {
    document.getElementById('messageReceiverId').value = userId;
    document.getElementById('messageRecipient').textContent = username;
    document.getElementById('messageModal').classList.remove('hidden');
}

function closeMessageModal() {
    document.getElementById('messageModal').classList.add('hidden');
    document.getElementById('messageContent').value = '';
}

function sendPrivateMessage(event) {
    event.preventDefault();

    const receiverId = document.getElementById('messageReceiverId').value;
    const content = document.getElementById('messageContent').value;

    fetch('ajax/send_private_message.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `receiver_id=${receiverId}&message=${encodeURIComponent(content)}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeMessageModal();
            alert('Message envoyé avec succès');
        } else {
            alert(data.error || 'Une erreur est survenue');
        }
    });
}

// Vérifier les nouveaux messages toutes les 30 secondes
setInterval(() => {
    fetch('ajax/check_messages.php')
        .then(response => response.json())
        .then(data => {
            if (data.unread > 0) {
                // Mettre à jour le compteur de messages non lus
                // TODO: Implémenter la notification visuelle
            }
        });
}, 30000);
</script>