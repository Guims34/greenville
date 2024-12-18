<?php
class RegistrationValidator {
    private $db;
    private $errors = [];

    public function __construct($db) {
        $this->db = $db;
    }

    public function validate($username, $email, $password, $confirmPassword) {
        return $this->validateUsername($username) &&
               $this->validateEmail($email) &&
               $this->validatePassword($password, $confirmPassword);
    }

    private function validateUsername($username) {
        if (strlen($username) < 3) {
            $this->errors[] = "Le nom d'utilisateur doit contenir au moins 3 caractères";
            return false;
        }
        return true;
    }

    private function validateEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "Email invalide";
            return false;
        }

        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $this->errors[] = "Cet email est déjà utilisé";
            return false;
        }
        return true;
    }

    private function validatePassword($password, $confirmPassword) {
        if (strlen($password) < 6) {
            $this->errors[] = "Le mot de passe doit contenir au moins 6 caractères";
            return false;
        }

        if ($password !== $confirmPassword) {
            $this->errors[] = "Les mots de passe ne correspondent pas";
            return false;
        }
        return true;
    }

    public function getErrors() {
        return $this->errors;
    }
}