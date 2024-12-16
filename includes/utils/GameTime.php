<?php
class GameTime {
    // 1 heure réelle = 1 jour en jeu
    private const REAL_HOUR_TO_GAME_DAY = 1;
    private const SECONDS_PER_HOUR = 3600;
    private const GAME_START = '2024-01-01';
    
    /**
     * Convertit un timestamp réel en jours de jeu
     */
    public static function getGameDays(int $realTimestamp): int {
        $startTimestamp = strtotime(self::GAME_START);
        $hoursSinceStart = floor((time() - $startTimestamp) / self::SECONDS_PER_HOUR);
        return max(0, $hoursSinceStart * self::REAL_HOUR_TO_GAME_DAY);
    }

    /**
     * Obtient la date actuelle du jeu
     */
    public static function getCurrentGameDate(): string {
        $startTimestamp = strtotime(self::GAME_START);
        $gameDaysSinceStart = self::getGameDays($startTimestamp);
        return date('Y-m-d', strtotime("+$gameDaysSinceStart days", $startTimestamp));
    }

    /**
     * Convertit une durée réelle en jours de jeu
     */
    public static function realToGameDays(int $realHours): int {
        return $realHours * self::REAL_HOUR_TO_GAME_DAY;
    }

    /**
     * Convertit des jours de jeu en heures réelles
     */
    public static function gameDaysToRealHours(int $gameDays): int {
        return $gameDays / self::REAL_HOUR_TO_GAME_DAY;
    }

    /**
     * Calcule le temps restant en jours de jeu
     */
    public static function getRemainingGameDays(int $targetTimestamp): int {
        $realHoursRemaining = ($targetTimestamp - time()) / self::SECONDS_PER_HOUR;
        return ceil($realHoursRemaining * self::REAL_HOUR_TO_GAME_DAY);
    }
}