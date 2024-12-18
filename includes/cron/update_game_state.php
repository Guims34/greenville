<?php
// Chemin absolu vers la racine du projet
define('ROOT_PATH', dirname(dirname(__DIR__)));

// Charger l'autoloader et les dépendances
require_once ROOT_PATH . '/src/autoload.php';
require_once ROOT_PATH . '/config/database.php';
require_once ROOT_PATH . '/includes/utils/GameTime.php';
require_once ROOT_PATH . '/includes/utils/GameScheduler.php';

use App\Classes\Plant\PlantManager;
use App\Classes\Plant\Environment\WeatherManager;
use App\Classes\Missions\MissionManager;

// Logger pour le suivi avec retour console
function logGameUpdate($message, $type = 'info') {
    $date = date('Y-m-d H:i:s');
    $prefix = match($type) {
        'success' => "✅",
        'error' => "❌",
        'warning' => "⚠️",
        default => "ℹ️"
    };
    
    $logMessage = "[$date] $prefix $message\n";
    
    // Écrire dans le fichier de log
    file_put_contents(ROOT_PATH . '/logs/game_update.log', $logMessage, FILE_APPEND);
    
    // Afficher dans la console
    echo $logMessage;
}

try {
    logGameUpdate("=== Début de la mise à jour du jeu ===\n");
    
    // Statistiques initiales
    $stats = [
        'plants_updated' => 0,
        'missions_refreshed' => 0,
        'events_completed' => 0
    ];

    // Mise à jour des plantes
    $plantManager = new PlantManager($db);
    $stmt = $db->query("SELECT COUNT(*) FROM plants WHERE growth_progress < 100");
    $totalPlants = $stmt->fetchColumn();
    
    $plantManager->updatePlants();
    $stats['plants_updated'] = $totalPlants;
    logGameUpdate("$totalPlants plantes mises à jour", 'success');

    $plantManager->updateEnvironment();
    logGameUpdate("Environnement mis à jour", 'success');


    // Rafraîchissement des missions
    if (GameScheduler::shouldRefreshMissions()) {
        $missionManager = new MissionManager($db);
        $stmt = $db->query("SELECT id FROM users");
        $count = 0;
        while ($user = $stmt->fetch()) {
            $missionManager->generateDailyMissions($user['id']);
            $count++;
        }
        $stats['missions_refreshed'] = $count;
        logGameUpdate("Missions rafraîchies pour $count utilisateurs", 'success');
    } else {
        logGameUpdate("Pas besoin de rafraîchir les missions maintenant", 'info');
    }

    // Mise à jour des événements de guilde
    $stmt = $db->prepare("
        SELECT COUNT(*) FROM guild_events 
        WHERE end_date <= NOW() AND event_type != 'completed'
    ");
    $stmt->execute();
    $eventsToComplete = $stmt->fetchColumn();

    if ($eventsToComplete > 0) {
        $stmt = $db->prepare("
            UPDATE guild_events 
            SET event_type = 'completed' 
            WHERE end_date <= NOW() AND event_type != 'completed'
        ");
        $stmt->execute();
        $stats['events_completed'] = $eventsToComplete;
        logGameUpdate("$eventsToComplete événements de guilde terminés", 'success');
    } else {
        logGameUpdate("Aucun événement de guilde à terminer", 'info');
    }

    // Calcul et affichage du temps de jeu
    $currentGameDay = GameTime::getCurrentGameDate();
    logGameUpdate("Jour de jeu actuel : $currentGameDay", 'info');

    // Résumé des statistiques
    logGameUpdate("\n=== Résumé de la mise à jour ===");
    logGameUpdate("Plantes mises à jour : {$stats['plants_updated']}");
    logGameUpdate("Missions rafraîchies : {$stats['missions_refreshed']}");
    logGameUpdate("Événements terminés : {$stats['events_completed']}");
    logGameUpdate("=== Fin de la mise à jour ===\n");

} catch (Exception $e) {
    logGameUpdate("ERREUR : " . $e->getMessage(), 'error');
    error_log("CRON ERROR: " . $e->getMessage());
}