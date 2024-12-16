<?php
namespace App\Classes\Plant;

class Environment {
    private float $temperature;
    private float $humidity;
    private float $co2Level;
    private float $airflow;
    private array $optimalRanges;

    public function __construct(array $data) {
        $this->temperature = $data['temperature'] ?? 20.0;
        $this->humidity = $data['humidity'] ?? 50.0;
        $this->co2Level = $data['co2_level'] ?? 400.0;
        $this->airflow = $data['airflow'] ?? 1.0;
        
        $this->optimalRanges = [
            'temperature' => ['min' => 20, 'max' => 28],
            'humidity' => ['min' => 40, 'max' => 60],
            'co2_level' => ['min' => 350, 'max' => 1500],
            'airflow' => ['min' => 0.5, 'max' => 2.0]
        ];
    }

    public function calculateGrowthMultiplier(): float {
        $multipliers = [
            $this->getTemperatureMultiplier(),
            $this->getHumidityMultiplier(),
            $this->getCo2Multiplier(),
            $this->getAirflowMultiplier()
        ];

        return array_reduce($multipliers, fn($a, $b) => $a * $b, 1.0);
    }

    private function getTemperatureMultiplier(): float {
        return $this->calculateRangeMultiplier(
            $this->temperature,
            $this->optimalRanges['temperature']['min'],
            $this->optimalRanges['temperature']['max']
        );
    }

    private function getHumidityMultiplier(): float {
        return $this->calculateRangeMultiplier(
            $this->humidity,
            $this->optimalRanges['humidity']['min'],
            $this->optimalRanges['humidity']['max']
        );
    }

    private function getCo2Multiplier(): float {
        return $this->calculateRangeMultiplier(
            $this->co2Level,
            $this->optimalRanges['co2_level']['min'],
            $this->optimalRanges['co2_level']['max']
        );
    }

    private function getAirflowMultiplier(): float {
        return $this->calculateRangeMultiplier(
            $this->airflow,
            $this->optimalRanges['airflow']['min'],
            $this->optimalRanges['airflow']['max']
        );
    }

    private function calculateRangeMultiplier(float $value, float $min, float $max): float {
        if ($value < $min) {
            $diff = $min - $value;
            return max(0.5, 1 - ($diff / $min) * 0.5);
        }
        if ($value > $max) {
            $diff = $value - $max;
            return max(0.5, 1 - ($diff / $max) * 0.5);
        }
        return 1.0;
    }

    // Getters et Setters
    public function getTemperature(): float { return $this->temperature; }
    public function getHumidity(): float { return $this->humidity; }
    public function getCo2Level(): float { return $this->co2Level; }
    public function getAirflow(): float { return $this->airflow; }

    public function setTemperature(float $value): void { $this->temperature = $value; }
    public function setHumidity(float $value): void { $this->humidity = $value; }
    public function setCo2Level(float $value): void { $this->co2Level = $value; }
    public function setAirflow(float $value): void { $this->airflow = $value; }
}