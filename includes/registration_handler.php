<?php
class RegistrationHandler {
    private $db;
    private $startingCoins = 2000;
    private $errors = [];

    public function __construct($db) {
        $this->db = $db;
    }

    public function register($username, $email, $password, $confirmPassword) {
        if (!$this->validateInput($username, $email, $password, $confirmPassword)) {
            return false;
        }

        try {
            $this->db->beginTransaction();

            // Créer l'utilisateur
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("
                INSERT INTO users (
                    username, 
                    email, 
                    password, 
                    coins,
                    level,
                    status,
                    created_at
                ) VALUES (?, ?, ?, ?, 1, 'active', NOW())
            ");
            $stmt->execute([$username, $email, $hashedPassword, $this->startingCoins]);
            $userId = $this->db->lastInsertId();

            // Créer une SEULE notification de bienvenue
            $stmt = $this->db->prepare("
                INSERT INTO notifications (
                    user_id, 
                    title, 
                    message, 
                    type,
                    created_at
                ) VALUES (?, ?, ?, 'success', NOW())
            ");
            
            $welcomeMessage = "🌱 Bienvenue dans GreenVille ! Pour bien démarrer votre aventure :\n\n" .
                            "• Vous recevez {$this->startingCoins} pièces de départ 🪙\n" .
                            "• Commencez par visiter la boutique pour acheter votre première méthode de culture\n" .
                            "• Choisissez une variété adaptée à votre niveau de débutant\n" .
                            "• Consultez vos missions quotidiennes pour gagner des récompenses supplémentaires\n" .
                            "• Rejoignez une guilde pour échanger avec d'autres joueurs\n\n" .
                            "Conseils :\n" .
                            "• Les variétés marquées 'Débutant' sont idéales pour commencer\n" .
                            "• N'oubliez pas d'arroser régulièrement vos plantes\n" .
                            "• Surveillez la santé et la croissance de vos plantes dans le tableau de bord\n\n" .
                            "Bon jeu ! 🌿";
            
            $stmt->execute([$userId, 'Bienvenue sur GreenVille !', $welcomeMessage]);

            // Initialiser les statistiques de l'utilisateur
            $stmt = $this->db->prepare("
                INSERT INTO user_stats (user_id) 
                VALUES (?)
            ");
            $stmt->execute([$userId]);

            $this->db->commit();

            // Initialiser la session
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $username;
            $_SESSION['user_email'] = $email;
            $_SESSION['authenticated'] = true;

            return true;

        } catch (Exception $e) {
            $this->db->rollBack();
            $this->errors[] = "Une erreur est survenue lors de l'inscription";
            error_log($e->getMessage());
            return false;
        }
    }

    private function validateInput($username, $email, $password, $confirmPassword) {
        if (strlen($username) < 3) {
            $this->errors[] = "Le nom d'utilisateur doit contenir au moins 3 caractères";
            return false;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "Email invalide";
            return false;
        }

        if (strlen($password) < 6) {
            $this->errors[] = "Le mot de passe doit contenir au moins 6 caractères";
            return false;
        }

        if ($password !== $confirmPassword) {
            $this->errors[] = "Les mots de passe ne correspondent pas";
            return false;
        }

        // Vérifier si l'email existe déjà
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $this->errors[] = "Cet email est déjà utilisé";
            return false;
        }

        return true;
    }

    public function getErrors() {
        return $this->errors;
    }
}
