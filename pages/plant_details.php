<?php
require_once 'includes/Plant/PlantDetailsController.php';
require_once 'includes/auth.php';

// Vérifier l'authentification
requireAuth();

// Vérifier si l'ID de la plante est fourni
if (!isset($_GET['id'])) {
    header('Location: index.php?page=dashboard');
    exit;
}

$plant_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

try {
    $controller = new PlantDetailsController($db);
    $data = $controller->loadPlant($plant_id, $_SESSION['user_id'])->getPlantData();
    
    // Extraire les données
    $plant = $data['plant'];
    $strain = $data['strain'];
    $stats = $data['stats'];
    $environment = $data['environment'];

} catch (Exception $e) {
    $_SESSION['error_message'] = $e->getMessage();
    header('Location: index.php?page=dashboard');
    exit;
}

// Inclure la vue
include 'templates/plant_details_view.php';
