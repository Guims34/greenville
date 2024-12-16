<?php
// Configuration de base
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../src/autoload.php';

// Démarrer la session
session_start();

// Initialiser les managers
use App\Classes\Plant\PlantManager;
use App\Classes\Missions\MissionManager;
use App\Classes\Progression\ProgressionManager;

$plantManager = new PlantManager($db);
$missionManager = new MissionManager($db);
$progressionManager = new ProgressionManager($db);
