<?php
require_once 'session.php';
require_once 'auth.php';
require_once __DIR__ . '/../src/autoload.php';

use App\Classes\Utils\DateUtils;
use App\Classes\Utils\WeatherUtils;
use App\Classes\Utils\FormatUtils;
use App\Classes\Progression\ExperienceManager;
use App\Classes\Progression\AchievementManager;

// Fonctions utilitaires génériques
function sanitizeString($str) {
    return FormatUtils::sanitizeString($str);
}

function validateInt($value) {
    return FormatUtils::validateInt($value);
}

function redirectTo($page) {
    header("Location: index.php?page=$page");
    exit;
}

function updateUserStats($userId, $type, $value = 1) {
    global $db;
    try {
        $stmt = $db->prepare("
            INSERT INTO user_stats (user_id, {$type}) 
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE {$type} = {$type} + ?
        ");
        $stmt->execute([$userId, $value, $value]);
        
        AchievementManager::checkAchievements($userId);
        return true;
    } catch (Exception $e) {
        error_log("Erreur lors de la mise à jour des statistiques : " . $e->getMessage());
        return false;
    }
}
