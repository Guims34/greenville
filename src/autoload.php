<?php
spl_autoload_register(function ($class) {
    // Convertir le namespace en chemin de fichier
    $file = str_replace('\\', DIRECTORY_SEPARATOR, $class);
    $file = __DIR__ . DIRECTORY_SEPARATOR . $file . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});