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

function markAsRead(notificationId) {
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
            // Masquer la notification avec une animation
            const notification = document.getElementById('notification-' + notificationId);
            if (notification) {
                notification.style.transition = 'all 0.3s ease-out';
                notification.style.opacity = '0';
                notification.style.maxHeight = '0';
                notification.style.padding = '0';
                notification.style.margin = '0';
                
                // Supprimer l'élément après l'animation
                setTimeout(() => {
                    notification.remove();
                    
                    // Vérifier s'il reste des notifications
                    const container = document.querySelector('.notifications-container');
                    if (container && container.querySelectorAll('[id^="notification-"]').length === 0) {
                        container.style.display = 'none';
                    }
                }, 300);
            }
        }
    })
    .catch(error => console.error('Erreur:', error));
}
