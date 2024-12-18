<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- En-tête -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <a href="index.php?page=dashboard" class="text-gray-600 hover:text-gray-800">
                    ← Retour au tableau de bord
                </a>
                <h1 class="text-2xl font-bold mt-2"><?php echo htmlspecialchars($plant->getName()); ?></h1>
            </div>
            <?php if ($stats['progress'] >= 100): ?>
                <button 
                    onclick="harvestPlant(<?php echo $plant->getId(); ?>)"
                    class="px-4 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600"
                >
                    Récolter
                </button>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Informations principales -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Progression -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold mb-4">Progression</h2>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span>Croissance</span>
                                <span><?php echo number_format($stats['progress'], 1); ?>%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-emerald-500 h-2 rounded-full" style="width: <?php echo $stats['progress']; ?>%"></div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-center">
                            <div>
                                <span class="block text-2xl font-bold"><?php echo $stats['age']; ?></span>
                                <span class="text-sm text-gray-500">Jours écoulés</span>
                            </div>
                            <div>
                                <span class="block text-2xl font-bold"><?php echo $stats['remaining_days']; ?></span>
                                <span class="text-sm text-gray-500">Jours restants</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- État de santé -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold mb-4">État de santé</h2>
                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Santé</label>
                            <div class="flex items-center">
                                <div class="flex-1 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-green-500 h-2 rounded-full" style="width: <?php echo $stats['health']; ?>%"></div>
                                </div>
                                <span class="text-sm font-medium"><?php echo $stats['health']; ?>%</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Hydratation</label>
                            <div class="flex items-center">
                                <div class="flex-1 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-blue-500 h-2 rounded-full" style="width: <?php echo $stats['water_level']; ?>%"></div>
                                </div>
                                <span class="text-sm font-medium"><?php echo $stats['water_level']; ?>%</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nutriments</label>
                            <div class="flex items-center">
                                <div class="flex-1 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-yellow-500 h-2 rounded-full" style="width: <?php echo $stats['nutrients_level']; ?>%"></div>
                                </div>
                                <span class="text-sm font-medium"><?php echo $stats['nutrients_level']; ?>%</span>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">pH</label>
                            <div class="flex items-center">
                                <span class="text-sm font-medium"><?php echo number_format($stats['ph_level'], 1); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold mb-4">Actions</h2>
                    <div class="flex gap-4">
                        <button 
                            onclick="waterPlant(<?php echo $plant->getId(); ?>)"
                            class="flex-1 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600"
                        >
                            Arroser
                        </button>
                        <button 
                            onclick="feedPlant(<?php echo $plant->getId(); ?>)"
                            class="flex-1 px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600"
                        >
                            Nourrir
                        </button>
                    </div>
                </div>
            </div>

            <!-- Informations sur la variété -->
            <div class="space-y-6">
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-lg font-semibold mb-4">Informations</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Variété</label>
                            <p class="mt-1"><?php echo htmlspecialchars($strain['strain_name']); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Type</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php
                                echo match($strain['strain_type']) {
                                    'Indica' => 'bg-purple-100 text-purple-800',
                                    'Sativa' => 'bg-yellow-100 text-yellow-800',
                                    'Hybrid' => 'bg-green-100 text-green-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            ?>">
                                <?php echo $strain['strain_type']; ?>
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Difficulté</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php
                                echo match($strain['difficulty']) {
                                    'Débutant' => 'bg-green-100 text-green-800',
                                    'Intermédiaire' => 'bg-yellow-100 text-yellow-800',
                                    'Expert' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            ?>">
                                <?php echo $strain['difficulty']; ?>
                            </span>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Temps de floraison</label>
                            <p class="mt-1"><?php echo $strain['flowering_time_min']; ?>-<?php echo $strain['flowering_time_max']; ?> jours</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <p class="mt-1 text-sm text-gray-600"><?php echo nl2br(htmlspecialchars($strain['strain_description'])); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function waterPlant(plantId) {
    fetch('ajax/water_plant.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `plant_id=${plantId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Une erreur est survenue');
        }
    });
}

function feedPlant(plantId) {
    fetch('ajax/feed_plant.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `plant_id=${plantId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Une erreur est survenue');
        }
    });
}

function harvestPlant(plantId) {
    if (!confirm('Voulez-vous récolter cette plante ? Cette action est irréversible.')) return;
    
    fetch('ajax/harvest_plant.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `plant_id=${plantId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'index.php?page=dashboard';
        } else {
            alert(data.error || 'Une erreur est survenue');
        }
    });
}
</script>
