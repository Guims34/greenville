<?php
// includes/admin/queries.php

function getAdminStats($db) {
    return $db->query("
        SELECT 
            (SELECT COUNT(*) FROM users WHERE email != 'admin@greenville.com') as total_users,
            (SELECT COUNT(*) FROM users WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)) as new_users_24h,
            (SELECT COUNT(*) FROM plants) as total_plants,
            (SELECT COUNT(*) FROM trades WHERE status = 'completed') as total_trades,
            (SELECT COUNT(*) FROM guilds) as total_guilds
    ")->fetch();
}

function getAdminUsers($db) {
    return $db->query("
        SELECT u.id, u.username, u.email, u.level, u.coins, u.premium_coins,
               u.status, u.created_at, u.last_activity, u.user_role,
               (SELECT COUNT(*) FROM plants WHERE user_id = u.id) as plants_count,
               (SELECT COUNT(*) FROM trades WHERE sender_id = u.id OR receiver_id = u.id) as trades_count
        FROM users u 
        WHERE u.email != 'admin@greenville.com'
        ORDER BY u.created_at DESC
    ")->fetchAll();
}
