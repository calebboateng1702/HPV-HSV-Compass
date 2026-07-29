<?php
/**
 * Database connection settings.
 * Update these four constants to match your local MySQL setup.
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'hpv_hsv_compass');
define('DB_USER', 'root');
define('DB_PASS', '');

/**
 * BASE_URL auto-detects the folder the app lives in, so links/assets/redirects
 * work whether you serve this from the web server's document root
 * (http://localhost/) or a subfolder (http://localhost/hpv-hsv-compass/).
 * No manual configuration needed.
 */
if (!defined('BASE_URL')) {
    $projectRoot = rtrim(str_replace('\\', '/', dirname(__DIR__)), '/');
    $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/');
    $detected = ($docRoot !== '' && strpos($projectRoot, $docRoot) === 0)
        ? substr($projectRoot, strlen($docRoot))
        : '';
    define('BASE_URL', $detected);
}

function get_db() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            die('Database connection failed. Check config/database.php — ' . htmlspecialchars($e->getMessage()));
        }
    }
    return $pdo;
}
