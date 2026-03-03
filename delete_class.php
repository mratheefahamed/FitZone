<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['class_id'])) {
    $class_id = $_POST['class_id'];

    try {
        // First delete all bookings for this class
        $stmt = $pdo->prepare("DELETE FROM bookings WHERE class_id = ?");
        $stmt->execute([$class_id]);

        // Then delete the class
        $stmt = $pdo->prepare("DELETE FROM classes WHERE id = ?");
        $stmt->execute([$class_id]);
        
        $_SESSION['success'] = "Class deleted successfully!";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Failed to delete class: " . $e->getMessage();
    }
}

header("Location: admin_dashboard.php#classes");
exit();
?>
