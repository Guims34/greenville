<?php
namespace App\Classes\Progression;

use PDO;

class ExperienceManager {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function addExperience(int $userId, int $amount): array {
        try {
            $this->db->beginTransaction();

            // Récupérer les informations actuelles
            $stmt = $this->db->prepare("
                SELECT experience, level FROM users WHERE id = ?
            ");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();

            $newExperience = $user['experience'] + $amount;

            // Vérifier le nouveau niveau
            $stmt = $this->db->prepare("
                SELECT * FROM levels 
                WHERE xp_required <= ? 
                ORDER BY level DESC 
                LIMIT 1
            ");
            $stmt->execute([$newExperience]);
            $newLevel = $stmt->fetch();

            $rewards = [];
            if ($newLevel['level'] > $user['level']) {
                $rewards = $this->processLevelUp($userId, $newLevel);
            }

            // Mettre à jour l'expérience
            $stmt = $this->db->prepare("
                UPDATE users 
                SET experience = ?, 
                    level = ? 
                WHERE id = ?
            ");
            $stmt->execute([$newExperience, $newLevel['level'], $userId]);

            $this->db->commit();

            return [
                'success' => true,
                'new_experience' => $newExperience,
                'new_level' => $newLevel['level'],
                'rewards' => $rewards
            ];

        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Erreur ajout expérience: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    private function processLevelUp(int $userId, array $levelData): array {
        $level = new Level($levelData);
        $rewards = $level->getRewards();

        // Donner les récompenses
        $stmt = $this->db->prepare("
            UPDATE users 
            SET coins = coins + ?,
                premium_coins = premium_coins + ?
            WHERE id = ?
        ");
        $stmt->execute([
            $rewards['coins'],
            $rewards['premium_coins'],
            $userId
        ]);

        // Créer une notification
        $stmt = $this->db->prepare("
            INSERT INTO notifications (
                user_id, title, message, type
            ) VALUES (
                ?, 'Niveau supérieur !', ?, 'success'
            )
        ");

        $message = "Vous avez atteint le niveau {$level->getLevel()} ! " .
                  "Nouvelles fonctionnalités débloquées : " . 
                  implode(", ", $rewards['features']);

        $stmt->execute([$userId, $message]);

        return $rewards;
    }

    public function getProgress(int $userId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT u.level, u.experience,
                       (SELECT xp_required FROM levels WHERE level = u.level) as current_level_xp,
                       (SELECT MIN(xp_required) FROM levels WHERE level = u.level + 1) as next_level_xp
                FROM users u
                WHERE u.id = ?
            ");
            $stmt->execute([$userId]);
            $progress = $stmt->fetch();

            if (!$progress) {
                return [];
            }

            $currentXp = $progress['experience'] - $progress['current_level_xp'];
            $requiredXp = $progress['next_level_xp'] - $progress['current_level_xp'];
            $percentage = ($currentXp / $requiredXp) * 100;

            return [
                'level' => $progress['level'],
                'experience' => $progress['experience'],
                'progress_percentage' => min(100, max(0, $percentage)),
                'xp_current' => $currentXp,
                'xp_required' => $requiredXp
            ];

        } catch (\PDOException $e) {
            error_log("Erreur récupération progression: " . $e->getMessage());
            return [];
        }
    }
}
