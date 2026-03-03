<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['plan_id'])) {
    $plan_id = $_POST['plan_id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM membership_plans WHERE id = ?");
        $stmt->execute([$plan_id]);
        $_SESSION['success'] = "Membership plan deleted successfully!";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Failed to delete membership plan: " . $e->getMessage();
    }
}

header("Location: admin_dashboard.php#plans");
exit();
?>
