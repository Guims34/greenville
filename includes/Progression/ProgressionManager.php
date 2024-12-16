<?php
namespace App\Classes\Progression;

use PDO;
use GameTime;
use TimeDisplay;

class ProgressionManager {
    // ... autres méthodes existantes ...

    public function getUserProgress(int $userId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT u.level, u.experience, u.created_at,
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

            // Ajouter les informations de temps de jeu
            $gameDays = GameTime::getGameDays(strtotime($progress['created_at']));
            $gameDate = TimeDisplay::formatGameDate($progress['created_at']);

            return [
                'level' => $progress['level'],
                'experience' => $progress['experience'],
                'progress_percentage' => min(100, max(0, $percentage)),
                'xp_current' => $currentXp,
                'xp_required' => $requiredXp,
                'game_days' => $gameDays,
                'game_date' => $gameDate
            ];

        } catch (\PDOException $e) {
            error_log("Erreur récupération progression: " . $e->getMessage());
            return [];
        }
    }
}