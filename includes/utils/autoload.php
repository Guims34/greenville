<?php
spl_autoload_register(function ($class) {
    $prefix = '';
    $base_dir = __DIR__ . '/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Charger les classes utilitaires
require_once __DIR__ . '/GameTime.php';
require_once __DIR__ . '/GameConstants.php';
require_once __DIR__ . '/TimeDisplay.php';
require_once __DIR__ . '/PlantGrowth.php';
require_once __DIR__ . '/MissionTime.php';
require_once __DIR__ . '/EventTime.php';
require_once __DIR__ . '/GrowthCalculator.php';
require_once __DIR__ . '/GameScheduler.php';
require_once __DIR__ . '/GameProgress.php';
require_once __DIR__ . '/GameEvents.php';