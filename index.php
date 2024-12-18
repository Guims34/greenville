<?php

// Configuration de l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

// Chargement de la configuration
//require_once 'config/bootstrap.php';

// Chargement des dépendances
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';     // D'abord auth.php
require_once 'includes/functions.php';
require_once 'includes/router.php';
require_once 'includes/error_handler.php';
require_once 'includes/activity_tracker.php';
require_once 'includes/online_status.php';

try {
    // Initialisation du routeur
    $router = new Router();
    
    // Vérification de la validité de la page
    if (!$router->isValidPage()) {
        throw new Exception('Page non trouvée');
    }
    
    // Vérification des autorisations
    if ($router->requiresAuth() && !isLoggedIn()) {
        header('Location: index.php?page=login&error=auth_required');
        exit;
    }
    
    if ($router->requiresAdmin() && !isAdmin()) {
        header('Location: index.php?page=dashboard&error=unauthorized');
        exit;
    }
    
    // Inclusion des éléments de page
    include 'includes/header.php';
    
    $page_file = $router->getPagePath();
    if (file_exists($page_file)) {
        include $page_file;
    } else {
        throw new Exception("Fichier de page introuvable : {$router->getCurrentPage()}");
    }
    
    include 'includes/footer.php';
    
} catch (Exception $e) {
    handleError($e);
}