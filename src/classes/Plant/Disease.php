<?php
namespace App\Classes\Plant;

class Disease {
    private int $id;
    private string $name;
    private string $description;
    private int $severity;
    private array $symptoms;
    private array $treatments;

    public function __construct(array $data) {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->description = $data['description'];
        $this->severity = $data['severity'];
        $this->symptoms = json_decode($data['symptoms'], true);
        $this->treatments = json_decode($data['treatments'], true);
    }

    public function getHealthImpact(): int {
        return $this->severity * 5;
    }

    public function canBeTreated(): bool {
        return !empty($this->treatments);
    }

    public function getTreatmentCost(): int {
        return $this->severity * 100;
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getDescription(): string { return $this->description; }
    public function getSeverity(): int { return $this->severity; }
    public function getSymptoms(): array { return $this->symptoms; }
    public function getTreatments(): array { return $this->treatments; }
}