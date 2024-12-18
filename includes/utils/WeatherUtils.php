<?php
class WeatherUtils {
    public static function getWeatherIcon(string $type): string {
        return match($type) {
            'drought' => '☀️',
            'rain' => '🌧️',
            'heatwave' => '🌡️',
            'storm' => '⛈️',
            default => '🌤️'
        };
    }

    public static function getWeatherDescription(string $type): string {
        return match($type) {
            'drought' => 'Sécheresse',
            'rain' => 'Pluie',
            'heatwave' => 'Canicule',
            'storm' => 'Tempête',
            default => 'Normal'
        };
    }

    public static function getWeatherEffects(string $type): array {
        return match($type) {
            'drought' => [
                'water_loss' => 'Augmentée',
                'growth' => 'Ralentie'
            ],
            'rain' => [
                'water_gain' => 'Augmentée',
                'growth' => 'Normale'
            ],
            'heatwave' => [
                'water_loss' => 'Très augmentée',
                'growth' => 'Très ralentie'
            ],
            'storm' => [
                'water_gain' => 'Très augmentée',
                'damage' => 'Possible'
            ],
            default => []
        };
    }
}
