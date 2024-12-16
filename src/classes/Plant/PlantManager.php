<?php
namespace App\Classes\Plant;

use PDO;

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
                    growth_time, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, NOW())
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

    public function updatePlant(Plant $plant): bool {
        try {
            $stmt = $this->db->prepare("
                UPDATE plants SET
                    stage = ?,
                    health = ?,
                    water_level = ?,
                    nutrients_level = ?,
                    ph_level = ?,
                    last_watered = ?,
                    last_fed = ?,
                    next_feeding = ?
                WHERE id = ?
            ");

            return $stmt->execute([
                $plant->getStage(),
                $plant->getHealth(),
                $plant->getWaterLevel(),
                $plant->getNutrientsLevel(),
                $plant->getPhLevel(),
                $plant->getLastWatered()->format('Y-m-d H:i:s'),
                $plant->getLastFed()?->format('Y-m-d H:i:s'),
                $plant->getNextFeeding()?->format('Y-m-d H:i:s'),
                $plant->getId()
            ]);
        } catch (\PDOException $e) {
            error_log("Erreur mise à jour plante: " . $e->getMessage());
            return false;
        }
    }

    public function harvestPlant(Plant $plant): array {
        try {
            $this->db->beginTransaction();

            // Calculer les récompenses
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
        
        return [
            'coins' => floor($baseReward * $healthMultiplier),
            'xp' => floor(100 * $healthMultiplier),
            'yield' => floor(500 * $healthMultiplier)
        ];
    }
}