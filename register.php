<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - FitZone</title>
    <link rel="icon" href="IMG/logo.png">
    <link rel="stylesheet" href="auth.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>Create Account</h2>
            <form action="register_process.php" method="POST">
                <div class="form-group">
                    <input type="text" name="fullname" required placeholder="Full Name">
                </div>
                <div class="form-group">
                    <input type="email" name="email" required placeholder="Email Address">
                </div>
                <div class="form-group">
                    <input type="password" name="password" required placeholder="Password">
                </div>
                <div class="form-group">
                    <select name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <select name="role" required>
                        <option value="">Select Role</option>
                        <option value="user">Member</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
                <button type="submit" class="btn">Register</button>
            </form>
            <p class="switch-form">Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</body>
</html>
