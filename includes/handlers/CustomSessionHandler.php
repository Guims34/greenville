<?php
// Activer l'affichage des erreurs pour le debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class CustomSessionHandler {
    /**
     * Détruit la session en cours de manière sécurisée
     */
    public static function destroySession() {
        try {
            // Log pour debug
            error_log("Début de la destruction de session");
            error_log("État de la session : " . session_status());
            error_log("Nom de la session : " . session_name());
            
            // Vérifier si une session est active
            if (session_status() === PHP_SESSION_ACTIVE) {
                // Log des données de session avant destruction
                error_log("Données de session avant destruction : " . print_r($_SESSION, true));
                
                // Vider les données de session
                $_SESSION = array();
                
                // Détruire le cookie de session si présent
                if (isset($_COOKIE[session_name()])) {
                    $params = session_get_cookie_params();
                    error_log("Paramètres du cookie : " . print_r($params, true));
                    
                    setcookie(
                        session_name(), 
                        '', 
                        1,  
                        $params['path'], 
                        $params['domain'],
                        true, 
                        true  
                    );
                }

                // Détruire la session
                $result = session_destroy();
                error_log("Résultat de session_destroy() : " . ($result ? 'true' : 'false'));
            }
            
            error_log("Fin de la destruction de session");

        } catch (Exception $e) {
            error_log("ERREUR CRITIQUE : " . $e->getMessage());
            error_log("Trace : " . $e->getTraceAsString());
            throw $e;
        }
    }
}
