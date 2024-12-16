<?php
// Configuration générale de l'application
define('DEBUG_MODE', true); // true, false, ou 'verbose'
define('APP_NAME', 'GreenVille');
define('APP_VERSION', '1.0.0');

// Configuration des chemins
define('BASE_PATH', dirname(__DIR__));
define('PAGES_PATH', BASE_PATH . '/pages');
define('INCLUDES_PATH', BASE_PATH . '/includes');

// Configuration de sécurité
define('ALLOWED_CHARS', 'a-zA-Z0-9_-');
define('MAX_INPUT_LENGTH', 100);