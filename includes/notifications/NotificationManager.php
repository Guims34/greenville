<?php
require_once 'NotificationConfig.php';

class NotificationManager {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function createWelcomeNotification($userId) {
        $config = NotificationConfig::TYPES['welcome'];
        $message = str_replace(
            '{coins}', 
            NotificationConfig::DEFAULT_VALUES['starting_coins'],
            $config['message']
        );

        return $this->createNotification(
            $userId,
            $config['title'],
            $message,
            $config['type']
        );
    }

    public function createLevelUpNotification($userId, $level, $features) {
        $config = NotificationConfig::TYPES['level_up'];
        $message = str_replace(
            ['{level}', '{features}'],
            [$level, $features],
            $config['message']
        );

        return $this->createNotification(
            $userId,
            $config['title'],
            $message,
            $config['type']
        );
    }

    private function createNotification($userId, $title, $message, $type) {
        $stmt = $this->db->prepare("
            INSERT INTO notifications (user_id, title, message, type)
            VALUES (?, ?, ?, ?)
        ");
        return $stmt->execute([$userId, $title, $message, $type]);
    }

    public function markAsRead($notificationId, $userId) {
        $stmt = $this->db->prepare("
            UPDATE notifications 
            SET is_read = TRUE 
            WHERE id = ? AND user_id = ?
        ");
        return $stmt->execute([$notificationId, $userId]);
    }
}