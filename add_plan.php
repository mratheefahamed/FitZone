<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $plan_name = $_POST['plan_name'];
    $duration = $_POST['duration'];
    $price = $_POST['price'];
    $description = $_POST['description'];

    try {
        $stmt = $pdo->prepare("INSERT INTO membership_plans (plan_name, duration, price, description) VALUES (?, ?, ?, ?)");
        $stmt->execute([$plan_name, $duration, $price, $description]);
        $_SESSION['success'] = "Membership plan added successfully!";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Failed to add membership plan: " . $e->getMessage();
    }
}

header("Location: admin_dashboard.php#plans");
exit();
?>
