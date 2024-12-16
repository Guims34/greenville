<?php
function handleError($error) {
    error_log($error->getMessage());
    
    if (DEBUG_MODE) {
        echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative m-4'>";
        echo "<h3 class='font-bold'>Erreur :</h3>";
        echo "<p>" . htmlspecialchars($error->getMessage()) . "</p>";
        if (DEBUG_MODE === 'verbose') {
            echo "<pre class='mt-2 text-sm'>" . htmlspecialchars($error->getTraceAsString()) . "</pre>";
        }
        echo "</div>";
    } else {
        include 'pages/error.php';
    }
}