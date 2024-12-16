<?php
require_once 'GameTime.php';

class EventTime {
    /**
     * Calcule la durée d'un événement en jours de jeu
     */
    public static function calculateEventDuration(string $startDate, string $endDate): int {
        $startTimestamp = strtotime($startDate);
        $endTimestamp = strtotime($endDate);
        return GameTime::getGameDays($startTimestamp) - GameTime::getGameDays($endTimestamp);
    }

    /**
     * Vérifie si un événement est actif
     */
    public static function isEventActive(string $startDate, string $endDate): bool {
        $now = time();
        return $now >= strtotime($startDate) && $now <= strtotime($endDate);
    }

    /**
     * Calcule la date de fin d'un événement
     */
    public static function calculateEventEnd(int $durationInGameDays): string {
        $realHours = GameTime::gameDaysToRealHours($durationInGameDays);
        return date('Y-m-d H:i:s', strtotime("+{$realHours} hours"));
    }
}