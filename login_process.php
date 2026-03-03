<?php
session_start();
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $redirect = $_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'user_dashboard.php';
    header("Location: $redirect");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate input
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';

    // Input validation
    $errors = [];
    if (!$email) {
        $errors[] = "Invalid email format";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    if (!in_array($role, ['admin', 'user'])) {
        $errors[] = "Invalid role selected";
    }

    if (!empty($errors)) {
        $_SESSION['error'] = implode(", ", $errors);
        header("Location: login.php");
        exit();
    }

    try {
        // Rate limiting check
        $ip = $_SERVER['REMOTE_ADDR'];
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempt_time > DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
        $stmt->execute([$ip]);
        $attempts = $stmt->fetchColumn();

        if ($attempts >= 5) {
            $_SESSION['error'] = "Too many login attempts. Please try again in 15 minutes.";
            header("Location: login.php");
            exit();
        }

        // Record login attempt
        $stmt = $pdo->prepare("INSERT INTO login_attempts (ip_address, email) VALUES (?, ?)");
        $stmt->execute([$ip, $email]);

        // Check user credentials
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
        $stmt->execute([$email, $role]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Clear login attempts on successful login
            $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
            $stmt->execute([$ip]);

            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['fullname'] = $user['fullname'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['last_activity'] = time();

            // Update last login time
            $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);

            $redirect = $role === 'admin' ? 'admin_dashboard.php' : 'user_dashboard.php';
            header("Location: $redirect");
            exit();
        } else {
            $_SESSION['error'] = "Invalid email, password, or role combination";
            header("Location: login.php");
            exit();
        }
    } catch(PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred during login. Please try again later.";
        header("Location: login.php");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>
