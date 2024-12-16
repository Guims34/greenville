<?php
namespace App\Classes\Missions;

use PDO;
use GameTime;
use GameConstants;

class MissionManager {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function generateDailyMissions(int $userId): bool {
        try {
            $this->db->beginTransaction();

            // Supprimer les missions expirées en temps de jeu
            $stmt = $this->db->prepare("
                DELETE FROM user_missions 
                WHERE user_id = ? AND expires_at < NOW()
            ");
            $stmt->execute([$userId]);

            // Vérifier les missions actives
            $stmt = $this->db->prepare("
                SELECT COUNT(*) 
                FROM user_missions 
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

            // Calculer la date d'expiration en temps de jeu (24h réelles = 24 jours de jeu)
            $expiresAt = date('Y-m-d H:i:s', 
                strtotime('+' . GameConstants::MISSION_DURATION . ' hours')
            );

            // Créer les nouvelles missions
            $stmt = $this->db->prepare("
                INSERT INTO user_missions (
                    user_id, mission_id, started_at, expires_at, game_days_elapsed
                ) VALUES (?, ?, NOW(), ?, 0)
            ");

            foreach ($missions as $mission) {
                $stmt->execute([
                    $userId, 
                    $mission['id'],
                    $expiresAt
                ]);
            }

            $this->db->commit();
            return true;

        } catch (\PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Erreur génération missions: " . $e->getMessage());
            return false;
        }
    }

    public function getUserMissions(int $userId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT m.*, um.progress, um.completed, um.claimed, 
                       um.expires_at, um.started_at, um.game_days_elapsed
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

    public function updateMissionProgress(int $userId, string $type, int $value): void {
        try {
            // Récupérer les missions actives du type spécifié
            $stmt = $this->db->prepare("
                SELECT m.*, um.progress, um.mission_id, um.started_at,
                       um.game_days_elapsed
                FROM daily_missions m
                JOIN user_missions um ON m.id = um.mission_id
                WHERE um.user_id = ? AND m.type = ?
                AND um.completed = FALSE AND um.expires_at > NOW()
            ");
            $stmt->execute([$userId, $type]);
            
            while ($missionData = $stmt->fetch()) {
                $mission = new Mission($missionData);
                if ($mission->updateProgress($value)) {
                    // Mettre à jour la progression et les jours de jeu écoulés
                    $stmt2 = $this->db->prepare("
                        UPDATE user_missions 
                        SET progress = ?,
                            completed = TRUE,
                            game_days_elapsed = ?
                        WHERE user_id = ? AND mission_id = ?
                    ");
                    $stmt2->execute([
                        $value,
                        $mission->getGameDaysElapsed(),
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
