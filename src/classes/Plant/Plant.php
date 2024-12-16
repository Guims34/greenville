<?php
namespace App\Classes\Plant;

class Plant {
    private int $id;
    private int $userId;
    private string $name;
    private string $strain;
    private int $stage = 1;
    private int $health = 100;
    private int $waterLevel = 100;
    private int $nutrientsLevel = 100;
    private float $phLevel = 6.5;
    private int $growthTime;
    private \DateTime $lastWatered;
    private \DateTime $lastFed;
    private \DateTime $nextFeeding;
    private \DateTime $createdAt;

    public function __construct(array $data) {
        $this->id = $data['id'] ?? 0;
        $this->userId = $data['user_id'];
        $this->name = $data['name'];
        $this->strain = $data['strain'];
        $this->growthTime = $data['growth_time'];
        $this->lastWatered = new \DateTime($data['last_watered'] ?? 'now');
        $this->createdAt = new \DateTime($data['created_at'] ?? 'now');
    }

    public function updateGrowth(): void {
        $age = $this->getAge();
        $this->stage = $this->calculateStage($age);
        $this->updateWaterLevel();
        $this->updateNutrientsLevel();
        $this->updateHealth();
    }

    public function water(): bool {
        if ($this->waterLevel >= 100) {
            return false;
        }
        
        $this->waterLevel = 100;
        $this->lastWatered = new \DateTime();
        return true;
    }

    public function feed(): bool {
        if ($this->nutrientsLevel >= 100) {
            return false;
        }

        $this->nutrientsLevel = 100;
        $this->lastFed = new \DateTime();
        $this->nextFeeding = (new \DateTime())->modify('+7 days');
        return true;
    }

    public function getProgress(): float {
        $age = $this->getAge();
        return min(100, ($age / $this->growthTime) * 100);
    }

    public function isReadyToHarvest(): bool {
        return $this->getProgress() >= 100;
    }

    private function getAge(): int {
        $now = new \DateTime();
        return $this->createdAt->diff($now)->days;
    }

    private function calculateStage(int $age): int {
        $progress = ($age / $this->growthTime) * 100;
        if ($progress < 25) return 1; // Germination
        if ($progress < 50) return 2; // Végétation
        if ($progress < 75) return 3; // Pré-floraison
        if ($progress < 100) return 4; // Floraison
        return 5; // Récolte
    }

    private function updateWaterLevel(): void {
        $hoursSinceWatered = (new \DateTime())->diff($this->lastWatered)->h;
        $this->waterLevel = max(0, $this->waterLevel - ($hoursSinceWatered * 2));
    }

    private function updateNutrientsLevel(): void {
        if (!$this->lastFed) return;
        
        $daysSinceFed = (new \DateTime())->diff($this->lastFed)->days;
        $this->nutrientsLevel = max(0, $this->nutrientsLevel - ($daysSinceFed * 5));
    }

    private function updateHealth(): void {
        // Facteurs affectant la santé
        if ($this->waterLevel < 30) {
            $this->health -= 5;
        }
        if ($this->nutrientsLevel < 30) {
            $this->health -= 3;
        }
        if ($this->phLevel < 5.5 || $this->phLevel > 7.5) {
            $this->health -= 2;
        }

        $this->health = max(0, min(100, $this->health));
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getName(): string { return $this->name; }
    public function getStrain(): string { return $this->strain; }
    public function getStage(): int { return $this->stage; }
    public function getHealth(): int { return $this->health; }
    public function getWaterLevel(): int { return $this->waterLevel; }
    public function getNutrientsLevel(): int { return $this->nutrientsLevel; }
    public function getPhLevel(): float { return $this->phLevel; }
}