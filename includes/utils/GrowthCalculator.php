<?php
require_once 'GameTime.php';

class GrowthCalculator {
    /**
     * Calcule la croissance quotidienne
     */
    public static function calculateDailyGrowth(array $plant): float {
        $baseGrowth = 100 / $plant['growth_time']; // Croissance de base par jour de jeu
        $healthMultiplier = $plant['health'] / 100;
        $waterMultiplier = $plant['water_level'] / 100;
        
        return $baseGrowth * $healthMultiplier * $waterMultiplier;
    }

    /**
     * Calcule la perte d'eau quotidienne
     */
    public static function calculateWaterLoss(string $lastWatered): float {
        $gameDaysSinceWatered = GameTime::getGameDays(strtotime($lastWatered));
        return min(100, $gameDaysSinceWatered * 10); // Perte de 10% par jour de jeu
    }

    /**
     * Calcule la santé en fonction du temps
     */
    public static function calculateHealth(array $plant): int {
        $waterPenalty = max(0, (70 - $plant['water_level']) / 2);
        $nutrientsPenalty = max(0, (70 - $plant['nutrients_level']) / 2);
        
        return max(0, min(100, $plant['health'] - $waterPenalty - $nutrientsPenalty));
    }
}