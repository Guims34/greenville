<?php
namespace App\Classes\Missions;

use PDO;

class MissionManager {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function generateDailyMissions(int $userId): bool {
        try {
            $this->db->beginTransaction();

            // Supprimer les missions expirées
            $stmt = $this->db->prepare("
                DELETE FROM user_missions 
                WHERE user_id = ? AND expires_at < NOW()
            ");
            $stmt->execute([$userId]);

            // Vérifier si l'utilisateur a déjà des missions actives
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM user_missions 
                WHERE user_id = ? AND expires_at > NOW()
            ");
            $stmt->execute([$userId]);
            
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
                INSERT INTO user_missions (
                    user_id, mission_id, expires_at
                ) VALUES (
                    ?, ?, DATE_ADD(NOW(), INTERVAL 1 DAY)
                )
            ");

            foreach ($missions as $mission) {
                $stmt->execute([$userId, $mission['id']]);
            }

            $this->db->commit();
            return true;

        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Erreur génération missions: " . $e->getMessage());
            return false;
        }
    }

    public function getUserMissions(int $userId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT m.*, um.progress, um.completed, um.claimed, um.expires_at
                FROM daily_missions m
                JOIN user_missions um ON m.id = um.mission_id
                WHERE um.user_id = ? AND um.expires_at > NOW()
                ORDER BY um.completed ASC, m.type ASC
            ");
            $stmt->execute([$userId]);
            
            $missions = [];
            while ($data = $stmt->fetch()) {
                $missions[] = new Mission($data);
            }
            return $missions;

        } catch (\PDOException $e) {
            error_log("Erreur récupération missions: " . $e->getMessage());
            return [];
        }
    }

    public function claimReward(int $userId, int $missionId): array {
        try {
            $this->db->beginTransaction();

            // Vérifier la mission
            $stmt = $this->db->prepare("
                SELECT m.*, um.completed, um.claimed
                FROM daily_missions m
                JOIN user_missions um ON m.id = um.mission_id
                WHERE m.id = ? AND um.user_id = ?
                AND um.completed = TRUE AND um.claimed = FALSE
            ");
            $stmt->execute([$missionId, $userId]);
            $mission = $stmt->fetch();

            if (!$mission) {
                throw new \Exception("Mission non disponible");
            }

            // Donner les récompenses
            $stmt = $this->db->prepare("
                UPDATE users 
                SET coins = coins + ?,
                    experience = experience + ?
                WHERE id = ?
            ");
            $stmt->execute([
                $mission['coins_reward'],
                $mission['xp_reward'],
                $userId
            ]);

            // Marquer comme réclamée
            $stmt = $this->db->prepare("
                UPDATE user_missions 
                SET claimed = TRUE 
                WHERE mission_id = ? AND user_id = ?
            ");
            $stmt->execute([$missionId, $userId]);

            // Mettre à jour les statistiques
            $stmt = $this->db->prepare("
                UPDATE user_stats 
                SET missions_completed = missions_completed + 1,
                    missions_completed_today = missions_completed_today + 1,
                    missions_completed_week = missions_completed_week + 1 WHERE id = ?
            ");
            $stmt->execute([$userId]);

            $this->db->commit();
            return [
                'success' => true,
                'rewards' => [
                    'coins' => $mission['coins_reward'],
                    'xp' => $mission['xp_reward']
                ]
            ];

        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Erreur réclamation récompense: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateMissionProgress(int $userId, string $type, int $value): void {
        try {
            // Récupérer les missions actives du type spécifié
            $stmt = $this->db->prepare("
                SELECT m.*, um.progress, um.mission_id
                FROM daily_missions m
                JOIN user_missions um ON m.id = um.mission_id
                WHERE um.user_id = ? AND m.type = ?
                AND um.completed = FALSE AND um.expires_at > NOW()
            ");
            $stmt->execute([$userId, $type]);
            
            while ($missionData = $stmt->fetch()) {
                $mission = new Mission($missionData);
                if ($mission->updateProgress($value)) {
                    // Mettre à jour la progression
                    $stmt2 = $this->db->prepare("
                        UPDATE user_missions 
                        SET progress = ?,
                            completed = TRUE 
                        WHERE user_id = ? AND mission_id = ?
                    ");
                    $stmt2->execute([
                        $value,
                        $userId,
                        $missionData['mission_id']
                    ]);
                }
            }
        } catch (\PDOException $e) {
            error_log("Erreur mise à jour progression mission: " . $e->getMessage());
        }
    }
}