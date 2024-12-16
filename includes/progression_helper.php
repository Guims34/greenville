<?php
function getUserLevel($db, $userId) {
    $stmt = $db->prepare("
        SELECT u.*,
               (SELECT level FROM levels WHERE xp_required <= u.experience ORDER BY level DESC LIMIT 1) as current_level,
               (SELECT xp_required FROM levels WHERE level = (
                   SELECT MAX(level) FROM levels WHERE xp_required <= u.experience
               )) as current_level_xp,
               (SELECT MIN(xp_required) FROM levels WHERE xp_required > u.experience) as next_level_xp
        FROM users u 
        WHERE u.id = ?
    ");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

function calculateLevelProgress($user) {
    $xp_progress = $user['experience'] - ($user['current_level_xp'] ?? 0);
    $xp_needed = ($user['next_level_xp'] ?? 100) - ($user['current_level_xp'] ?? 0);
    
    return [
        'xp_progress' => $xp_progress,
        'xp_needed' => $xp_needed,
        'progress_percent' => ($xp_needed > 0) ? min(100, ($xp_progress / $xp_needed) * 100) : 0
    ];
}