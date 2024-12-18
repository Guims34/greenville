<?php
requireAuth();
require_once 'includes/components/dashboard/Notifications.php';
require_once 'includes/components/dashboard/Statistics.php';
require_once 'includes/components/dashboard/PlantsList.php';
require_once 'includes/components/dashboard/MissionsList.php';
require_once 'includes/data/DashboardData.php';

// Récupérer les données
$notifications = DashboardData::getNotifications($db, $_SESSION['user_id']);
$missions = DashboardData::getMissions($db, $_SESSION['user_id']);
$stats = DashboardData::getStats($db, $_SESSION['user_id']);
$plants = DashboardData::getPlants($db, $_SESSION['user_id']);

// Créer les instances des composants
$missionsList = MissionsList::create($missions);
?>

<div class="container mx-auto px-4 py-8">
    <?php echo Notifications::render($notifications); ?>
    
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <?php 
            echo Statistics::render($stats);
            echo PlantsList::render($plants);
            ?>
        </div>
        <div>
            <?php echo $missionsList->render(); ?>
        </div>
    </div>
</div>

<script src="/js/dashboard.js"></script>
<script src="includes/components/dashboard/js/missions.js"></script>