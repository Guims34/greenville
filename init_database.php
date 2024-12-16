<?php
require_once 'config/database.php';

try {
    // Supprimer la base de données si elle existe
    $db->exec("DROP DATABASE IF EXISTS greenville");
    
    // Lire et exécuter le schéma SQL
    $sql = file_get_contents(__DIR__ . '/sql/schema.sql');
    $queries = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($queries as $query) {
        if (!empty($query)) {
            $db->exec($query);
            echo "Exécution: " . substr($query, 0, 50) . "...\n";
        }
    }
    
    echo "Base de données initialisée avec succès!\n";
    
    // Initialiser l'admin
    require_once 'init_admin.php';
    
} catch (PDOException $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
?>