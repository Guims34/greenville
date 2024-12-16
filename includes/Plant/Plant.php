<?php
namespace App\Classes\Plant;

use GameTime;
use GrowthCalculator;
use GameConstants;

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
    private \DateTime $lastFed;
    private \DateTime $nextFeeding;
    private \DateTime $createdAt;
    private \DateTime $lastUpdate;

    public function __construct(array $data) {
        $this->id = $data['id'] ?? 0;
        $this->userId = $data['user_id'];
        $this->name = $data['name'];
        $this->strain = $data['strain'];
        $this->growthTime = $data['growth_time'];
        $this->growthProgress = $data['growth_progress'] ?? 0;
        $this->gameDays = $data['game_days'] ?? 0;
        $this->lastWatered = new \DateTime($data['last_watered'] ?? 'now');
        $this->createdAt = new \DateTime($data['created_at'] ?? 'now');
        $this->lastUpdate = new \DateTime($data['last_update'] ?? 'now');
    }

    public function updateGrowth(): void {
        // Calculer les jours de jeu écoulés depuis la dernière mise à jour
        $newGameDays = GameTime::getGameDays(strtotime($this->lastUpdate->format('Y-m-d H:i:s')));
        $daysElapsed = $newGameDays - $this->gameDays;
        
        if ($daysElapsed > 0) {
            // Mettre à jour la progression
            $this->growthProgress = min(100, $this->growthProgress + 
                (GrowthCalculator::calculateDailyGrowth([
                    'health' => $this->health,
                    'water_level' => $this->waterLevel,
                    'growth_time' => $this->growthTime
                ]) * $daysElapsed));

            // Mettre à jour les niveaux d'eau et de nutriments
            $this->waterLevel = max(0, $this->waterLevel - 
                ($daysElapsed * GameConstants::WATER_LOSS_PER_DAY));
            $this->nutrientsLevel = max(0, $this->nutrientsLevel - 
                ($daysElapsed * GameConstants::NUTRIENT_LOSS_PER_DAY));

            $this->gameDays = $newGameDays;
            $this->stage = $this->calculateStage();
            $this->updateHealth();
            $this->lastUpdate = new \DateTime();
        }
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

    public function water(): bool {
        if ($this->waterLevel >= 100) return false;
        
        $this->waterLevel = 100;
        $this->lastWatered = new \DateTime();
        return true;
    }

    public function feed(): bool {
        if ($this->nutrientsLevel >= 100) return false;

        $this->nutrientsLevel = 100;
        $this->lastFed = new \DateTime();
        $this->nextFeeding = (new \DateTime())->modify('+' . GameConstants::NUTRIENT_CYCLE_DAYS . ' days');
        return true;
    }

    public function isReadyToHarvest(): bool {
        return $this->growthProgress >= 100;
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
    public function getGrowthProgress(): float { return $this->growthProgress; }
    public function getGameDays(): int { return $this->gameDays; }
    public function getLastUpdate(): \DateTime { return $this->lastUpdate; }
}
