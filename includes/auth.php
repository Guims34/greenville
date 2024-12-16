<?php
require_once 'session.php';

function login($email, $password) {
    global $db;
    
    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['user_role'];
            $_SESSION['authenticated'] = true;
            $_SESSION['last_activity'] = time();
            return true;
        }
        
        return false;
    } catch (PDOException $e) {
        error_log("Erreur de connexion : " . $e->getMessage());
        return false;
    }
}

function isLoggedIn() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['authenticated']) || !$_SESSION['authenticated']) {
        error_log("Session non authentifiée - Session actuelle : " . print_r($_SESSION, true));
        return false;
    }

    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 3600)) {
        session_unset();
        session_destroy();
        return false;
    }

    $_SESSION['last_activity'] = time();
    return true;
}

function requireAuth() {
    if (!isLoggedIn()) {
        $_SESSION['error_message'] = "Authentification requise";
        exit(header('Location: index.php?page=login'));
    }

    global $db;
    try {
        $stmt = $db->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$_SESSION['user_id']]);
        if (!$stmt->fetch()) {
            error_log("Utilisateur non trouvé en base de données - ID: " . $_SESSION['user_id']);
            session_unset();
            session_destroy();
            header('Location: index.php?page=login?error=invalid_session');
            exit;
        }
    } catch (PDOException $e) {
        error_log("Erreur de vérification d'authentification : " . $e->getMessage());
        throw $e;
    }
}

function getUserRole($userId) {
    global $db;
    try {
        $stmt = $db->prepare("SELECT user_role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        return $user ? $user['user_role'] : 'visitor';
    } catch (PDOException $e) {
        error_log("Erreur lors de la récupération du rôle : " . $e->getMessage());
        return 'visitor';
    }
}

function isAdmin() {
    if (!isLoggedIn()) return false;
    return getUserRole($_SESSION['user_id']) === 'admin';
}

function isModerator() {
    if (!isLoggedIn()) return false;
    $role = getUserRole($_SESSION['user_id']);
    return in_array($role, ['admin', 'moderator']);
}

function logout() {
    session_unset();
    session_destroy();
    header('Location: index.php?page=login');
    exit;
}