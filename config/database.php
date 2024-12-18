<?php
define('DB_HOST', '185.207.226.14');
define('DB_USER', 'qcdhfi_f0xxwstr_db');
define('DB_PASS', 'I2%-5a7XB0w!_zcS');
define('DB_NAME', 'qcdhfi_f0xxwstr_db');

try {
    $db = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch(PDOException $e) {
    error_log("Erreur de connexion à la base de données : " . $e->getMessage());
    die("Erreur de connexion à la base de données");
}