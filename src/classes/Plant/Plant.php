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
    private float $growthProgress = 0;
    private int $gameDays = 0;
    private \DateTime $lastWatered;
    private ?\DateTime $lastFed = null;
    private ?\DateTime $nextFeeding = null;
    private \DateTime $createdAt;
    private \DateTime $lastUpdate;
    private float $ambientHumidity = 50.0;
    private float $ambientTemperature = 20.0;
    private ?int $irrigationSystemId = null;

    public function __construct(array $data) {
        $this->id = $data['id'] ?? 0;
        $this->userId = $data['user_id'];
        $this->name = $data['name'];
        $this->strain = $data['strain'];
        $this->growthTime = $data['growth_time'];
        $this->growthProgress = $data['growth_progress'] ?? 0;
        $this->gameDays = $data['game_days'] ?? 0;
        $this->health = $data['health'] ?? 100;
        $this->waterLevel = $data['water_level'] ?? 100;
        $this->nutrientsLevel = $data['nutrients_level'] ?? 100;
        $this->phLevel = $data['ph_level'] ?? 6.5;
        $this->ambientHumidity = $data['ambient_humidity'] ?? 50.0;
        $this->ambientTemperature = $data['ambient_temperature'] ?? 20.0;
        $this->irrigationSystemId = $data['irrigation_system_id'] ?? null;
        
        // Initialisation des dates
        $this->lastWatered = new \DateTime($data['last_watered'] ?? 'now');
        $this->lastFed = isset($data['last_fed']) ? new \DateTime($data['last_fed']) : null;
        $this->nextFeeding = isset($data['next_feeding']) ? new \DateTime($data['next_feeding']) : null;
        $this->createdAt = new \DateTime($data['created_at'] ?? 'now');
        $this->lastUpdate = new \DateTime($data['last_update'] ?? 'now');
    }

    public function updateGrowth(): void {
        $newGameDays = $this->calculateGameDays();
        $daysElapsed = $newGameDays - $this->gameDays;
        
        if ($daysElapsed > 0) {
            $this->growthProgress = min(100, $this->growthProgress + 
                ($this->calculateDailyGrowth() * $daysElapsed));

            $this->waterLevel = max(0, $this->waterLevel - ($daysElapsed * 10));
            $this->nutrientsLevel = max(0, $this->nutrientsLevel - ($daysElapsed * 5));

            $this->gameDays = $newGameDays;
            $this->stage = $this->calculateStage();
            $this->updateHealth();
            $this->lastUpdate = new \DateTime();
        }
    }

    private function calculateGameDays(): int {
        $now = new \DateTime();
        return (int)($now->getTimestamp() - $this->lastUpdate->getTimestamp()) / 3600;
    }

    private function calculateDailyGrowth(): float {
        $baseGrowth = 100 / $this->growthTime;
        $healthMultiplier = $this->health / 100;
        $waterMultiplier = $this->waterLevel / 100;
        
        return $baseGrowth * $healthMultiplier * $waterMultiplier;
    }

    private function calculateStage(): int {
        if ($this->growthProgress < 25) return 1; // Germination
        if ($this->growthProgress < 50) return 2; // Végétation
        if ($this->growthProgress < 75) return 3; // Pré-floraison
        if ($this->growthProgress < 100) return 4; // Floraison
        return 5; // Récolte
    }

    private function updateHealth(): void {
        $waterPenalty = $this->waterLevel < 30 ? 5 : 0;
        $nutrientsPenalty = $this->nutrientsLevel < 30 ? 3 : 0;
        $phPenalty = ($this->phLevel < 5.5 || $this->phLevel > 7.5) ? 2 : 0;

        $this->health = max(0, min(100, $this->health - $waterPenalty - $nutrientsPenalty - $phPenalty));
    }

    // Getters de base
    public function getId(): int { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getName(): string { return $this->name; }
    public function getStrain(): string { return $this->strain; }
    public function getStage(): int { return $this->stage; }
    public function getHealth(): int { return $this->health; }
    public function getWaterLevel(): int { return $this->waterLevel; }
    public function getNutrientsLevel(): int { return $this->nutrientsLevel; }
    public function getPhLevel(): float { return $this->phLevel; }
    public function getGrowthProgress(): float { return $this->growthProgress; }
    public function getGameDays(): int { return $this->gameDays; }
    public function getLastUpdate(): \DateTime { return $this->lastUpdate; }
    
    // Getters pour les dates
    public function getCreatedAt(): \DateTime { return $this->createdAt; }
    public function getLastWatered(): \DateTime { return $this->lastWatered; }
    public function getLastFed(): ?\DateTime { return $this->lastFed; }
    public function getNextFeeding(): ?\DateTime { return $this->nextFeeding; }
    
    // Getters pour l'environnement
    public function getAmbientHumidity(): float { return $this->ambientHumidity; }
    public function getAmbientTemperature(): float { return $this->ambientTemperature; }
    public function getIrrigationSystemId(): ?int { return $this->irrigationSystemId; }

    // Setters pour l'environnement
    public function setAmbientHumidity(float $humidity): void {
        $this->ambientHumidity = max(0, min(100, $humidity));
    }

    public function setAmbientTemperature(float $temperature): void {
        $this->ambientTemperature = max(10, min(35, $temperature));
    }

    public function setIrrigationSystemId(?int $systemId): void {
        $this->irrigationSystemId = $systemId;
    }
}
