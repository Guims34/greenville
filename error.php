<?php
// Configuration de l'affichage des erreurs
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

$error_code = $_GET['code'] ?? '404';
$error_messages = [
    '404' => 'Page non trouvée',
    '500' => 'Erreur interne du serveur'
];

$error_message = $error_messages[$error_code] ?? 'Erreur inconnue';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Erreur <?php echo $error_code; ?> - GreenVille</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white rounded-lg shadow-md p-8 text-center">
        <h1 class="text-4xl font-bold text-red-600 mb-4">Erreur <?php echo $error_code; ?></h1>
        <p class="text-gray-600 mb-6"><?php echo $error_message; ?></p>
        <a href="index.php" class="inline-block bg-emerald-500 text-white px-6 py-2 rounded hover:bg-emerald-600">
            Retour à l'accueil
        </a>
    </div>
</body>
</html>