<?php
function updateUserActivity() {
    global $db;
    
    if (isLoggedIn()) {
        try {
            $stmt = $db->prepare("
                UPDATE users 
                SET last_activity = NOW() 
                WHERE id = ?
            ");
            $stmt->execute([$_SESSION['user_id']]);
        } catch (PDOException $e) {
            error_log("Erreur de mise à jour de l'activité : " . $e->getMessage());
        }
    }
}

// Mettre à jour l'activité à chaque requête
updateUserActivity();