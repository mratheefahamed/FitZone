<?php
require_once 'config.php';

try {
    // Show table structure
    $result = $pdo->query("DESCRIBE users");
    echo "Current table structure:\n";
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
        echo "\n";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
