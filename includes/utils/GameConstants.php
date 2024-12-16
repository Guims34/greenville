<?php
class GameConstants {
    // Constantes de temps
    public const REAL_HOUR_TO_GAME_DAY = 1;
    public const SECONDS_PER_HOUR = 3600;
    public const GAME_START_DATE = '2024-01-01';

    // Constantes de croissance des plantes
    public const BASE_GROWTH_TIME = 60; // 60 jours de jeu pour une croissance complète
    public const WATER_LOSS_PER_DAY = 10; // 10% de perte d'eau par jour
    public const NUTRIENT_LOSS_PER_DAY = 5; // 5% de perte de nutriments par jour

    // Constantes des missions
    public const MISSION_DURATION = 24; // 24 heures réelles = 24 jours de jeu
    public const MISSION_REFRESH_HOUR = 0; // Heure de rafraîchissement des missions (minuit)

    // Constantes des événements
    public const MIN_EVENT_DURATION = 7; // 7 jours de jeu minimum
    public const MAX_EVENT_DURATION = 30; // 30 jours de jeu maximum
}