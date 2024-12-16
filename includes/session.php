<?php
// Configuration de la session
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', 3600);

// Démarrage de la session si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Régénération périodique de l'ID de session
function regenerateSessionId() {
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
        return;
    }

    $regeneration_interval = 300; // 5 minutes
    if (time() - $_SESSION['last_regeneration'] > $regeneration_interval) {
        $old_session_data = $_SESSION;
        session_regenerate_id(true);
        $_SESSION = $old_session_data;
        $_SESSION['last_regeneration'] = time();
    }
}

// Vérification de l'expiration de la session
function checkSessionExpiration() {
    if (isset($_SESSION['last_activity'])) {
        $inactive = 3600; // 1 heure
        if (time() - $_SESSION['last_activity'] > $inactive) {
            session_unset();
            session_destroy();
            header('Location: index.php?page=login&error=session_expired');
            exit;
        }
    }
    $_SESSION['last_activity'] = time();
}

// Appel des fonctions de sécurité de session
regenerateSessionId();
checkSessionExpiration();