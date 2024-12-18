<?php
class Statistics {
    private const STATS_CONFIG = [
        'plants_harvested' => [
            'label' => 'Plantes récoltées',
            'icon' => '🌱'
        ],
        'trades_completed' => [
            'label' => 'Échanges réalisés',
            'icon' => '🤝'
        ],
        'missions_completed' => [
            'label' => 'Missions complétées',
            'icon' => '✅'
        ],
        'achievements_completed' => [
            'label' => 'Succès débloqués',
            'icon' => '🏆'
        ]
    ];

    public static function render($stats) {
        ob_start();
        ?>
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <h2 class="text-lg font-semibold mb-4">Statistiques</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <?php foreach (self::STATS_CONFIG as $key => $config): ?>
                    <div class="text-center">
                        <div class="flex items-center justify-center gap-2">
                            <span class="text-xl"><?php echo $config['icon']; ?></span>
                            <span class="block text-2xl font-bold text-emerald-600">
                                <?php echo number_format($stats[$key] ?? 0); ?>
                            </span>
                        </div>
                        <span class="text-gray-500"><?php echo $config['label']; ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function formatNumber($value) {
        if ($value >= 1000000) {
            return number_format($value / 1000000, 1) . 'M';
        } elseif ($value >= 1000) {
            return number_format($value / 1000, 1) . 'k';
        }
        return number_format($value);
    }
}
