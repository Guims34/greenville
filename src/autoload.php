<?php
spl_autoload_register(function ($class) {
    // Convertir le namespace en chemin de fichier
    $class = str_replace('App\\Classes\\', 'classes/', $class);
    $file = str_replace('\\', '/', $class);
    
    // Chemin absolu vers le dossier src
    $baseDir = dirname(__FILE__);
    
    // Construire le chemin complet du fichier
    $file = $baseDir . '/' . $file . '.php';
    
    // Vérifier si le fichier existe
    if (file_exists($file)) {
        require_once $file;
    } else {
        // Log pour le debug
        error_log("Autoload failed for class: $class (File not found: $file)");
    }
});