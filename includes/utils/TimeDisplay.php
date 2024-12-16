<?php
require_once 'GameTime.php';

class TimeDisplay {
    /**
     * Formate une durée en jours de jeu
     */
    public static function formatGameDuration(int $gameDays): string {
        if ($gameDays < 1) {
            return "Moins d'un jour";
        }
        if ($gameDays === 1) {
            return "1 jour";
        }
        return "$gameDays jours";
    }

    /**
     * Formate une date de jeu
     */
    public static function formatGameDate(string $date): string {
        $timestamp = strtotime($date);
        $gameDays = GameTime::getGameDays($timestamp);
        $gameDate = date('d/m/Y', strtotime("+$gameDays days", strtotime('2024-01-01')));
        return "Jour $gameDays ($gameDate)";
    }

    /**
     * Affiche le temps restant
     */
    public static function formatRemainingTime(string $targetDate): string {
        $remainingDays = GameTime::getRemainingGameDays(strtotime($targetDate));
        return self::formatGameDuration($remainingDays);
    }
}