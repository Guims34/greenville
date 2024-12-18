<?php
namespace App\Classes\Plant\Environment;

class WeatherEvent {
    public const DROUGHT = 'drought';
    public const RAIN = 'rain';
    public const HEATWAVE = 'heatwave';
    public const STORM = 'storm';

    private string $type;
    private int $duration;
    private float $intensity;
    private array $effects;

    public function __construct(string $type, int $duration, float $intensity) {
        $this->type = $type;
        $this->duration = $duration;
        $this->intensity = $intensity;
        $this->effects = $this->calculateEffects();
    }

    private function calculateEffects(): array {
        return match($this->type) {
            self::DROUGHT => [
                'water_loss' => 0.2 * $this->intensity,
                'health_impact' => -0.1 * $this->intensity
            ],
            self::RAIN => [
                'water_gain' => 0.3 * $this->intensity,
                'health_impact' => 0.1 * $this->intensity
            ],
            self::HEATWAVE => [
                'water_loss' => 0.3 * $this->intensity,
                'growth_speed' => -0.2 * $this->intensity
            ],
            self::STORM => [
                'water_gain' => 0.4 * $this->intensity,
                'health_impact' => -0.2 * $this->intensity
            ],
            default => []
        };
    }

    public function getEffects(): array {
        return $this->effects;
    }

    public function getDuration(): int {
        return $this->duration;
    }
}