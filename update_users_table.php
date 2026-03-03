<?php
require_once 'config.php';

try {
    // Add gender column
    $pdo->exec("ALTER TABLE users ADD COLUMN gender ENUM('male', 'female', 'other') AFTER password");
    
    // Drop age column
    $pdo->exec("ALTER TABLE users DROP COLUMN age");
    
    echo "Database updated successfully!";
} catch(PDOException $e) {
    echo "Error updating database: " . $e->getMessage();
}
?>
