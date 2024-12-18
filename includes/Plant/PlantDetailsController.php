<?php
class PlantDetailsController {
    private $db;
    private $plant;
    private $strain;
    private $environment;

    public function __construct($db) {
        $this->db = $db;
    }

    public function loadPlant($plantId, $userId) {
        // Charger les détails de la plante avec les informations de la variété
        $stmt = $this->db->prepare("
            SELECT p.*, s.name as strain_name, s.type as strain_type,
                   s.difficulty, s.flowering_time_min, s.flowering_time_max,
                   s.description as strain_description
            FROM plants p
            JOIN strains s ON p.strain = s.id
            WHERE p.id = ? AND p.user_id = ?
            LIMIT 1
        ");
        
        $stmt->execute([$plantId, $userId]);
        $data = $stmt->fetch();

        if (!$data) {
            throw new Exception("Plante non trouvée");
        }

        $this->plant = new App\Classes\Plant\Plant($data);
        $this->strain = $data;
        
        // Charger l'environnement
        $this->loadEnvironment();
        
        return $this;
    }

    public function getPlantData() {
        // Calculer les statistiques
        $age = $this->calculateAge();
        $progress = $this->plant->getGrowthProgress();
        // Utiliser flowering_time_max du strain au lieu de getGrowthTime()
        $remainingDays = max(0, $this->strain['flowering_time_max'] - $age);

        return [
            'plant' => $this->plant,
            'strain' => $this->strain,
            'stats' => [
                'age' => $age,
                'progress' => $progress,
                'remaining_days' => $remainingDays,
                'health' => $this->plant->getHealth(),
                'water_level' => $this->plant->getWaterLevel(),
                'nutrients_level' => $this->plant->getNutrientsLevel(),
                'ph_level' => $this->plant->getPhLevel()
            ],
            'environment' => $this->environment
        ];
    }

    private function loadEnvironment() {
        // Charger les conditions environnementales
        $this->environment = [
            'humidity' => $this->plant->getAmbientHumidity(),
            'temperature' => $this->plant->getAmbientTemperature(),
            'irrigation_system' => $this->getIrrigationSystem(),
            'weather_events' => $this->getActiveWeatherEvents()
        ];
    }

    private function calculateAge() {
        $createdAt = $this->plant->getCreatedAt()->format('Y-m-d H:i:s');
        $now = new DateTime();
        return (new DateTime($createdAt))->diff($now)->days;
    }

    private function getIrrigationSystem() {
        $irrigationSystemId = $this->plant->getIrrigationSystemId();
        if (!$irrigationSystemId) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT * FROM irrigation_systems 
            WHERE id = ?
        ");
        $stmt->execute([$irrigationSystemId]);
        return $stmt->fetch();
    }

    private function getActiveWeatherEvents() {
        $stmt = $this->db->prepare("
            SELECT * FROM weather_events 
            WHERE active = TRUE 
            AND NOW() BETWEEN start_date AND end_date
            ORDER BY start_date DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
