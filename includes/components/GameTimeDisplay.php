<?php
require_once __DIR__ . '/../utils/GameTime.php';
require_once __DIR__ . '/../utils/TimeDisplay.php';
require_once __DIR__ . '/../utils/GameConstants.php';

class GameTimeDisplay {
    private const HOURS_PER_DAY = 24;
    private const SUNRISE_HOUR = 6;
    private const SUNSET_HOUR = 18;

    public static function render() {
        $currentGameDate = GameTime::getCurrentGameDate();
        $gameHour = (int)date('G');
        $isDaytime = $gameHour >= self::SUNRISE_HOUR && $gameHour < self::SUNSET_HOUR;
        $realTime = date('H:i');
        
        echo '<div id="game-time-display" class="fixed bottom-4 right-4 bg-white/90 backdrop-blur rounded-lg shadow-lg p-4 z-50 text-sm">';
        
        // Explication du système de temps
        echo '<div class="text-xs text-gray-500 mb-2">';
        echo '1 heure réelle = 1 jour en jeu';
        echo '</div>';
        
        // Temps réel et temps de jeu
        echo '<div class="flex flex-col gap-1 mb-2">';
        echo '<div class="font-medium">' . TimeDisplay::formatGameDate($currentGameDate) . '</div>';
        echo '<div class="text-xs text-gray-600">Heure réelle : ' . $realTime . '</div>';
        echo '</div>';
        
        // Cycle jour/nuit
        echo '<div class="flex items-center gap-2 text-sm">';
        echo '<div class="text-lg">' . ($isDaytime ? '☀️' : '🌙') . '</div>';
        echo '<div class="flex-1">';
        echo '<div class="w-full bg-gray-200 rounded-full h-1.5 mb-1">';
        echo '<div class="' . ($isDaytime ? 'bg-yellow-400' : 'bg-blue-400') . ' h-1.5 rounded-full transition-all duration-1000" style="width: ' . (($gameHour / self::HOURS_PER_DAY) * 100) . '%"></div>';
        echo '</div>';
        echo '<div class="flex justify-between text-xs text-gray-500">';
        echo '<span>🌅 ' . sprintf('%02d:00', self::SUNRISE_HOUR) . '</span>';
        echo '<span>🌇 ' . sprintf('%02d:00', self::SUNSET_HOUR) . '</span>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        
        echo '</div>';
    }
}