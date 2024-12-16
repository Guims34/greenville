<?php
// Vérifier que le fichier est appelé depuis admin.php
if (!defined('ADMIN_ACTIONS')) {
    exit('Accès direct interdit');
}

function handleAdminActions() {
    global $db;
    
    $userId = filter_input(INPUT_POST, 'user_id', FILTER_SANITIZE_NUMBER_INT);
    
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'suspend':
                return handleSuspendAction($db, $userId);
            case 'delete':
                return handleDeleteAction($db, $userId);
            case 'activate':
                return handleActivateAction($db, $userId);
            case 'add_coins':
            case 'add_premium':
                return handleCoinsAction($db, $userId, $_POST['action']);
            case 'promote_mod':
            case 'remove_mod':
                return handleModAction($db, $userId, $_POST['action']);
        }
    }
    return null;
}

function handleSuspendAction($db, $userId) {
    try {
        $db->beginTransaction();

        $type = $_POST['suspension_type'];
        $reason = trim($_POST['reason']);
        $banIp = isset($_POST['ban_ip']);

        if ($type === 'temporary') {
            $duration = filter_input(INPUT_POST, 'duration', FILTER_SANITIZE_NUMBER_INT);
            $suspendedUntil = date('Y-m-d H:i:s', strtotime("+$duration days"));
            
            $stmt = $db->prepare("
                UPDATE users 
                SET status = 'suspended',
                    suspended_until = ?,
                    suspension_reason = ?
                WHERE id = ?
            ");
            $stmt->execute([$suspendedUntil, $reason, $userId]);
        } else {
            $stmt = $db->prepare("
                UPDATE users 
                SET status = 'suspended',
                    suspended_until = NULL,
                    suspension_reason = ?
                WHERE id = ?
            ");
            $stmt->execute([$reason, $userId]);
        }

        if ($banIp) {
            $stmt = $db->prepare("SELECT ip_address FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $ip = $stmt->fetchColumn();

            if ($ip) {
                $stmt = $db->prepare("
                    INSERT INTO banned_ips (ip_address, banned_by, reason)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    banned_by = VALUES(banned_by),
                    reason = VALUES(reason)
                ");
                $stmt->execute([$ip, $_SESSION['user_id'], $reason]);
            }
        }

        $db->commit();
        $_SESSION['success_message'] = "Utilisateur suspendu avec succès";
        return 'index.php?page=admin';

    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['error_message'] = "Erreur lors de la suspension : " . $e->getMessage();
        return 'index.php?page=admin';
    }
}

function handleDeleteAction($db, $userId) {
    try {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND email != 'admin@greenville.com'");
        $stmt->execute([$userId]);
        $_SESSION['success_message'] = "Utilisateur supprimé avec succès";
        return 'index.php?page=admin';
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Erreur lors de la suppression : " . $e->getMessage();
        return 'index.php?page=admin';
    }
}

function handleActivateAction($db, $userId) {
    try {
        $stmt = $db->prepare("UPDATE users SET status = 'active' WHERE id = ?");
        $stmt->execute([$userId]);
        $_SESSION['success_message'] = "Utilisateur réactivé avec succès";
        return 'index.php?page=admin';
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Erreur lors de la réactivation : " . $e->getMessage();
        return 'index.php?page=admin';
    }
}

function handleCoinsAction($db, $userId, $action) {
    try {
        $amount = filter_input(INPUT_POST, 'amount', FILTER_SANITIZE_NUMBER_INT);
        if ($amount > 0) {
            $column = $action === 'add_coins' ? 'coins' : 'premium_coins';
            $stmt = $db->prepare("UPDATE users SET $column = $column + ? WHERE id = ?");
            $stmt->execute([$amount, $userId]);
            $_SESSION['success_message'] = ($action === 'add_coins' ? "Pièces" : "Pièces premium") . " ajoutées avec succès";
        }
        return 'index.php?page=admin';
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Erreur lors de l'ajout de pièces : " . $e->getMessage();
        return 'index.php?page=admin';
    }
}

function handleModAction($db, $userId, $action) {
    try {
        $role = $action === 'promote_mod' ? 'moderator' : 'user';
        $stmt = $db->prepare("UPDATE users SET user_role = ? WHERE id = ?");
        $stmt->execute([$role, $userId]);
        $_SESSION['success_message'] = $action === 'promote_mod' ? 
            "Utilisateur promu modérateur" : 
            "Rôle de modérateur retiré";
        return 'index.php?page=admin';
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Erreur lors de la modification du rôle : " . $e->getMessage();
        return 'index.php?page=admin';
    }
}