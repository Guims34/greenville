<?php
requireAuth();

// Récupérer la liste des conversations
$stmt = $db->prepare("
    SELECT 
        CASE 
            WHEN pm.sender_id = ? THEN pm.receiver_id
            ELSE pm.sender_id
        END as other_user_id,
        u.username,
        MAX(pm.created_at) as last_message,
        COUNT(CASE WHEN pm.receiver_id = ? AND pm.read_at IS NULL THEN 1 END) as unread_count
    FROM private_messages pm
    JOIN users u ON (
        CASE 
            WHEN pm.sender_id = ? THEN pm.receiver_id
            ELSE pm.sender_id
        END = u.id
    )
    WHERE pm.sender_id = ? OR pm.receiver_id = ?
    GROUP BY 
        CASE 
            WHEN pm.sender_id = ? THEN pm.receiver_id
            ELSE pm.sender_id
        END,
        u.username
    ORDER BY last_message DESC
");
$stmt->execute([
    $_SESSION['user_id'],
    $_SESSION['user_id'],
    $_SESSION['user_id'],
    $_SESSION['user_id'],
    $_SESSION['user_id'],
    $_SESSION['user_id']
]);
$conversations = $stmt->fetchAll();

// Si une conversation est sélectionnée
$selected_user = null;
$messages = [];
if (isset($_GET['user']) && is_numeric($_GET['user'])) {
    $user_id = (int)$_GET['user'];
    
    // Récupérer les informations de l'utilisateur
    $stmt = $db->prepare("SELECT id, username FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $selected_user = $stmt->fetch();

    if ($selected_user) {
        // Marquer les messages comme lus
        $stmt = $db->prepare("
            UPDATE private_messages 
            SET read_at = NOW() 
            WHERE sender_id = ? AND receiver_id = ? AND read_at IS NULL
        ");
        $stmt->execute([$user_id, $_SESSION['user_id']]);

        // Récupérer les messages
        $stmt = $db->prepare("
            SELECT pm.*, u.username
            FROM private_messages pm
            JOIN users u ON pm.sender_id = u.id
            WHERE (pm.sender_id = ? AND pm.receiver_id = ?)
            OR (pm.sender_id = ? AND pm.receiver_id = ?)
            ORDER BY pm.created_at DESC
            LIMIT 50
        ");
        $stmt->execute([
            $_SESSION['user_id'],
            $user_id,
            $user_id,
            $_SESSION['user_id']
        ]);
        $messages = array_reverse($stmt->fetchAll());
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex h-[600px] bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Liste des conversations -->
        <div class="w-1/3 border-r">
            <div class="p-4 border-b">
                <h2 class="text-lg font-semibold">Messages</h2>
            </div>
            <div class="overflow-y-auto h-[calc(100%-4rem)]">
                <?php if (empty($conversations)): ?>
                    <p class="text-center text-gray-500 p-4">
                        Aucune conversation
                    </p>
                <?php else: ?>
                    <?php foreach ($conversations as $conv): ?>
                        <a 
                            href="?page=messages&user=<?php echo $conv['other_user_id']; ?>"
                            class="block p-4 hover:bg-gray-50 <?php echo isset($_GET['user']) && $_GET['user'] == $conv['other_user_id'] ? 'bg-gray-50' : ''; ?>"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                                        <span>👤</span>
                                    </div>
                                    <div>
                                        <h4 class="font-medium">
                                            <?php echo htmlspecialchars($conv['username']); ?>
                                        </h4>
                                        <p class="text-sm text-gray-500">
                                            <?php 
                                            $time_diff = time() - strtotime($conv['last_message']);
                                            if ($time_diff < 60) {
                                                echo "À l'instant";
                                            } elseif ($time_diff < 3600) {
                                                echo "Il y a " . floor($time_diff / 60) . " min";
                                            } elseif ($time_diff < 86400) {
                                                echo "Il y a " . floor($time_diff / 3600) . "h";
                                            } else {
                                                echo date('d/m/Y', strtotime($conv['last_message']));
                                            }
                                            ?>
                                        </p>
                                    </div>
                                </div>
                                <?php if ($conv['unread_count'] > 0): ?>
                                    <span class="px-2 py-1 bg-emerald-500 text-white text-xs rounded-full">
                                        <?php echo $conv['unread_count']; ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Zone de conversation -->
        <div class="flex-1 flex flex-col">
            <?php if ($selected_user): ?>
                <!-- En-tête -->
                <div class="p-4 border-b">
                    <h3 class="font-semibold">
                        <?php echo htmlspecialchars($selected_user['username']); ?>
                    </h3>
                </div>

                <!-- Messages -->
                <div id="messages" class="flex-1 overflow-y-auto p-4 space-y-4">
                    <?php foreach ($messages as $message): ?>
                        <div class="flex <?php echo $message['sender_id'] == $_SESSION['user_id'] ? 'justify-end' : 'justify-start'; ?>">
                            <div class="max-w-[70%] rounded-lg p-3 <?php echo $message['sender_id'] == $_SESSION['user_id'] ? 'bg-emerald-500 text-white' : 'bg-gray-100'; ?>">
                                <p class="break-words"><?php echo htmlspecialchars($message['message']); ?></p>
                                <p class="text-xs opacity-70 mt-1">
                                    <?php echo date('H:i', strtotime($message['created_at'])); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Formulaire d'envoi -->
                <div class="p-4 border-t">
                    <form id="messageForm" class="flex gap-2">
                        <input type="hidden" id="receiverId" value="<?php echo $selected_user['id']; ?>">
                        <input 
                            type="text" 
                            id="messageInput"
                            class="flex-1 rounded-lg border-gray-300"
                            placeholder="Votre message..."
                            required
                        >
                        <button 
                            type="submit"
                            class="px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600"
                        >
                            Envoyer
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <div class="flex-1 flex items-center justify-center">
                    <p class="text-gray-500">
                        Sélectionnez une conversation
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Faire défiler jusqu'au dernier message
const messages = document.getElementById('messages');
if (messages) {
    messages.scrollTop = messages.scrollHeight;
}

// Gestion de l'envoi de message
const messageForm = document.getElementById('messageForm');
if (messageForm) {
    messageForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const receiverId = document.getElementById('receiverId').value;
        const messageInput = document.getElementById('messageInput');
        const message = messageInput.value.trim();
        
        if (!message) return;

        try {
            const response = await fetch('ajax/send_private_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `receiver_id=${receiverId}&message=${encodeURIComponent(message)}`
            });

            const data = await response.json();
            
            if (data.success) {
                messageInput.value = '';
                location.reload(); // Recharger pour voir le nouveau message
            } else {
                alert(data.error || 'Une erreur est survenue');
            }
        } catch (error) {
            console.error('Erreur:', error);
            alert('Une erreur est survenue');
        }
    });
}

// Actualisation automatique
setInterval(() => {
    const receiverId = document.getElementById('receiverId')?.value;
    if (receiverId) {
        fetch(`ajax/check_new_messages.php?user=${receiverId}`)
            .then(response => response.json())
            .then(data => {
                if (data.new_messages) {
                    location.reload();
                }
            });
    }
}, 10000);
</script>