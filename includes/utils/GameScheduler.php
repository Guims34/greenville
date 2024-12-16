<?php
require_once 'GameConstants.php';
require_once 'GameTime.php';

class GameScheduler {
    /**
     * Vérifie si c'est l'heure de rafraîchir les missions
     */
    public static function shouldRefreshMissions(): bool {
        $currentHour = (int)date('G');
        return $currentHour === GameConstants::MISSION_REFRESH_HOUR;
    }

    /**
     * Calcule la prochaine date de rafraîchissement
     */
    public static function getNextRefreshDate(): string {
        $now = time();
        $tomorrow = strtotime('tomorrow');
        $nextRefresh = strtotime(date('Y-m-d', $tomorrow) . ' ' . GameConstants::MISSION_REFRESH_HOUR . ':00:00');
        
        if ($now > $nextRefresh) {
            $nextRefresh = strtotime('+1 day', $nextRefresh);
        }
        
        return date('Y-m-d H:i:s', $nextRefresh);
    }

    /**
     * Calcule le temps restant avant le prochain rafraîchissement
     */
    public static function getTimeUntilNextRefresh(): int {
        $nextRefresh = strtotime(self::getNextRefreshDate());
        return max(0, $nextRefresh - time());
    }
}