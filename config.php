<?php
// Error reporting
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

// Application settings
define('APP_NAME', 'FitZone');
define('APP_URL', 'http://localhost/Temp');
define('UPLOAD_DIR', __DIR__ . '/uploads');

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Changed from 1 to 0 for localhost
ini_set('session.gc_maxlifetime', 1800);
ini_set('session.cookie_lifetime', 0);

// Database configuration
$db_config = [
    'host' => 'localhost',
    'dbname' => 'fitness_db',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]
];

try {
    $dsn = sprintf(
        "mysql:host=%s;dbname=%s;charset=%s",
        $db_config['host'],
        $db_config['dbname'],
        $db_config['charset']
    );

    $pdo = new PDO(
        $dsn,
        $db_config['username'],
        $db_config['password'],
        $db_config['options']
    );
} catch(PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("A system error has occurred. Please try again later.");
}

// Helper functions
function sanitize_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

function redirect($path) {
    header("Location: " . APP_URL . '/' . ltrim($path, '/'));
    exit();
}

function is_ajax_request() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function json_response($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

// Create upload directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}
?>
