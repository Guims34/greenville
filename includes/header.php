<?php
// Démarrer la session et charger les dépendances avant tout output
require_once 'auth.php';
require_once 'session.php';
require_once 'components/GameTimeDisplay.php';

// Définir la constante pour les fichiers de navigation
define('INCLUDED_FROM_HEADER', true);

// Initialiser les variables avant tout output
$notifications = [];
if (isLoggedIn()) {
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM user_missions WHERE user_id = ? AND completed = TRUE AND claimed = FALSE");
    $stmt->execute([$_SESSION['user_id']]);
    $notifications['missions'] = $stmt->fetch()['count'];
}

// Vérifier si on doit rediriger avant tout output
if (isset($redirect_url)) {
    header("Location: $redirect_url");
    exit;
}

// Commencer l'output HTML
?><!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? "$page_title - " : ""; ?>GreenVille</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        main {
            flex: 1;
        }
        .dropdown {
            position: relative;
        }
        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 0.5rem;
            z-index: 50;
            min-width: 12rem;
        }
        .dropdown:hover .dropdown-menu,
        .dropdown:focus-within .dropdown-menu {
            display: block;
        }
    </style>
</head>
<body class="bg-emerald-50">
    <?php if (isLoggedIn()): ?>
        <?php GameTimeDisplay::render(); ?>
    <?php endif; ?>

    <nav class="bg-white shadow">
        <div class="container mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <a href="index.php" class="text-xl font-bold text-emerald-600">GreenVille</a>
                <div class="flex items-center gap-4">
				<?php if (isLoggedIn()): ?>
    <?php GameTimeDisplay::render(); ?>
                        <?php include 'navigation/main_nav.php'; ?>
                        <?php include 'navigation/commerce_nav.php'; ?>
                        <?php if (isAdmin()): ?>
                            <?php include 'navigation/admin_nav.php'; ?>
                        <?php endif; ?>
                        <span class="text-gray-700"><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></span>
                        <a href="index.php?page=logout" class="text-gray-600 hover:text-gray-800">Déconnexion</a>
                    <?php else: ?>
                        <a href="index.php?page=login" class="text-emerald-600 hover:text-emerald-700 px-4 py-2 rounded-lg">Connexion</a>
                        <a href="index.php?page=register" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700">Inscription</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <main class="container mx-auto px-4 py-8">