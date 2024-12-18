<?php
namespace App\Classes\Plant;

use PDO;
use App\Classes\Plant\Environment\WeatherManager;

class PlantManager {
    private PDO $db;
    private WeatherManager $weatherManager;

    public function __construct(PDO $db) {
        $this->db = $db;
        $this->weatherManager = new WeatherManager();
    }

    public function updateEnvironment(): void {
    $weatherManager = new WeatherManager();
    
    // Générer des événements aléatoires
    $event = $weatherManager->generateRandomEvent();
    if ($event) {
        $this->db->prepare("
            INSERT INTO weather_events (type, intensity, duration, end_date) 
            VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL ? DAY))
        ")->execute([
            $event->getType(),
            $event->getIntensity(),
            $event->getDuration(),
            $event->getDuration()
        ]);
        
        logGameUpdate("Nouvel événement météo : " . $event->getType());
    }
    
    // Mettre à jour l'environnement pour toutes les plantes
    $environment = $weatherManager->updateEnvironment();
    $this->db->prepare("
        UPDATE plants 
        SET ambient_humidity = ?,
            ambient_temperature = ?
    ")->execute([
        $environment['humidity'],
        $environment['temperature']
    ]);
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
}
