<?php
class PlantsList {
    public static function render($plants) {
        ob_start();
        ?>
        <div class="bg-white rounded-lg shadow-md p-6 mb-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-semibold">Mes Plantes</h2>
                <a href="?page=new_plant" class="text-emerald-600 hover:text-emerald-700">
                    Nouvelle plante
                </a>
            </div>

            <?php if (empty($plants)): ?>
                <p class="text-center text-gray-500 py-8">
                    Vous n'avez pas encore de plantes. Commencez à cultiver !
                </p>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($plants as $plant): ?>
                        <?php
                        $created = new DateTime($plant['created_at']);
                        $now = new DateTime();
                        $age = $created->diff($now)->days;
                        $progress = min(100, ($age / $plant['growth_time']) * 100);
                        ?>
                        <div class="border rounded-lg p-4">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="font-medium"><?php echo htmlspecialchars($plant['name']); ?></h3>
                                    <p class="text-sm text-gray-500">
                                        <?php echo htmlspecialchars($plant['strain_name']); ?>
                                    </p>
                                </div>
                                <span class="inline-block px-2 py-1 text-xs rounded-full <?php
                                    echo match($plant['strain_type']) {
                                        'Indica' => 'bg-purple-100 text-purple-800',
                                        'Sativa' => 'bg-yellow-100 text-yellow-800',
                                        'Hybrid' => 'bg-green-100 text-green-800'
                                    };
                                ?>">
                                    <?php echo $plant['strain_type']; ?>
                                </span>
                            </div>

                            <div class="space-y-2">
                                <div>
                                    <div class="flex justify-between text-sm mb-1">
                                        <span>Progression</span>
                                        <span><?php echo number_format($progress, 0); ?>%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-emerald-500 h-2 rounded-full" style="width: <?php echo $progress; ?>%"></div>
                                    </div>
                                </div>

                                <div class="flex justify-between text-sm">
                                    <span>Santé</span>
                                    <span><?php echo $plant['health']; ?>%</span>
                                </div>

                                <div class="flex justify-between text-sm">
                                    <span>Hydratation</span>
                                    <span><?php echo $plant['water_level']; ?>%</span>
                                </div>
                            </div>

                            <div class="mt-4 flex gap-2">
                                <button 
                                    onclick="waterPlant(<?php echo $plant['id']; ?>)"
                                    class="flex-1 px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600"
                                >
                                    Arroser
                                </button>
                                <a 
                                    href="?page=plant_details&id=<?php echo $plant['id']; ?>"
                                    class="flex-1 px-3 py-1 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 text-center"
                                >
                                    Détails
                                </a>
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
