<?php
class DashboardData {
    /**
     * Récupère les notifications non lues de l'utilisateur
     */
    public static function getNotifications($db, $userId) {
        try {
            $stmt = $db->prepare("
                SELECT * FROM notifications 
                WHERE user_id = ? AND is_read = FALSE 
                ORDER BY created_at DESC
                LIMIT 5
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des notifications: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les missions actives de l'utilisateur
     */
    public static function getMissions($db, $userId) {
        try {
            $stmt = $db->prepare("
                SELECT m.*, um.progress, um.completed, um.claimed
                FROM daily_missions m
                JOIN user_missions um ON m.id = um.mission_id
                WHERE um.user_id = ? AND um.expires_at > NOW()
                ORDER BY um.completed ASC, m.type ASC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des missions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère les statistiques de l'utilisateur
     */
    public static function getStats($db, $userId) {
        try {
            // Vérifier si les stats existent
            $stmt = $db->prepare("SELECT COUNT(*) FROM user_stats WHERE user_id = ?");
            $stmt->execute([$userId]);
            if ($stmt->fetchColumn() === 0) {
                // Créer les stats si elles n'existent pas
                self::initializeUserStats($db, $userId);
            }

            // Récupérer les stats
            $stmt = $db->prepare("SELECT * FROM user_stats WHERE user_id = ?");
            $stmt->execute([$userId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des statistiques: " . $e->getMessage());
            return [
                'plants_harvested' => 0,
                'trades_completed' => 0,
                'missions_completed' => 0,
                'achievements_completed' => 0
            ];
        }
    }

    /**
     * Récupère les plantes de l'utilisateur
     */
    public static function getPlants($db, $userId) {
        try {
            $stmt = $db->prepare("
                SELECT p.*, s.name as strain_name, s.type as strain_type
                FROM plants p
                JOIN strains s ON p.strain = s.id
                WHERE p.user_id = ?
                ORDER BY p.created_at DESC
            ");
            $stmt->execute([$userId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur lors de la récupération des plantes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Initialise les statistiques d'un nouvel utilisateur
     */
    private static function initializeUserStats($db, $userId) {
        try {
            $stmt = $db->prepare("
                INSERT INTO user_stats (
                    user_id,
                    plants_harvested,
                    trades_completed,
                    missions_completed,
                    achievements_completed,
                    missions_completed_today,
                    missions_completed_week,
                    created_at
                ) VALUES (?, 0, 0, 0, 0, 0, 0, NOW())
            ");
            $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("Erreur lors de l'initialisation des statistiques: " . $e->getMessage());
        }
    }

    /**
     * Met à jour les statistiques de l'utilisateur
     */
    public static function updateStats($db, $userId, $type, $value = 1) {
        try {
            $validColumns = [
                'plants_harvested',
                'trades_completed',
                'missions_completed',
                'achievements_completed',
                'missions_completed_today',
                'missions_completed_week'
            ];

            if (!in_array($type, $validColumns)) {
                throw new Exception("Type de statistique invalide");
            }

            $stmt = $db->prepare("
                UPDATE user_stats 
                SET $type = $type + ? 
                WHERE user_id = ?
            ");
            $stmt->execute([$value, $userId]);

            return true;
        } catch (Exception $e) {
            error_log("Erreur lors de la mise à jour des statistiques: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Réinitialise les statistiques quotidiennes
     */
    public static function resetDailyStats($db) {
        try {
            $db->exec("UPDATE user_stats SET missions_completed_today = 0");
            return true;
        } catch (PDOException $e) {
            error_log("Erreur lors de la réinitialisation des stats quotidiennes: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Réinitialise les statistiques hebdomadaires
     */
    public static function resetWeeklyStats($db) {
        try {
            $db->exec("UPDATE user_stats SET missions_completed_week = 0");
            return true;
        } catch (PDOException $e) {
            error_log("Erreur lors de la réinitialisation des stats hebdomadaires: " . $e->getMessage());
            return false;
        }
    }
}
