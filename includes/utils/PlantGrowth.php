<?php
require_once 'GameTime.php';

class PlantGrowth {
    /**
     * Calcule la progression d'une plante
     */
    public static function calculateProgress(string $plantedAt): float {
        $plantAge = GameTime::getGameDays(strtotime($plantedAt));
        return min(100, ($plantAge / 60) * 100); // 60 jours de jeu pour une croissance complète
    }

    /**
     * Vérifie si une plante a besoin d'eau
     */
    public static function needsWater(string $lastWateredAt): bool {
        $daysSinceWatered = GameTime::getGameDays(strtotime($lastWateredAt));
        return $daysSinceWatered >= 1; // Besoin d'eau tous les jours de jeu
    }

    /**
     * Vérifie si une plante est prête à être récoltée
     */
    public static function isReadyToHarvest(string $plantedAt): bool {
        return self::calculateProgress($plantedAt) >= 100;
    }
}