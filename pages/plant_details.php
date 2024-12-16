<?php
requireAuth();

// Vérifier si l'ID de la plante est fourni
if (!isset($_GET['id'])) {
    header('Location: index.php?page=dashboard');
    exit;
}

$plant_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

try {
    // Récupérer les détails de la plante avec les informations de la variété
    $stmt = $db->prepare("
        SELECT 
            p.*,
            s.name as strain_name,
            s.type as strain_type,
            s.difficulty,
            s.flowering_time_min,
            s.flowering_time_max,
            s.description as strain_description
        FROM plants p
        JOIN strains s ON p.strain = s.id
        WHERE p.id = ? AND p.user_id = ?
        LIMIT 1
    ");
    
    $stmt->execute([$plant_id, $_SESSION['user_id']]);
    $plant = $stmt->fetch();

    if (!$plant) {
        throw new Exception("Plante non trouvée");
    }

    // Calculer le temps restant
    $created = new DateTime($plant['created_at']);
    $now = new DateTime();
    $age = $created->diff($now)->days;
    $remaining_days = max(0, $plant['growth_time'] - $age);
    
    // Calculer le pourcentage de progression
    $progress = min(100, ($age / $plant['growth_time']) * 100);

} catch (Exception $e) {
    error_log("Erreur lors de la récupération des détails de la plante: " . $e->getMessage());
    $_SESSION['error_message'] = "Erreur lors de la récupération des détails de la plante";
    header('Location: index.php?page=dashboard');
    exit;
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- En-tête avec bouton retour -->
        <div class="flex items-center justify-between mb-6">
            <a href="index.php?page=dashboard" class="text-gray-600 hover:text-gray-800">
                ← Retour au tableau de bord
            </a>
            <h1 class="text-2xl font-bold"><?php echo htmlspecialchars($plant['name']); ?></h1>
        </div>

        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <!-- Image et informations principales -->
            <div class="md:flex">
                <div class="md:w-1/3 bg-emerald-50 p-6">
                    <div class="aspect-square rounded-lg bg-emerald-100 flex items-center justify-center mb-4">
                        <!-- Placeholder pour l'image -->
                        <svg class="w-24 h-24 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Variété:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($plant['strain_name']); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Type:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($plant['strain_type']); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Difficulté:</span>
                            <span class="font-medium"><?php echo htmlspecialchars($plant['difficulty']); ?></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Âge:</span>
                            <span class="font-medium"><?php echo $age; ?> jours</span>
                        </div>
                    </div>
                </div>

                <div class="md:w-2/3 p-6">
                    <!-- Progression -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">Progression</h3>
                        <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
                            <div class="bg-emerald-500 h-2.5 rounded-full" style="width: <?php echo $progress; ?>%"></div>
                        </div>
                        <p class="text-sm text-gray-600">
                            <?php echo $remaining_days; ?> jours restants
                        </p>
                    </div>

                    <!-- État actuel -->
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-600 mb-2">Santé</h4>
                            <div class="flex items-center">
                                <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2">
                                    <div class="bg-green-500 h-2.5 rounded-full" style="width: <?php echo $plant['health']; ?>%"></div>
                                </div>
                                <span class="text-sm font-medium"><?php echo $plant['health']; ?>%</span>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <h4 class="text-sm font-medium text-gray-600 mb-2">Hydratation</h4>
                            <div class="flex items-center">
                                <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2">
                                    <div class="bg-blue-500 h-2.5 rounded-full" style="width: <?php echo $plant['water_level']; ?>%"></div>
                                </div>
                                <span class="text-sm font-medium"><?php echo $plant['water_level']; ?>%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-4">
                        <button onclick="waterPlant(<?php echo $plant_id; ?>)" class="flex-1 bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600">
                            Arroser
                        </button>
                        <?php if ($progress >= 100): ?>
                            <button onclick="harvestPlant(<?php echo $plant_id; ?>)" class="flex-1 bg-emerald-500 text-white px-4 py-2 rounded-lg hover:bg-emerald-600">
                                Récolter
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Informations détaillées -->
            <div class="border-t p-6">
                <h3 class="text-lg font-semibold mb-4">Informations sur la variété</h3>
                <p class="text-gray-600 mb-4">
                    <?php echo nl2br(htmlspecialchars($plant['strain_description'])); ?>
                </p>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-600">Temps de floraison:</span>
                        <span class="font-medium"><?php echo $plant['flowering_time_min']; ?>-<?php echo $plant['flowering_time_max']; ?> jours</span>
                    </div>
                    <div>
                        <span class="text-gray-600">Dernière irrigation:</span>
                        <span class="font-medium"><?php echo (new DateTime($plant['last_watered']))->format('d/m/Y H:i'); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function waterPlant(plantId) {
    if (!confirm('Voulez-vous arroser cette plante ?')) return;
    
    fetch('ajax/water_plant.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'plant_id=' + plantId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || 'Une erreur est survenue');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Une erreur est survenue');
    });
}

function harvestPlant(plantId) {
    if (!confirm('Voulez-vous récolter cette plante ? Cette action est irréversible.')) return;
    
    fetch('ajax/harvest_plant.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'plant_id=' + plantId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'index.php?page=dashboard';
        } else {
            alert(data.error || 'Une erreur est survenue');
        }
    })
    .catch(error => {
        console.error('Erreur:', error);
        alert('Une erreur est survenue');
    });
}
</script>