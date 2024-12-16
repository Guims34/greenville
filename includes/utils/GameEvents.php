<?php
require_once 'GameConstants.php';
require_once 'GameTime.php';

class GameEvents {
    /**
     * Crée un nouvel événement
     */
    public static function createEvent(array $eventData): array {
        $startDate = date('Y-m-d H:i:s');
        $durationInGameDays = min(
            GameConstants::MAX_EVENT_DURATION,
            max(GameConstants::MIN_EVENT_DURATION, $eventData['duration'])
        );

        $realHours = GameTime::gameDaysToRealHours($durationInGameDays);
        $endDate = date('Y-m-d H:i:s', strtotime("+{$realHours} hours"));

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'duration_game_days' => $durationInGameDays
        ];
    }

    /**
     * Calcule les récompenses d'événement
     */
    public static function calculateEventRewards(array $event, float $participation): array {
        $baseRewards = [
            'coins' => 1000,
            'xp' => 500,
            'premium_coins' => 5
        ];

        $multiplier = min(1, $participation / 100);

        return [
            'coins' => floor($baseRewards['coins'] * $multiplier),
            'xp' => floor($baseRewards['xp'] * $multiplier),
            'premium_coins' => floor($baseRewards['premium_coins'] * $multiplier)
        ];
    }
}