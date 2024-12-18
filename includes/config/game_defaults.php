// includes/config/game_defaults.php
<?php
return [
    'new_player' => [
        'coins' => 2000,           // Pièces de départ
        'premium_coins' => 0,      // Pièces premium de départ
        'level' => 1,              // Niveau de départ
        'experience' => 0,         // XP de départ
        'status' => 'active',      // Statut du compte
        'user_role' => 'user',     // Rôle par défaut
    ],
    'inventory' => [
        // Kit de démarrage gratuit
        'starter_items' => [
            [
                'item_id' => 'small_pot',     // Petit pot de base
                'quantity' => 1
            ],
            [
                'item_id' => 'basic_soil',    // Terreau basique
                'quantity' => 1
            ]
        ]
    ]
];
