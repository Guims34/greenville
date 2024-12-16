<?php
function isUserOnline($lastActivity) {
    if (!$lastActivity) return false;
    
    $timestamp = strtotime($lastActivity);
    $fiveMinutesAgo = time() - (5 * 60); // 5 minutes
    
    return $timestamp > $fiveMinutesAgo;
}

function getOnlineStatus($userId) {
    global $db;
    
    try {
        $stmt = $db->prepare("
            SELECT last_activity 
            FROM users 
            WHERE id = ?
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        
        return $result ? isUserOnline($result['last_activity']) : false;
    } catch (PDOException $e) {
        error_log("Erreur de récupération du statut en ligne : " . $e->getMessage());
        return false;
    }
}