<?php
require_once '../config/GameDefaults.php';

class UserCreator {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function createUser($username, $email, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $this->db->prepare("
            INSERT INTO users (
                username, 
                email, 
                password, 
                coins,
                premium_coins,
                level,
                status,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, 'active', NOW())
        ");

        $stmt->execute([
            $username, 
            $email, 
            $hashedPassword, 
            NotificationConfig::DEFAULT_VALUES['starting_coins'],
            NotificationConfig::DEFAULT_VALUES['starting_premium_coins'],
            NotificationConfig::DEFAULT_VALUES['starting_level']
        ]);

        return $this->db->lastInsertId();
    }
}