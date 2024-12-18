<?php
namespace App\Classes\Plant\Environment;

class WeatherManager {
    private array $activeEvents = [];
    private float $baseHumidity = 50.0;
    private float $baseTemperature = 20.0;

    public function generateRandomEvent(): ?WeatherEvent {
        if (rand(1, 100) > 20) return null; // 20% de chance d'événement

        $types = [
            WeatherEvent::DROUGHT,
            WeatherEvent::RAIN,
            WeatherEvent::HEATWAVE,
            WeatherEvent::STORM
        ];

        $type = $types[array_rand($types)];
        $duration = rand(1, 5); // 1-5 jours
        $intensity = rand(5, 10) / 10; // 0.5-1.0 intensité

        return new WeatherEvent($type, $duration, $intensity);
    }

    public function addEvent(WeatherEvent $event): void {
        $this->activeEvents[] = $event;
    }

    public function updateEnvironment(): array {
        $humidity = $this->baseHumidity;
        $temperature = $this->baseTemperature;

        foreach ($this->activeEvents as $event) {
            $effects = $event->getEffects();
            if (isset($effects['humidity_modifier'])) {
                $humidity += $effects['humidity_modifier'];
            }
            if (isset($effects['temperature_modifier'])) {
                $temperature += $effects['temperature_modifier'];
            }
        }

        return [
            'humidity' => max(0, min(100, $humidity)),
            'temperature' => max(10, min(35, $temperature))
        ];
    }
}