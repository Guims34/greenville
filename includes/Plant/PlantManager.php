<?php
namespace App\Classes\Plant;

use PDO;
use GameTime;
use GrowthCalculator;

class PlantManager {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function createPlant(array $data): ?Plant {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO plants (
                    user_id, name, strain, growing_method_id, growing_medium_id,
                    growth_time, growth_progress, game_days, created_at, last_update
                ) VALUES (?, ?, ?, ?, ?, ?, 0, 0, NOW(), NOW())
            ");

            $stmt->execute([
                $data['user_id'],
                $data['name'],
                $data['strain'],
                $data['growing_method_id'],
                $data['growing_medium_id'],
                $data['growth_time']
            ]);

            $data['id'] = $this->db->lastInsertId();
            return new Plant($data);
        } catch (\PDOException $e) {
            error_log("Erreur création plante: " . $e->getMessage());
            return null;
        }
    }

    public function updatePlants(): void {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM plants 
                WHERE growth_progress < 100
            ");
            $stmt->execute();
            
            while ($plantData = $stmt->fetch()) {
                $plant = new Plant($plantData);
                $plant->updateGrowth();
                
                $this->db->prepare("
                    UPDATE plants 
                    SET stage = ?,
                        health = ?,
                        water_level = ?,
                        nutrients_level = ?,
                        growth_progress = ?,
                        game_days = ?,
                        last_update = NOW()
                    WHERE id = ?
                ")->execute([
                    $plant->getStage(),
                    $plant->getHealth(),
                    $plant->getWaterLevel(),
                    $plant->getNutrientsLevel(),
                    $plant->getGrowthProgress(),
                    $plant->getGameDays(),
                    $plant->getId()
                ]);
            }
        } catch (\PDOException $e) {
            error_log("Erreur mise à jour plantes: " . $e->getMessage());
        }
    }

    public function getPlant(int $id): ?Plant {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM plants WHERE id = ?
            ");
            $stmt->execute([$id]);
            
            if ($data = $stmt->fetch()) {
                return new Plant($data);
            }
            return null;
        } catch (\PDOException $e) {
            error_log("Erreur récupération plante: " . $e->getMessage());
            return null;
        }
    }

    public function getUserPlants(int $userId): array {
        try {
            $stmt = $this->db->prepare("
                SELECT p.*, s.name as strain_name, s.type as strain_type
                FROM plants p
                JOIN strains s ON p.strain = s.id
                WHERE p.user_id = ?
                ORDER BY p.created_at DESC
            ");
            $stmt->execute([$userId]);
            
            $plants = [];
            while ($data = $stmt->fetch()) {
                $plants[] = new Plant($data);
            }
            return $plants;
        } catch (\PDOException $e) {
            error_log("Erreur récupération plantes utilisateur: " . $e->getMessage());
            return [];
        }
    }

    public function harvestPlant(Plant $plant): array {
        try {
            $this->db->beginTransaction();

            // Calculer les récompenses basées sur le temps de jeu
            $rewards = $this->calculateHarvestRewards($plant);

            // Mettre à jour l'utilisateur
            $stmt = $this->db->prepare("
                UPDATE users 
                SET coins = coins + ?,
                    experience = experience + ?
                WHERE id = ?
            ");
            $stmt->execute([
                $rewards['coins'],
                $rewards['xp'],
                $plant->getUserId()
            ]);

            // Supprimer la plante
            $stmt = $this->db->prepare("DELETE FROM plants WHERE id = ?");
            $stmt->execute([$plant->getId()]);

            // Mettre à jour les statistiques
            $stmt = $this->db->prepare("
                UPDATE user_stats 
                SET plants_harvested = plants_harvested + 1,
                    total_yield = total_yield + ?
                WHERE user_id = ?
            ");
            $stmt->execute([$rewards['yield'], $plant->getUserId()]);

            $this->db->commit();
            return $rewards;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Erreur récolte plante: " . $e->getMessage());
            return [];
        }
    }

    private function calculateHarvestRewards(Plant $plant): array {
        $baseReward = 1000;
        $healthMultiplier = $plant->getHealth() / 100;
        $timeMultiplier = min(1.5, $plant->getGameDays() / GameConstants::BASE_GROWTH_TIME);
        
        return [
            'coins' => floor($baseReward * $healthMultiplier * $timeMultiplier),
            'xp' => floor(100 * $healthMultiplier * $timeMultiplier),
            'yield' => floor(500 * $healthMultiplier * $timeMultiplier)
        ];
    }
}
