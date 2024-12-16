
<?php
function generateDailyMissions($db, $userId) {
    try {
        $db->beginTransaction();

        // Supprimer les missions expirées
        $stmt = $db->prepare("
            DELETE FROM user_missions 
            WHERE user_id = ? AND expires_at < NOW()
        ");
        $stmt->execute([$userId]);

        // Vérifier les missions actives
        $stmt = $db->prepare("
            SELECT COUNT(*) 
            FROM user_missions 
            WHERE user_id = ? AND expires_at > NOW()
        ");
        $stmt->execute([$userId]);
        
        if ($stmt->fetchColumn() > 0) {
            $db->commit();
            return true;
        }

        // Sélectionner 3 missions aléatoires
        $stmt = $db->query("
            SELECT * FROM daily_missions 
            ORDER BY RAND() 
            LIMIT 3
        ");
        $missions = $stmt->fetchAll();

        // Créer les nouvelles missions
        $stmt = $db->prepare("
            INSERT INTO user_missions (user_id, mission_id, expires_at) 
            VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 DAY))
        ");

        foreach ($missions as $mission) {
            $stmt->execute([$userId, $mission['id']]);
        }

        $db->commit();
        return true;

    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        error_log("Erreur missions: " . $e->getMessage());
        return false;
    }
}

function getUserMissions($db, $userId) {
    $stmt = $db->prepare("
        SELECT m.*, um.progress, um.completed, um.claimed, um.expires_at
        FROM daily_missions m
        JOIN user_missions um ON m.id = um.mission_id
        WHERE um.user_id = ? AND um.expires_at > NOW()
        ORDER BY um.completed ASC, m.type ASC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}
