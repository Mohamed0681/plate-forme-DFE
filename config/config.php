<?php
declare(strict_types=1);
session_start();

define('DB_HOST', 'localhost');
define('DB_NAME', 'plateforme_dep');
define('DB_USER', 'root');
define('DB_PASS', ''); // À modifier selon votre installation

define('SITE_NAME', 'Direction du Financement Extérieur (DFE)');
define('BASE_URL', '/plateforme_dep'); // Si le projet est à la racine du domaine, mettre ''.

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
         PDO::ATTR_EMULATE_PREPARES => false]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Connexion à la base de données impossible. Vérifiez config/config.php.');
}
