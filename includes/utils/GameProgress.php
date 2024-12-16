<?php
require_once 'GameConstants.php';
require_once 'GameTime.php';

class GameProgress {
    /**
     * Calcule la progression globale du joueur
     */
    public static function calculatePlayerProgress(array $stats): float {
        $totalPoints = 0;
        $maxPoints = 0;

        // Points pour les plantes
        $totalPoints += $stats['plants_harvested'] * 10;
        $maxPoints += 1000; // Max 100 plantes

        // Points pour les échanges
        $totalPoints += $stats['trades_completed'] * 5;
        $maxPoints += 500; // Max 100 échanges

        // Points pour les missions
        $totalPoints += $stats['missions_completed'] * 2;
        $maxPoints += 200; // Max 100 missions

        return min(100, ($totalPoints / $maxPoints) * 100);
    }

    /**
     * Calcule les récompenses journalières
     */
    public static function calculateDailyRewards(int $consecutiveDays): array {
        $baseCoins = 100;
        $baseXp = 50;
        $multiplier = min(5, 1 + ($consecutiveDays * 0.1)); // Max 5x après 40 jours

        return [
            'coins' => floor($baseCoins * $multiplier),
            'xp' => floor($baseXp * $multiplier),
            'premium_coins' => $consecutiveDays % 7 === 0 ? 1 : 0 // 1 pièce premium tous les 7 jours
        ];
    }
}