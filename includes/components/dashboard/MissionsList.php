<?php
class MissionsList {
    private $missions;

    public function __construct(array $missions) {
        $this->missions = $missions;
    }

    public static function create(array $missions): self {
        return new self($missions);
    }

    public function render(): string {
        ob_start();
        ?>
        <div class="bg-white rounded-lg shadow-md p-6 h-full"> <!-- Ajout de h-full -->
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">Missions du jour</h2>
                <a href="?page=missions" class="text-emerald-600 hover:text-emerald-700">
                    Voir tout
                </a>
            </div>

            <?php if (empty($this->missions)): ?>
                <p class="text-center text-gray-500 py-4">
                    Aucune mission disponible
                </p>
            <?php else: ?>
                <div class="space-y-4">
                    <?php foreach ($this->missions as $mission): ?>
                        <div class="border rounded-lg p-4">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h3 class="font-medium">
                                        <?php echo htmlspecialchars($mission['title']); ?>
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        <?php echo str_replace(
                                            '{target}', 
                                            $mission['target_value'], 
                                            htmlspecialchars($mission['description'])
                                        ); ?>
                                    </p>
                                </div>
                                <?php if ($mission['completed'] && !$mission['claimed']): ?>
                                    <button 
                                        onclick="claimReward(<?php echo $mission['id']; ?>)"
                                        class="px-2 py-1 bg-emerald-500 text-white text-sm rounded hover:bg-emerald-600"
                                    >
                                        Réclamer
                                    </button>
                                <?php endif; ?>
                            </div>

                            <div class="mt-2">
                                <div class="flex justify-between text-sm mb-1">
                                    <span>Progression</span>
                                    <span><?php echo $mission['progress']; ?>/<?php echo $mission['target_value']; ?></span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div 
                                        class="bg-emerald-500 h-2 rounded-full transition-all duration-300" 
                                        style="width: <?php echo min(100, ($mission['progress'] / $mission['target_value']) * 100); ?>%"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
