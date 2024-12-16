<?php
if (isLoggedIn()) {
    header('Location: index.php?page=dashboard');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $errors = [];

    if (strlen($username) < 3) {
        $errors[] = "Le nom d'utilisateur doit contenir au moins 3 caractères";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email invalide";
    }

    if (strlen($password) < 6) {
        $errors[] = "Le mot de passe doit contenir au moins 6 caractères";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Les mots de passe ne correspondent pas";
    }

    if (empty($errors)) {
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Cet email est déjà utilisé";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            
            try {
                $stmt->execute([$username, $email, $hashedPassword]);
                $_SESSION['user_id'] = $db->lastInsertId();
                $_SESSION['username'] = $username;
                $_SESSION['user_email'] = $email;
                header('Location: index.php?page=dashboard');
                exit;
            } catch (PDOException $e) {
                $errors[] = "Erreur lors de l'inscription";
            }
        }
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-8">
        <div class="flex items-center justify-center mb-8">
            <h2 class="text-2xl font-bold">Inscription</h2>
        </div>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">
                    Nom d'utilisateur
                </label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    required
                    minlength="3"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                    value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>"
                >
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">
                    Email
                </label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                    value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                >
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">
                    Mot de passe
                </label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    required
                    minlength="6"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                >
            </div>

            <div>
                <label for="confirm_password" class="block text-sm font-medium text-gray-700">
                    Confirmer le mot de passe
                </label>
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    required
                    minlength="6"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                >
            </div>

            <button
                type="submit"
                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
            >
                S'inscrire
            </button>
        </form>

        <p class="mt-4 text-center text-sm text-gray-600">
            Déjà un compte ?
            <a href="?page=login" class="font-medium text-emerald-600 hover:text-emerald-500">
                Se connecter
            </a>
        </p>
    </div>
</div>