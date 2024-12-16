<?php
requireAuth();

// Vérifier que l'utilisateur est admin
if (!isAdmin()) {
    header('Location: index.php?page=dashboard');
    exit;
}

// Traitement de la modification d'une variété
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_strain'])) {
    try {
        $strain_id = $_POST['strain_id'];
        
        // Mise à jour des informations de base
        $stmt = $db->prepare("
            UPDATE strains 
            SET name = ?, 
                type = ?,
                difficulty = ?,
                flowering_time_min = ?,
                flowering_time_max = ?,
                price = ?,
                description = ?
            WHERE id = ?
        ");
        
        $stmt->execute([
            $_POST['name'],
            $_POST['type'],
            $_POST['difficulty'],
            $_POST['flowering_time_min'],
            $_POST['flowering_time_max'],
            $_POST['price'],
            $_POST['description'],
            $strain_id
        ]);

        // Mise à jour des images
        if (isset($_POST['image_urls'])) {
            // Supprimer les anciennes images
            $stmt = $db->prepare("DELETE FROM strain_images WHERE strain_id = ?");
            $stmt->execute([$strain_id]);

            // Insérer les nouvelles images
            $stages = ['Seedling', 'Vegetative', 'Flowering', 'Harvest'];
            $stmt = $db->prepare("
                INSERT INTO strain_images (strain_id, growth_stage, image_url, thumbnail_url) 
                VALUES (?, ?, ?, ?)
            ");

            foreach ($_POST['image_urls'] as $index => $url) {
                if (!empty($url) && isset($stages[$index])) {
                    $stmt->execute([
                        $strain_id,
                        $stages[$index],
                        $url,
                        $url // Utiliser la même URL pour la miniature pour l'instant
                    ]);
                }
            }
        }

        $_SESSION['success_message'] = "Variété mise à jour avec succès";
    } catch (Exception $e) {
        error_log("Erreur lors de la mise à jour de la variété: " . $e->getMessage());
        $_SESSION['error_message'] = "Erreur lors de la mise à jour de la variété";
    }
}

// Récupération des variétés
try {
    $stmt = $db->query("
        SELECT s.*, 
               GROUP_CONCAT(DISTINCT si.growth_stage, ':', si.image_url) as images
        FROM strains s
        LEFT JOIN strain_images si ON s.id = si.strain_id
        GROUP BY s.id
        ORDER BY s.name ASC
    ");
    $strains = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Erreur lors de la récupération des variétés: " . $e->getMessage());
    $strains = [];
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold">Gestion des Variétés</h2>
        <a href="index.php?page=admin" class="text-gray-600 hover:text-gray-800">
            ← Retour à l'administration
        </a>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            <?php 
            echo htmlspecialchars($_SESSION['success_message']);
            unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <?php 
            echo htmlspecialchars($_SESSION['error_message']);
            unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nom</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Difficulté</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prix</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($strains as $strain): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">
                                <?php echo htmlspecialchars($strain['name']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 text-xs font-semibold rounded-full <?php
                                echo match($strain['type']) {
                                    'Indica' => 'bg-purple-100 text-purple-800',
                                    'Sativa' => 'bg-yellow-100 text-yellow-800',
                                    'Hybrid' => 'bg-green-100 text-green-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            ?>">
                                <?php echo $strain['type']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 text-xs font-semibold rounded-full <?php
                                echo match($strain['difficulty']) {
                                    'Débutant' => 'bg-green-100 text-green-800',
                                    'Intermédiaire' => 'bg-yellow-100 text-yellow-800',
                                    'Expert' => 'bg-red-100 text-red-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            ?>">
                                <?php echo $strain['difficulty']; ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo number_format($strain['price']); ?> 🪙
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button 
                                onclick="editStrain(<?php echo htmlspecialchars(json_encode($strain)); ?>)"
                                class="text-emerald-600 hover:text-emerald-900"
                            >
                                Modifier
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal d'édition -->
<div id="strainModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full">
            <form method="POST" class="p-6">
                <input type="hidden" name="edit_strain" value="1">
                <input type="hidden" name="strain_id" id="strain_id">
                
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-semibold">Modifier la variété</h3>
                    <button type="button" onclick="closeStrainModal()" class="text-gray-400 hover:text-gray-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Onglets -->
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8">
                        <button 
                            type="button"
                            onclick="switchStrainTab('info')"
                            class="strain-tab-button border-b-2 border-emerald-500 py-4 px-1 text-sm font-medium text-emerald-600"
                            data-tab="info"
                        >
                            Informations
                        </button>
                        <button 
                            type="button"
                            onclick="switchStrainTab('images')"
                            class="strain-tab-button border-b-2 border-transparent py-4 px-1 text-sm font-medium text-gray-500 hover:text-gray-700 hover:border-gray-300"
                            data-tab="images"
                        >
                            Images
                        </button>
                    </nav>
                </div>

                <!-- Contenu des onglets -->
                <div class="mt-6">
                    <!-- Onglet Informations -->
                    <div id="strain-tab-info" class="strain-tab-content">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nom</label>
                                <input type="text" name="name" id="strain_name" required
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Type</label>
                                <select name="type" id="strain_type" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    <option value="Indica">Indica</option>
                                    <option value="Sativa">Sativa</option>
                                    <option value="Hybrid">Hybrid</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Difficulté</label>
                                <select name="difficulty" id="strain_difficulty" required
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    <option value="Débutant">Débutant</option>
                                    <option value="Intermédiaire">Intermédiaire</option>
                                    <option value="Expert">Expert</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Floraison Min (jours)</label>
                                    <input type="number" name="flowering_time_min" id="strain_flowering_min" required min="1"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Floraison Max (jours)</label>
                                    <input type="number" name="flowering_time_max" id="strain_flowering_max" required min="1"
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Prix</label>
                                <input type="number" name="price" id="strain_price" required min="1"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea name="description" id="strain_description" rows="3" required
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Onglet Images -->
                    <div id="strain-tab-images" class="strain-tab-content hidden">
                        <div class="space-y-6">
                            <!-- Semis -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Semis</label>
                                <div class="flex items-start space-x-4">
                                    <div class="flex-1">
                                        <input type="url" name="image_urls[]" id="strain_image_seedling"
                                               placeholder="URL de l'image"
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    </div>
                                    <div class="w-24 h-24 bg-gray-100 rounded-lg overflow-hidden">
                                        <img id="preview_seedling" src="" alt="" class="w-full h-full object-cover hidden">
                                        <div class="w-full h-full flex items-center justify-center text-gray-400" id="placeholder_seedling">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Végétation -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Végétation</label>
                                <div class="flex items-start space-x-4">
                                    <div class="flex-1">
                                        <input type="url" name="image_urls[]" id="strain_image_vegetative"
                                               placeholder="URL de l'image"
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    </div>
                                    <div class="w-24 h-24 bg-gray-100 rounded-lg overflow-hidden">
                                        <img id="preview_vegetative" src="" alt="" class="w-full h-full object-cover hidden">
                                        <div class="w-full h-full flex items-center justify-center text-gray-400" id="placeholder_vegetative">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Floraison -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Floraison</label>
                                <div class="flex items-start space-x-4">
                                    <div class="flex-1">
                                        <input type="url" name="image_urls[]" id="strain_image_flowering"
                                               placeholder="URL de l'image"
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    </div>
                                    <div class="w-24 h-24 bg-gray-100 rounded-lg overflow-hidden">
                                        <img id="preview_flowering" src="" alt="" class="w-full h-full object-cover hidden">
                                        <div class="w-full h-full flex items-center justify-center text-gray-400" id="placeholder_flowering">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Récolte -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Récolte</label>
                                <div class="flex items-start space-x-4">
                                    <div class="flex-1">
                                        <input type="url" name="image_urls[]" id="strain_image_harvest"
                                               placeholder="URL de l'image"
                                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                                    </div>
                                    <div class="w-24 h-24 bg-gray-100 rounded-lg overflow-hidden">
                                        <img id="preview_harvest" src="" alt="" class="w-full h-full object-cover hidden">
                                        <div class="w-full h-full flex items-center justify-center text-gray-400" id="placeholder_harvest">
                                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="closeStrainModal()"
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700">
                        Sauvegarder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editStrain(strain) {
    // Remplir les champs du formulaire
    document.getElementById('strain_id').value = strain.id;
    document.getElementById('strain_name').value = strain.name;
    document.getElementById('strain_type').value = strain.type;
    document.getElementById('strain_difficulty').value = strain.difficulty;
    document.getElementById('strain_flowering_min').value = strain.flowering_time_min;
    document.getElementById('strain_flowering_max').value = strain.flowering_time_max;
    document.getElementById('strain_price').value = strain.price;
    document.getElementById('strain_description').value = strain.description;

    // Remplir les URLs des images si elles existent
    if (strain.images) {
        const imageMap = {};
        strain.images.split(',').forEach(img => {
            const [stage, url] = img.split(':');
            imageMap[stage.toLowerCase()] = url;
        });

        document.getElementById('strain_image_seedling').value = imageMap['seedling'] || '';
        document.getElementById('strain_image_vegetative').value = imageMap['vegetative'] || '';
        document.getElementById('strain_image_flowering').value = imageMap['flowering'] || '';
        document.getElementById('strain_image_harvest').value = imageMap['harvest'] || '';

        // Déclencher l'événement input pour mettre à jour les prévisualisations
        ['seedling', 'vegetative', 'flowering', 'harvest'].forEach(stage => {
            const input = document.getElementById(`strain_image_${stage}`);
            const event = new Event('input');
            input.dispatchEvent(event);
        });
    }

    // Afficher la modal
    document.getElementById('strainModal').classList.remove('hidden');
}

function closeStrainModal() {
    document.getElementById('strainModal').classList.add('hidden');
}

function switchStrainTab(tabName) {
    // Cacher tous les contenus
    document.querySelectorAll('.strain-tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    // Réinitialiser tous les boutons
    document.querySelectorAll('.strain-tab-button').forEach(button => {
        button.classList.remove('border-emerald-500', 'text-emerald-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Afficher le contenu sélectionné
    document.getElementById('strain-tab-' + tabName).classList.remove('hidden');
    
    // Activer le bouton sélectionné
    const activeButton = document.querySelector(`.strain-tab-button[data-tab="${tabName}"]`);
    activeButton.classList.remove('border-transparent', 'text-gray-500');
    activeButton.classList.add('border-emerald-500', 'text-emerald-600');
}

// Prévisualisation des images
function setupImagePreviews() {
    const imageInputs = [
        'seedling',
        'vegetative',
        'flowering',
        'harvest'
    ];

    imageInputs.forEach(stage => {
        const input = document.getElementById(`strain_image_${stage}`);
        const preview = document.getElementById(`preview_${stage}`);
        const placeholder = document.getElementById(`placeholder_${stage}`);

        input.addEventListener('input', function() {
            if (this.value) {
                preview.src = this.value;
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');

                // Vérifier si l'image se charge correctement
                preview.onerror = function() {
                    preview.classList.add('hidden');
                    placeholder.classList.remove('hidden');
                };
            } else {
                preview.classList.add('hidden');
                placeholder.classList.remove('hidden');
            }
        });
    });
}

// Appeler la fonction au chargement de la page
document.addEventListener('DOMContentLoaded', setupImagePreviews);
</script>