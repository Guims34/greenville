<?php
class Notifications {
    public static function render($notifications) {
        if (empty($notifications)) {
            return '';
        }

        ob_start();
        ?>
        <style>
        .notification {
            transition: all 0.3s ease-out;
        }
        .notification.hiding {
            opacity: 0;
            transform: translateX(100px);
            height: 0;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        .close-button {
            opacity: 0.6;
            transition: opacity 0.2s;
        }
        .close-button:hover {
            opacity: 1;
        }
        </style>

        <div class="notifications-container mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-lg font-semibold mb-4">Notifications</h2>
                <div class="space-y-4" id="notifications-list">
                    <?php foreach ($notifications as $notif): ?>
                        <div id="notification-<?php echo $notif['id']; ?>" 
                             class="notification flex items-start justify-between p-4 rounded-lg <?php
                                echo match($notif['type']) {
                                    'success' => 'bg-green-50 text-green-800',
                                    'warning' => 'bg-yellow-50 text-yellow-800',
                                    'error' => 'bg-red-50 text-red-800',
                                    default => 'bg-blue-50 text-blue-800'
                                };
                             ?>">
                            <div class="flex-1 pr-4">
                                <h3 class="font-medium"><?php echo htmlspecialchars($notif['title']); ?></h3>
                                <p class="text-sm whitespace-pre-line"><?php echo htmlspecialchars($notif['message']); ?></p>
                            </div>
                            <button onclick="dismissNotification(<?php echo $notif['id']; ?>)" 
                                    class="close-button p-1.5 rounded-full hover:bg-black/5 flex items-center justify-center"
                                    title="Fermer">
                                <svg xmlns="http://www.w3.org/2000/svg" 
                                     class="h-5 w-5" 
                                     viewBox="0 0 20 20" 
                                     fill="currentColor">
                                    <path fill-rule="evenodd" 
                                          d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" 
                                          clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <script>
        function dismissNotification(notificationId) {
            const notification = document.getElementById('notification-' + notificationId);
            if (!notification) return;

            // Appeler l'API pour marquer comme lu
            fetch('ajax/mark_notification_read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'notification_id=' + notificationId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Ajouter la classe pour l'animation
                    notification.classList.add('hiding');
                    
                    // Supprimer après l'animation
                    setTimeout(() => {
                        notification.remove();
                        
                        // Vérifier s'il reste des notifications
                        const container = document.querySelector('.notifications-container');
                        const remainingNotifications = document.querySelectorAll('.notification:not(.hiding)');
                        if (container && remainingNotifications.length === 0) {
                            container.style.display = 'none';
                        }
                    }, 300);
                }
            })
            .catch(error => console.error('Erreur:', error));
        }
        </script>
        <?php
        return ob_get_clean();
    }
}
