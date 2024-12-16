<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils/autoload.php';
require_once __DIR__ . '/../Plant/PlantManager.php';
require_once __DIR__ . '/../Missions/MissionManager.php';

// Logger pour le suivi
function logGameUpdate($message) {
    $date = date('Y-m-d H:i:s');
    $logMessage = "[$date] $message\n";
    file_put_contents(__DIR__ . '/../../logs/game_update.log', $logMessage, FILE_APPEND);
}

try {
    // Mise à jour des plantes
    $plantManager = new PlantManager($db);
    $plantManager->updatePlants();
    logGameUpdate("Plantes mises à jour");

    // Rafraîchissement des missions
    if (GameScheduler::shouldRefreshMissions()) {
        $missionManager = new MissionManager($db);
        $stmt = $db->query("SELECT id FROM users WHERE status = 'active'");
        $count = 0;
        while ($user = $stmt->fetch()) {
            $missionManager->generateDailyMissions($user['id']);
            $count++;
        }
        logGameUpdate("Missions rafraîchies pour $count utilisateurs");
    }

    // Mise à jour des événements
    $stmt = $db->prepare("
        UPDATE guild_events 
        SET status = 'completed' 
        WHERE end_date <= NOW() AND status = 'active'
    ");
    $stmt->execute();
    logGameUpdate("Événements mis à jour");

    // Calcul du temps de jeu actuel
    $currentGameDay = GameTime::getCurrentGameDate();
    logGameUpdate("Jour de jeu actuel : $currentGameDay");

} catch (Exception $e) {
    logGameUpdate("ERREUR : " . $e->getMessage());
}