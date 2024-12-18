function dismissNotification(notificationId) {
    console.log('Dismissing notification:', notificationId);
    
    const notification = document.getElementById('notification-' + notificationId);
    if (!notification) {
        console.error('Notification not found:', notificationId);
        return;
    }

    fetch('/ajax/mark_notification_read.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'notification_id=' + notificationId
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        if (data.success) {
            notification.classList.add('hiding');
            
            setTimeout(() => {
                notification.remove();
                
                const container = document.querySelector('.notifications-container');
                const remainingNotifications = document.querySelectorAll('.notification:not(.hiding)');
                if (container && remainingNotifications.length === 0) {
                    container.style.display = 'none';
                }
            }, 300);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}
