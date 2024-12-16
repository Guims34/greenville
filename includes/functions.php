<?php
require_once 'session.php';
require_once 'auth.php';

function sanitizeString($str) {
    return htmlspecialchars(strip_tags(trim($str)));
}

function addExperience($userId, $amount) {
    global $db;
    
    try {
        $db->beginTransaction();
        
        $stmt = $db->prepare("UPDATE users SET experience = experience + ? WHERE id = ?");
        $stmt->execute([$amount, $userId]);
        
        $stmt = $db->prepare("
            SELECT 
                (SELECT level FROM levels WHERE xp_required <= u.experience ORDER BY level DESC LIMIT 1) as new_level,
                u.experience
            FROM users u 
            WHERE u.id = ?
        ");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        $stmt = $db->prepare("
            SELECT level, coins_reward, premium_coins_reward, unlocked_items
            FROM levels 
            WHERE xp_required <= ? 
            ORDER BY level DESC 
            LIMIT 1
        ");
        $stmt->execute([$user['experience']]);
        $currentLevel = $stmt->fetch();
        
        if ($currentLevel && $currentLevel['level'] > $_SESSION['user_level']) {
            $stmt = $db->prepare("
                UPDATE users 
                SET coins = coins + ?,
                    premium_coins = premium_coins + ?
                WHERE id = ?
            ");
            $stmt->execute([
                $currentLevel['coins_reward'],
                $currentLevel['premium_coins_reward'],
                $userId
            ]);
            
            $_SESSION['user_level'] = $currentLevel['level'];
            
            $stmt = $db->prepare("
                INSERT INTO notifications (user_id, title, message, type) 
                VALUES (?, 'Niveau supérieur !', ?, 'level_up')
            ");
            $stmt->execute([
                $userId, 
                "Vous avez atteint le niveau " . $currentLevel['level'] . " !"
            ]);
        }
        
        $db->commit();
        return true;
        
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        error_log("Erreur lors de l'ajout d'expérience : " . $e->getMessage());
        return false;
    }
}

function checkAchievements($userId) {
    global $db;
    
    try {
        // Récupérer les statistiques
        $stmt = $db->prepare("SELECT * FROM user_stats WHERE user_id = ?");
        $stmt->execute([$userId]);
        $stats = $stmt->fetch();
        
        if (!$stats) return false;
        
        // Récupérer les succès non complétés
        $stmt = $db->prepare("
            SELECT a.* 
            FROM achievements a
            LEFT JOIN user_achievements ua ON a.id = ua.achievement_id AND ua.user_id = ?
            WHERE ua.completed IS NULL OR ua.completed = 0
        ");
        $stmt->execute([$userId]);
        $achievements = $stmt->fetchAll();
        
        foreach ($achievements as $achievement) {
            $completed = false;
            $progress = 0;
            
            switch ($achievement['category']) {
                case 'cultivation':
                    $progress = ($stats['plants_harvested'] / $achievement['target_value']) * 100;
                    $completed = $stats['plants_harvested'] >= $achievement['target_value'];
                    break;
                    
                case 'trading':
                    $progress = ($stats['trades_completed'] / $achievement['target_value']) * 100;
                    $completed = $stats['trades_completed'] >= $achievement['target_value'];
                    break;
                    
                case 'collection':
                    $progress = ($stats['items_collected'] / $achievement['target_value']) * 100;
                    $completed = $stats['items_collected'] >= $achievement['target_value'];
                    break;
            }
            
            // Mise à jour du succès
            $stmt = $db->prepare("
                INSERT INTO user_achievements (user_id, achievement_id, progress, completed, completed_at)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    progress = ?,
                    completed = ?,
                    completed_at = ?
            ");
            
            $completedAt = $completed ? date('Y-m-d H:i:s') : null;
            
            $stmt->execute([
                $userId,
                $achievement['id'],
                $progress,
                $completed,
                $completedAt,
                $progress,
                $completed,
                $completedAt
            ]);
            
            if ($completed) {
                addExperience($userId, $achievement['xp_reward']);
                
                $stmt = $db->prepare("
                    UPDATE users 
                    SET coins = coins + ?,
                        premium_coins = premium_coins + ?
                    WHERE id = ?
                ");
                $stmt->execute([
                    $achievement['coins_reward'],
                    $achievement['premium_coins_reward'],
                    $userId
                ]);
                
                // Notification
                $stmt = $db->prepare("
                    INSERT INTO notifications (user_id, title, message, type) 
                    VALUES (?, ?, ?, 'achievement')
                ");
                $stmt->execute([
                    $userId,
                    "Succès débloqué !",
                    "Vous avez débloqué le succès : " . $achievement['title']
                ]);
            }
        }
        
        return true;
        
    } catch (Exception $e) {
        error_log("Erreur lors de la vérification des succès : " . $e->getMessage());
        return false;
    }
}

function updateUserStats($userId, $type, $value = 1) {
    global $db;
    
    try {
        $stmt = $db->prepare("
            INSERT INTO user_stats (user_id, {$type}) 
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE {$type} = {$type} + ?
        ");
        $stmt->execute([$userId, $value, $value]);
        
        checkAchievements($userId);
        return true;
        
    } catch (Exception $e) {
        error_log("Erreur lors de la mise à jour des statistiques : " . $e->getMessage());
        return false;
    }
}