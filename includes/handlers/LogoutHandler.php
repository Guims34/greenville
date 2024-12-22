<?php
require_once 'CustomSessionHandler.php';

class LogoutHandler {
    public static function handleLogout() {
        // Enregistrer la déconnexion dans les logs
        error_log("User logout - ID: " . ($_SESSION['user_id'] ?? 'unknown') . ", Session ID: " . session_id());

        // Détruire la session
        CustomSessionHandler::destroySession();

        // Vérifier si les headers ont déjà été envoyés
        if (!headers_sent()) {
            header('Location: index.php');
            exit;
        } else {
            echo '<script>window.location.href = "index.php";</script>';
            exit;
        }
    }
}
