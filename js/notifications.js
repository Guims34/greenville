function markAsRead(notificationId) {
    fetch('ajax/mark_notification_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `notification_id=${notificationId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Masquer la notification avec une animation
            const notification = document.getElementById(`notification-${notificationId}`);
            if (notification) {
                notification.classList.add('opacity-0');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }
        } else {
            console.error('Erreur:', data.error);
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
    });
}
