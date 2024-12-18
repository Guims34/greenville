<?php
namespace App\Classes\Plant\Environment;

class IrrigationSystem {
    private string $type;
    private float $efficiency;
    private int $capacity;
    private bool $isAutomatic;
    private float $waterLevel = 100.0;

    public function __construct(array $data) {
        $this->type = $data['type'];
        $this->efficiency = $data['efficiency'];
        $this->capacity = $data['capacity'];
        $this->isAutomatic = $data['is_automatic'];
    }

    public function canWater(): bool {
        return $this->waterLevel > 0;
    }

    public function water(): float {
        if (!$this->canWater()) return 0;

        $waterAmount = min($this->waterLevel, 100 * $this->efficiency);
        $this->waterLevel -= $waterAmount;
        return $waterAmount;
    }

    public function refill(float $amount): void {
        $this->waterLevel = min(100, $this->waterLevel + $amount);
    }

    public function shouldAutoWater(float $plantWaterLevel): bool {
        return $this->isAutomatic && $plantWaterLevel < 50 && $this->canWater();
    }
}