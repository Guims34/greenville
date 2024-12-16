<?php
require_once 'GameTime.php';

class MissionTime {
    /**
     * Vérifie si une mission est expirée
     */
    public static function isMissionExpired(string $expiresAt): bool {
        return time() >= strtotime($expiresAt);
    }

    /**
     * Calcule la date d'expiration d'une mission (24h réelles = 24 jours de jeu)
     */
    public static function calculateMissionExpiration(): string {
        return date('Y-m-d H:i:s', strtotime('+24 hours'));
    }

    /**
     * Obtient le temps restant en jours de jeu
     */
    public static function getRemainingTime(string $expiresAt): int {
        return GameTime::getRemainingGameDays(strtotime($expiresAt));
    }
}