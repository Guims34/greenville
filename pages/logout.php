<?php
// Démarrer la session avant tout output
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/session.php';

// Enregistrer la déconnexion dans les logs
error_log("User logout - ID: " . ($_SESSION['user_id'] ?? 'unknown') . ", Session ID: " . session_id());

// Détruire toutes les données de session
session_unset();
session_destroy();

// Supprimer le cookie de session
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}

// Rediriger vers la page d'accueil
header('Location: index.php');
exit;