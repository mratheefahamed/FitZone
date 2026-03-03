<?php
session_start();

class SessionManager {
    private const SESSION_LIFETIME = 1800; // 30 minutes
    private const REGENERATE_TIME = 300;   // 5 minutes

    public static function initialize() {
        // Set secure session parameters
        ini_set('session.cookie_httponly', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.cookie_secure', 1);
        
        if (isset($_SESSION['last_activity'])) {
            // Check if session has expired
            if (time() - $_SESSION['last_activity'] > self::SESSION_LIFETIME) {
                self::destroy();
                header("Location: login.php?msg=expired");
                exit();
            }

            // Regenerate session ID periodically
            if (!isset($_SESSION['last_regenerated']) || 
                time() - $_SESSION['last_regenerated'] > self::REGENERATE_TIME) {
                self::regenerateSession();
            }
        }

        $_SESSION['last_activity'] = time();
    }

    public static function requireLogin() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login.php");
            exit();
        }
        self::initialize();
    }

    public static function requireAdmin() {
        self::requireLogin();
        if ($_SESSION['role'] !== 'admin') {
            header("Location: user_dashboard.php");
            exit();
        }
    }

    public static function requireUser() {
        self::requireLogin();
        if ($_SESSION['role'] !== 'user') {
            header("Location: admin_dashboard.php");
            exit();
        }
    }

    private static function regenerateSession() {
        // Save old session data
        $old_session_data = $_SESSION;
        
        // Create new session
        session_regenerate_id(true);
        
        // Restore old session data
        $_SESSION = $old_session_data;
        $_SESSION['last_regenerated'] = time();
    }

    public static function destroy() {
        $_SESSION = array();

        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        session_destroy();
    }
}
?>
