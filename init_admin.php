<?php
require_once 'config/database.php';

try {
    // Supprimer l'admin existant s'il existe
    $db->exec("DELETE FROM users WHERE email = 'admin@greenville.com'");
    
    // Créer un nouveau mot de passe hashé
    $plainPassword = 'admin123456';
    $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

    error_log("=== Création de l'administrateur ===");
    error_log("Hash généré pour admin123456: " . $hashedPassword);
    
    // Créer le nouvel utilisateur admin
    $stmt = $db->prepare("
        INSERT INTO users (
            username,
            email,
            password,
            level,
            coins,
            premium_coins,
            status
        ) VALUES (
            'Administrateur',
            'admin@greenville.com',
            :password,
            99,
            999999,
            999999,
            'active'
        )
    ");
    
    $stmt->execute([':password' => $hashedPassword]);
    $adminId = $db->lastInsertId();
    
    // Vérifier que l'admin a bien été créé
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$adminId]);
    $admin = $stmt->fetch();
    
    if ($admin) {
        error_log("Admin créé avec succès:");
        error_log("ID: " . $admin['id']);
        error_log("Email: " . $admin['email']);
        error_log("Hash stocké: " . $admin['password']);
        
        // Vérifier que le mot de passe fonctionne
        if (password_verify($plainPassword, $admin['password'])) {
            error_log("Vérification du mot de passe réussie");
            echo "Configuration de l'administrateur terminée avec succès\n";
            echo "Email: admin@greenville.com\n";
            echo "Mot de passe: admin123456\n";
        } else {
            throw new Exception("La vérification du mot de passe a échoué");
        }
    } else {
        throw new Exception("L'administrateur n'a pas été créé correctement");
    }
} catch (Exception $e) {
    error_log("Erreur lors de la création de l'admin: " . $e->getMessage());
    echo "Erreur: " . $e->getMessage() . "\n";
    exit(1);
}
?>