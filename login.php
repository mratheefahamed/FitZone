<?php
session_start();
require_once 'config.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $redirect = $_SESSION['role'] === 'admin' ? 'admin_dashboard.php' : 'user_dashboard.php';
    header("Location: $redirect");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FitZone</title>
    <link rel="icon" href="IMG/logo.png">
    <link rel="stylesheet" href="auth.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>Login to FitZone</h2>
            <?php
            if(isset($_SESSION['error'])) {
                echo '<div class="error"><i class="fas fa-exclamation-circle"></i> '.
                     htmlspecialchars($_SESSION['error']).'</div>';
                unset($_SESSION['error']);
            }
            if(isset($_SESSION['success'])) {
                echo '<div class="success"><i class="fas fa-check-circle"></i> '.
                     htmlspecialchars($_SESSION['success']).'</div>';
                unset($_SESSION['success']);
            }
            ?>
            <form action="login_process.php" method="POST" class="login-form">
                <div class="form-group">
                    <label for="role"> Role</label>
                    <select name="role" id="role" required class="role-select">
                        <option value=""> Select Your Role </option>
                        <option value="user">Member</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="email"> Email</label>
                    <input type="email" name="email" id="email" required 
                           placeholder="Enter your email" 
                           pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" 
                           title="Please enter a valid email address">
                </div>
                <div class="form-group">
                    <label for="password"> Password</label>
                    <div class="password-input">
                        <input type="password" name="password" id="password" required 
                               placeholder="Enter your password" 
                               minlength="6">
                        <span class="toggle-password" onclick="togglePassword()">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
                <button type="submit" class="btn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            <p class="switch-form">
                Don't have an account? <a href="register.php">Register here</a>
            </p>
            
        </div>
    </div>

    <script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.querySelector('.toggle-password i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }

    // Form validation
    document.querySelector('.login-form').addEventListener('submit', function(e) {
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        const role = document.getElementById('role').value;
        let isValid = true;
        let errorMessage = '';

        if (!role) {
            errorMessage = 'Please select your role';
            isValid = false;
        } else if (!email) {
            errorMessage = 'Please enter your email';
            isValid = false;
        } else if (!password) {
            errorMessage = 'Please enter your password';
            isValid = false;
        } else if (password.length < 6) {
            errorMessage = 'Password must be at least 6 characters long';
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error';
            errorDiv.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + errorMessage;
            
            // Remove any existing error messages
            const existingError = document.querySelector('.error');
            if (existingError) {
                existingError.remove();
            }
            
            // Insert new error message
            const form = document.querySelector('.login-form');
            form.insertBefore(errorDiv, form.firstChild);
        }
    });
    </script>
</body>
</html>
