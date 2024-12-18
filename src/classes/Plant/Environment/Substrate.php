
<?php
namespace App\Classes\Plant\Environment;

class Substrate {
    private string $id;
    private string $type;
    private float $waterRetention;
    private float $aeration;
    private float $phStability;

    public function __construct(array $data) {
        $this->id = $data['id'];
        $this->type = $data['type'];
        $this->waterRetention = $data['water_retention'];
        $this->aeration = $data['aeration'];
        $this->phStability = $data['ph_stability'];
    }

    public function getWaterLossModifier(): float {
        // Plus la rétention est élevée, moins la perte d'eau est importante
        return 1 - ($this->waterRetention * 0.5);
    }

    public function getAerationBonus(): float {
        // Bonus de croissance basé sur l'aération
        return 1 + ($this->aeration * 0.2);
    }

    public function getPhStabilityModifier(): float {
        // Influence sur la stabilité du pH
        return $this->phStability;
    }
}
