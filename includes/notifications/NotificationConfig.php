<?php
class NotificationConfig {
    // Configuration des types de notifications
    public const TYPES = [
        'welcome' => [
            'title' => 'Bienvenue sur GreenVille !',
            'message' => "🌱 Bienvenue dans GreenVille ! Pour bien démarrer votre aventure :\n\n" .
                        "• Vous recevez {coins} pièces de départ 🪙\n" .
                        "• Commencez par visiter la boutique pour acheter votre première méthode de culture\n" .
                        "• Choisissez une variété adaptée à votre niveau de débutant\n" .
                        "• Consultez vos missions quotidiennes pour gagner des récompenses supplémentaires\n" .
                        "• Rejoignez une guilde pour échanger avec d'autres joueurs\n\n" .
                        "Conseils :\n" .
                        "• Les variétés marquées 'Débutant' sont idéales pour commencer\n" .
                        "• N'oubliez pas d'arroser régulièrement vos plantes\n" .
                        "• Surveillez la santé et la croissance de vos plantes dans le tableau de bord\n\n" .
                        "Bon jeu ! 🌿",
            'type' => 'success'
        ],
        'level_up' => [
            'title' => 'Niveau supérieur !',
            'message' => "🎉 Félicitations ! Vous avez atteint le niveau {level} !\n" .
                        "Nouvelles fonctionnalités débloquées : {features}",
            'type' => 'success'
        ],
        // Ajoutez d'autres types de notifications ici
    ];

    // Valeurs par défaut pour les nouveaux joueurs
    public const DEFAULT_VALUES = [
        'starting_coins' => 2000,
        'starting_premium_coins' => 0,
        'starting_level' => 1
    ];
}