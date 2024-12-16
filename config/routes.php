<?php
// Configuration des routes de l'application
return [
    // Pages publiques
    'public' => [
        'home',
        'login',
        'register',
        'error',
        'logout' // Ajout de la route logout
    ],
    
    // Pages nécessitant une authentification
    'auth' => [
        'dashboard',
        'admin',
        'admin_plants',
        'new_plant',
        'plant_details',
        'shop',
        'inventory',
        'trade',
        'missions',
        'social',
        'messages',
        'guilds',
        'guild',
        'leaderboard',
        'mod_logs',
        'reports',
        'system_logs',
        'admin_missions',
        'progression'
    ],
    
    // Pages réservées aux administrateurs
    'admin' => [
        'admin',
        'admin_plants',
        'admin_missions',
        'mod_logs',
        'system_logs'
    ]
];