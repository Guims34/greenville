<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- En-tête -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($plant['name']); ?></h2>
            <a href="index.php?page=dashboard" class="text-gray-600 hover:text-gray-800">
                ← Retour au tableau de bord
            </a>
        </div>

        <!-- Informations principales -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Statistiques vitales -->
            <?php include 'templates/plant/partials/vital_stats.php'; ?>
            
            <!-- Environnement -->
            <?php include 'templates/plant/partials/environment.php'; ?>
        </div>

        <!-- Système d'irrigation -->
        <?php if (!empty($irrigation)): ?>
            <?php include 'templates/plant/partials/irrigation.php'; ?>
        <?php endif; ?>

        <!-- Événements météo -->
        <?php if (!empty($weatherEvents)): ?>
            <?php include 'templates/plant/partials/weather_events.php'; ?>
        <?php endif; ?>
    </div>
</div>
