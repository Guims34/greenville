<?php
// Vérifier l'authentification avant tout output
require_once 'includes/auth.php';
if (!isLoggedIn()) {
    header('Location: index.php?page=login');
    exit;
}

// Définir le titre de la page
$page_title = "Nouvelle Plante";

// Inclure le header
require_once 'includes/header.php';

// Initialiser les variables
$error = null;
$success_message = null;

try {
    // Récupérer les méthodes de culture disponibles
    $stmt = $db->prepare("
        SELECT * FROM growing_methods 
        ORDER BY type ASC, price ASC
    ");
    $stmt->execute();
    $growing_methods = $stmt->fetchAll();

    // Organiser les méthodes par type
    $methods_by_type = [
        'Soil' => [],
        'Hydroponic' => []
    ];
    foreach ($growing_methods as $method) {
        $methods_by_type[$method['type']][] = $method;
    }

    // Récupérer les variétés disponibles
    $stmt = $db->prepare("SELECT * FROM strains ORDER BY difficulty ASC, name ASC");
    $stmt->execute();
    $strains = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log("Erreur lors de la récupération des données : " . $e->getMessage());
    $error = "Une erreur est survenue lors du chargement des données";
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validation des données
        $name = sanitizeString($_POST['name'] ?? '');
        $strain_id = sanitizeString($_POST['strain'] ?? '');
        $growing_method_id = sanitizeString($_POST['growing_method'] ?? '');
        
        if (empty($name) || empty($strain_id) || empty($growing_method_id)) {
            throw new Exception("Tous les champs sont requis");
        }

        // Vérifier que la variété et la méthode existent
        $stmt = $db->prepare("
            SELECT s.id as strain_id, s.price as strain_price, 
                   gm.id as method_id, gm.price as method_price 
            FROM strains s, growing_methods gm 
            WHERE s.id = ? AND gm.id = ?
        ");
        $stmt->execute([$strain_id, $growing_method_id]);
        $item = $stmt->fetch();

        if (!$item) {
            throw new Exception("Sélection non valide");
        }

        // Calculer le coût total
        $total_cost = $item['strain_price'] + $item['method_price'];

        // Vérifier que l'utilisateur a assez de pièces
        $stmt = $db->prepare("SELECT coins FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch();

        if ($user['coins'] < $total_cost) {
            throw new Exception("Vous n'avez pas assez de pièces (Coût total: $total_cost)");
        }

        // Débuter la transaction
        $db->beginTransaction();

        try {
            // Déduire le coût
            $stmt = $db->prepare("UPDATE users SET coins = coins - ? WHERE id = ?");
            $stmt->execute([$total_cost, $_SESSION['user_id']]);

            // Créer la plante
            $stmt = $db->prepare("
                INSERT INTO plants (
                    user_id,
                    name,
                    strain,
                    growing_method_id,
                    stage,
                    health,
                    humidity,
                    temperature,
                    water_level,
                    growth_time,
                    last_watered,
                    created_at
                ) VALUES (
                    :user_id,
                    :name,
                    :strain,
                    :growing_method_id,
                    1,
                    100,
                    50,
                    20,
                    100,
                    :growth_time,
                    NOW(),
                    NOW()
                )
            ");

            $stmt->execute([
                ':user_id' => $_SESSION['user_id'],
                ':name' => $name,
                ':strain' => $strain_id,
                ':growing_method_id' => $growing_method_id,
                ':growth_time' => rand(60, 90)
            ]);

            $db->commit();
            $_SESSION['success_message'] = "Nouvelle plante créée avec succès !";
            
            // Rediriger vers le dashboard
            header('Location: index.php?page=dashboard');
            exit;

        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    } catch (Exception $e) {
        error_log("Erreur lors de la création de la plante: " . $e->getMessage());
        $error = $e->getMessage();
    }
}

// Gérer les étapes du wizard
$current_step = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$selected_method = isset($_GET['method']) ? $_GET['method'] : '';

// Afficher le contenu
?>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold">Nouvelle Plante</h2>
            <a href="index.php?page=dashboard" class="text-gray-600 hover:text-gray-800">
                ← Retour au tableau de bord
            </a>
        </div>

        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Indicateur de progression -->
        <div class="mb-8">
            <div class="flex items-center justify-between relative">
                <div class="w-full absolute top-1/2 transform -translate-y-1/2">
                    <div class="h-1 bg-gray-200">
                        <div class="h-1 bg-emerald-500 transition-all duration-500" style="width: <?php echo ($current_step - 1) * 50; ?>%"></div>
                    </div>
                </div>
                <div class="relative flex items-center justify-center w-10 h-10 bg-emerald-500 rounded-full text-white">
                    1
                </div>
                <div class="relative flex items-center justify-center w-10 h-10 <?php echo $current_step >= 2 ? 'bg-emerald-500 text-white' : 'bg-gray-200'; ?> rounded-full">
                    2
                </div>
                <div class="relative flex items-center justify-center w-10 h-10 <?php echo $current_step >= 3 ? 'bg-emerald-500 text-white' : 'bg-gray-200'; ?> rounded-full">
                    3
                </div>
            </div>
            <div class="flex justify-between mt-2">
                <span class="text-sm font-medium">Méthode de culture</span>
                <span class="text-sm font-medium">Variété</span>
                <span class="text-sm font-medium">Finalisation</span>
            </div>
        </div>

        <?php if ($current_step === 1): ?>
            <!-- Étape 1: Choix de la méthode de culture -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold mb-6">Choisissez votre méthode de culture</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($methods_by_type as $type => $methods): ?>
                        <div class="space-y-4">
                            <h4 class="font-medium text-gray-700">
                                <?php echo $type === 'Soil' ? 'Culture en Terre' : 'Culture Hydroponique'; ?>
                            </h4>
                            <?php foreach ($methods as $method): ?>
                                <a 
                                    href="?page=new_plant&step=2&method=<?php echo $method['id']; ?>"
                                    class="block p-4 bg-white border rounded-lg transition-colors hover:border-emerald-500 hover:shadow-md"
                                >
                                    <div class="flex justify-between items-start mb-2">
                                        <h3 class="font-semibold"><?php echo htmlspecialchars($method['name']); ?></h3>
                                        <span class="text-sm text-emerald-600 font-medium">
                                            <?php echo number_format($method['price']); ?> 🪙
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-600">
                                        <?php echo htmlspecialchars($method['description']); ?>
                                    </p>
                                    <div class="mt-2 text-sm">
                                        <span class="inline-block px-2 py-1 rounded-full text-xs mr-2 <?php
                                            echo match($method['maintenance_level']) {
                                                'Faible' => 'bg-green-100 text-green-800',
                                                'Moyen' => 'bg-yellow-100 text-yellow-800',
                                                'Élevé' => 'bg-red-100 text-red-800',
                                                default => 'bg-gray-100 text-gray-800'
                                            };
                                        ?>">
                                            Entretien: <?php echo $method['maintenance_level']; ?>
                                        </span>
                                        <span class="text-gray-500">
                                            Capacité: <?php echo $method['capacity']; ?> plante(s)
                                        </span>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php elseif ($current_step === 2 && !empty($selected_method)): ?>
            <!-- Étape 2: Choix de la variété -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold mb-6">Choisissez votre variété</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <?php foreach ($strains as $strain): ?>
                        <a 
                            href="?page=new_plant&step=3&method=<?php echo $selected_method; ?>&strain=<?php echo $strain['id']; ?>"
                            class="block p-4 bg-white border rounded-lg transition-colors hover:border-emerald-500 hover:shadow-md"
                        >
                            <div class="flex justify-between items-start mb-2">
                                <h3 class="font-semibold"><?php echo htmlspecialchars($strain['name']); ?></h3>
                                <span class="text-sm text-emerald-600 font-medium">
                                    <?php echo number_format($strain['price']); ?> 🪙
                                </span>
                            </div>
                            <div class="text-sm text-gray-600 mb-2">
                                <span class="inline-block px-2 py-1 rounded-full text-xs mr-2 <?php
                                    echo match($strain['type']) {
                                        'Indica' => 'bg-purple-100 text-purple-800',
                                        'Sativa' => 'bg-yellow-100 text-yellow-800',
                                        'Hybrid' => 'bg-green-100 text-green-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                ?>"><?php echo $strain['type']; ?></span>
                                <span class="inline-block px-2 py-1 rounded-full text-xs <?php
                                    echo match($strain['difficulty']) {
                                        'Débutant' => 'bg-green-100 text-green-800',
                                        'Intermédiaire' => 'bg-yellow-100 text-yellow-800',
                                        'Expert' => 'bg-red-100 text-red-800',
                                        default => 'bg-gray-100 text-gray-800'
                                    };
                                ?>"><?php echo $strain['difficulty']; ?></span>
                            </div>
                            <p class="text-sm text-gray-500">
                                Floraison: <?php echo $strain['flowering_time_min']; ?>-<?php echo $strain['flowering_time_max']; ?> jours
                            </p>
                            <p class="text-sm text-gray-600 mt-2">
                                <?php echo htmlspecialchars($strain['description']); ?>
                            </p>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

        <?php elseif ($current_step === 3 && !empty($selected_method) && isset($_GET['strain'])): ?>
            <!-- Étape 3: Finalisation -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-semibold mb-6">Finalisez votre création</h3>
                <form method="POST" class="space-y-6">
                    <input type="hidden" name="growing_method" value="<?php echo htmlspecialchars($selected_method); ?>">
                    <input type="hidden" name="strain" value="<?php echo htmlspecialchars($_GET['strain']); ?>">
                    
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nom de votre plante
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            required
                            maxlength="100"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                            placeholder="Donnez un nom à votre plante"
                        >
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a 
                            href="?page=new_plant&step=2&method=<?php echo $selected_method; ?>"
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
                        >
                            Retour
                        </a>
                        <button
                            type="submit"
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
                        >
                            Créer ma plante
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>