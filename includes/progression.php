<?php
class Progression {
    private $db;
    private $userId;

    public function __construct($db, $userId) {
        $this->db = $db;
        $this->userId = $userId;
    }

    public function addExperience($amount) {
        try {
            $this->db->beginTransaction();

            // Mettre à jour l'XP
            $stmt = $this->db->prepare("
                UPDATE users 
                SET experience = experience + ? 
                WHERE id = ?
            ");
            $stmt->execute([$amount, $this->userId]);

            // Vérifier le nouveau niveau
            $stmt = $this->db->prepare("
                SELECT u.experience,
                       (SELECT level FROM levels WHERE xp_required <= u.experience ORDER BY level DESC LIMIT 1) as new_level
                FROM users u 
                WHERE u.id = ?
            ");
            $stmt->execute([$this->userId]);
            $result = $stmt->fetch();

            // Récupérer l'ancien niveau
            $oldLevel = $_SESSION['user_level'] ?? 1;
            $newLevel = $result['new_level'];

            // Si le niveau a augmenté
            if ($newLevel > $oldLevel) {
                // Récupérer les récompenses
                $stmt = $this->db->prepare("
                    SELECT coins_reward, premium_coins_reward, unlocked_features
                    FROM levels 
                    WHERE level = ?
                ");
                $stmt->execute([$newLevel]);
                $rewards = $stmt->fetch();

                // Donner les récompenses
                $stmt = $this->db->prepare("
                    UPDATE users 
                    SET coins = coins + ?,
                        premium_coins = premium_coins + ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $rewards['coins_reward'],
                    $rewards['premium_coins_reward'],
                    $this->userId
                ]);

                // Créer une notification
                $stmt = $this->db->prepare("
                    INSERT INTO notifications (user_id, title, message, type) 
                    VALUES (?, 'Niveau supérieur !', ?, 'success')
                ");
                $stmt->execute([
                    $this->userId,
                    "Vous avez atteint le niveau $newLevel ! Nouvelles fonctionnalités débloquées : {$rewards['unlocked_features']}"
                ]);

                $_SESSION['user_level'] = $newLevel;
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erreur progression: " . $e->getMessage());
            return false;
        }
    }

    public function checkAchievements() {
        try {
            // Récupérer les statistiques
            $stmt = $this->db->prepare("
                SELECT * FROM user_stats 
                WHERE user_id = ?
            ");
            $stmt->execute([$this->userId]);
            $stats = $stmt->fetch();

            if (!$stats) return false;

            // Récupérer les succès non complétés
            $stmt = $this->db->prepare("
                SELECT a.* 
                FROM achievements a
                LEFT JOIN user_achievements ua ON a.id = ua.achievement_id AND ua.user_id = ?
                WHERE ua.completed IS NULL OR ua.completed = 0
            ");
            $stmt->execute([$this->userId]);
            $achievements = $stmt->fetchAll();

            foreach ($achievements as $achievement) {
                $progress = 0;
                $completed = false;

                // Vérifier la progression selon le type
                switch ($achievement['category']) {
                    case 'cultivation':
                        $progress = ($stats['plants_harvested'] / $achievement['target_value']) * 100;
                        $completed = $stats['plants_harvested'] >= $achievement['target_value'];
                        break;

                    case 'trading':
                        $progress = ($stats['trades_completed'] / $achievement['target_value']) * 100;
                        $completed = $stats['trades_completed'] >= $achievement['target_value'];
                        break;

                    case 'collection':
                        $progress = ($stats['items_collected'] / $achievement['target_value']) * 100;
                        $completed = $stats['items_collected'] >= $achievement['target_value'];
                        break;
                }

                // Mettre à jour le succès
                $stmt = $this->db->prepare("
                    INSERT INTO user_achievements (user_id, achievement_id, progress, completed, completed_at)
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        progress = ?,
                        completed = ?,
                        completed_at = ?
                ");

                $completedAt = $completed ? date('Y-m-d H:i:s') : null;

                $stmt->execute([
                    $this->userId,
                    $achievement['id'],
                    $progress,
                    $completed,
                    $completedAt,
                    $progress,
                    $completed,
                    $completedAt
                ]);

                // Si complété, donner les récompenses
                if ($completed) {
                    $this->addExperience($achievement['xp_reward']);
                    
                    $stmt = $this->db->prepare("
                        UPDATE users 
                        SET coins = coins + ?,
                            premium_coins = premium_coins + ?
                        WHERE id = ?
                    ");
                    $stmt->execute([
                        $achievement['coins_reward'],
                        $achievement['premium_coins_reward'],
                        $this->userId
                    ]);

                    // Notification
                    $stmt = $this->db->prepare("
                        INSERT INTO notifications (user_id, title, message, type) 
                        VALUES (?, 'Succès débloqué !', ?, 'success')
                    ");
                    $stmt->execute([
                        $this->userId,
                        "Vous avez débloqué le succès : {$achievement['title']}"
                    ]);
                }
            }

            return true;

        } catch (Exception $e) {
            error_log("Erreur succès: " . $e->getMessage());
            return false;
        }
    }

    public function generateDailyMissions() {
        try {
            $this->db->beginTransaction();

            // Supprimer les missions expirées
            $stmt = $this->db->prepare("
                DELETE FROM user_missions 
                WHERE user_id = ? AND expires_at < NOW()
            ");
            $stmt->execute([$this->userId]);

            // Vérifier les missions actives
            $stmt = $this->db->prepare("
                SELECT COUNT(*) 
                FROM user_missions 
                WHERE user_id = ? AND expires_at > NOW()
            ");
            $stmt->execute([$this->userId]);
            
            if ($stmt->fetchColumn() > 0) {
                $this->db->commit();
                return true;
            }

            // Sélectionner 3 missions aléatoires
            $stmt = $this->db->query("
                SELECT * FROM daily_missions 
                ORDER BY RAND() 
                LIMIT 3
            ");
            $missions = $stmt->fetchAll();

            // Créer les nouvelles missions
            $stmt = $this->db->prepare("
                INSERT INTO user_missions (user_id, mission_id, expires_at) 
                VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 DAY))
            ");

            foreach ($missions as $mission) {
                $stmt->execute([$this->userId, $mission['id']]);
            }

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Erreur missions: " . $e->getMessage());
            return false;
        }
    }

    public function updateStats($type, $value = 1) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO user_stats (user_id, {$type}) 
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE {$type} = {$type} + ?
            ");
            $stmt->execute([$this->userId, $value, $value]);

            $this->checkAchievements();
            return true;

        } catch (Exception $e) {
            error_log("Erreur stats: " . $e->getMessage());
            return false;
        }
    }
}