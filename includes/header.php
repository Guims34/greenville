<?php
require_once 'auth.php';
require_once 'session.php';
require_once 'components/GameTimeDisplay.php';
require_once 'components/Navigation.php';
require_once 'components/Head.php';

define('INCLUDED_FROM_HEADER', true);

// Initialiser les notifications
$notifications = [];
if (isLoggedIn()) {
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM user_missions WHERE user_id = ? AND completed = TRUE AND claimed = FALSE");
    $stmt->execute([$_SESSION['user_id']]);
    $notifications['missions'] = $stmt->fetch()['count'];
}

// Redirection si nécessaire
if (isset($redirect_url)) {
    header("Location: $redirect_url");
    exit;
}
?>
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
    }
    @media (min-width: 768px) {
        .dropdown-menu {
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
    }
    @media (max-width: 767px) {
        .dropdown-menu.show {
            display: block;
            width: 100%;
            margin-top: 0.5rem;
        }
    }
</style>

<head>
    <!-- ... autres balises head ... -->
    <link rel="stylesheet" href="/public/css/notifications.css">
    <script src="/public/js/notifications.js" defer></script>

</head>
<!DOCTYPE html>
<html lang="fr">
<?php renderHead($page_title ?? ''); ?>
<body class="bg-emerald-50 min-h-screen flex flex-col">
    <?php if (isLoggedIn()): ?>
        <?php GameTimeDisplay::render(); ?>
    <?php endif; ?>

    <nav class="bg-white shadow">
    <div class="container mx-auto px-4 py-4">
        <div class="flex justify-between items-center">
            <a href="index.php" class="text-xl font-bold text-emerald-600">GreenVille</a>
            <div class="flex items-center gap-4">
                <?php if (isLoggedIn()): ?>
                    <?php Navigation::render(); ?>
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

    
    <main class="flex-1">
        <div class="container mx-auto px-4 py-8">
