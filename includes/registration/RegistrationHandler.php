<?php
require_once 'RegistrationValidator.php';
require_once 'UserCreator.php';
require_once 'SessionManager.php';
require_once '../notifications/NotificationManager.php';

class RegistrationHandler {
    private $db;
    private $validator;
    private $userCreator;
    private $sessionManager;
    private $notificationManager;
    private $errors = [];

    public function __construct($db) {
        $this->db = $db;
        $this->validator = new RegistrationValidator($db);
        $this->userCreator = new UserCreator($db);
        $this->sessionManager = new SessionManager();
        $this->notificationManager = new NotificationManager($db);
    }

    public function register($username, $email, $password, $confirmPassword) {
        if (!$this->validator->validate($username, $email, $password, $confirmPassword)) {
            $this->errors = $this->validator->getErrors();
            return false;
        }

        try {
            $this->db->beginTransaction();

            $userId = $this->userCreator->createUser($username, $email, $password);
            $this->notificationManager->createWelcomeNotification($userId);
            $this->sessionManager->initializeSession($userId, $username, $email);

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            $this->errors[] = "Une erreur est survenue lors de l'inscription";
            error_log($e->getMessage());
            return false;
        }
    }

    public function getErrors() {
        return $this->errors;
    }
}