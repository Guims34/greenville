<?php
class SessionManager {
    public function initializeSession($userId, $username, $email) {
        $_SESSION['user_id'] = $userId;
        $_SESSION['username'] = $username;
        $_SESSION['user_email'] = $email;
        $_SESSION['authenticated'] = true;
    }
}