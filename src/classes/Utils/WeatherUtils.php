<?php
namespace App\Classes\Utils;

class WeatherUtils {
    public static function getHumidityImpact(float $humidity): float {
        // Impact de l'humidité sur la croissance
        if ($humidity < 30) {
            return 0.7; // Trop sec
        } elseif ($humidity > 70) {
            return 0.8; // Trop humide
        } else {
            return 1.0; // Optimal
        }
    }

    public static function getTemperatureImpact(float $temperature): float {
        // Impact de la température sur la croissance
        if ($temperature < 18) {
            return 0.7; // Trop froid
        } elseif ($temperature > 28) {
            return 0.8; // Trop chaud
        } else {
            return 1.0; // Optimal
        }
    }

    public static function calculateWaterLoss(float $temperature, float $humidity): float {
        // Calcul de la perte d'eau basé sur les conditions
        $baseLoss = 0.1; // 10% par jour
        $tempFactor = ($temperature - 20) * 0.02; // +2% par degré au-dessus de 20°C
        $humidityFactor = (50 - $humidity) * 0.01; // +1% par % en dessous de 50%
        
        return max(0, $baseLoss + $tempFactor + $humidityFactor);
    }
}
