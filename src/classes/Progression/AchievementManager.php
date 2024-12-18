<?php
namespace App\Classes\Progression;

use PDO;

class AchievementManager {
    private PDO $db;
    private ExperienceManager $expManager;

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->expManager = new ExperienceManager($db);
    }

    public function checkAchievements(int $userId): void {
        try {
            // Récupérer les statistiques
            $stmt = $this->db->prepare("SELECT * FROM user_stats WHERE user_id = ?");
            $stmt->execute([$userId]);
            $stats = $stmt->fetch();

            if (!$stats) return;

            // Récupérer les succès non complétés
            $stmt = $this->db->prepare("
                SELECT a.* 
                FROM achievements a
                LEFT JOIN user_achievements ua ON a.id = ua.achievement_id AND ua.user_id = ?
                WHERE ua.completed IS NULL OR ua.completed = 0
            ");
            $stmt->execute([$userId]);
            $achievements = $stmt->fetchAll();

            foreach ($achievements as $achievementData) {
                $achievement = new Achievement($achievementData);
                $this->processAchievement($userId, $achievement, $stats);
            }

        } catch (\PDOException $e) {
            error_log("Erreur vérification succès: " . $e->getMessage());
        }
    }

    private function processAchievement(int $userId, Achievement $achievement, array $stats): void {
        $progress = $this->calculateProgress($achievement, $stats);
        
        if ($achievement->updateProgress($progress)) {
            $this->completeAchievement($userId, $achievement);
        } else {
            $this->updateProgress($userId, $achievement);
        }
    }

    private function calculateProgress(Achievement $achievement, array $stats): int {
        return match($achievement->getCategory()) {
            'cultivation' => $stats['plants_harvested'],
            'trading' => $stats['trades_completed'],
            'collection' => $stats['items_collected'],
            'social' => $stats['friends_count'] ?? 0,
            default => 0
        };
    }

    private function completeAchievement(int $userId, Achievement $achievement): void {
        try {
            $this->db->beginTransaction();

            // Mettre à jour le succès
            $stmt = $this->db->prepare("
                UPDATE user_achievements 
                SET completed = TRUE,
                    completed_at = NOW()
                WHERE user_id = ? AND achievement_id = ?
            ");
            $stmt->execute([$userId, $achievement->getId()]);

            // Donner les récompenses
            $rewards = $achievement->getRewards();
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

            // Ajouter l'expérience
            $this->expManager->addExperience($userId, $rewards['xp']);

            // Créer une notification
            $stmt = $this->db->prepare("
                INSERT INTO notifications (
                    user_id, title, message, type
                ) VALUES (?, ?, ?, 'achievement')
            ");
            $stmt->execute([
                $userId,
                "Succès débloqué !",
                "Vous avez débloqué le succès : " . $achievement->getTitle()
            ]);

            $this->db->commit();

        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Erreur completion succès: " . $e->getMessage());
        }
    }

    private function updateProgress(int $userId, Achievement $achievement): void {
        $stmt = $this->db->prepare("
            UPDATE user_achievements 
            SET progress = ? 
            WHERE user_id = ? AND achievement_id = ?
        ");
        $stmt->execute([
            $achievement->getProgress(),
            $userId,
            $achievement->getId()
        ]);
    }
}
