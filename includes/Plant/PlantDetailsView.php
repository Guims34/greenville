<?php
class PlantDetailsView {
    public function render(array $data): void {
        $plant = $data['plant'];
        $environment = $data['environment'];
        $irrigation = $data['irrigation'];
        $weatherEvents = $data['weatherEvents'];
        
        include 'templates/plant/details.php';
    }
}
