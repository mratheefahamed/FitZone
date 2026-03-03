<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['plan_id'])) {
    $plan_id = $_POST['plan_id'];
    $user_id = $_SESSION['user_id'];

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Check if user already has an active membership
        $stmt = $pdo->prepare("SELECT * FROM user_memberships WHERE user_id = ? AND status = 'active'");
        $stmt->execute([$user_id]);
        $active_membership = $stmt->fetch();

        if ($active_membership) {
            $_SESSION['message'] = "You already have an active membership plan.";
            $_SESSION['message_type'] = "error";
            header("Location: user_dashboard.php#membership");
            exit();
        }

        // Verify the plan exists and is active
        $stmt = $pdo->prepare("SELECT * FROM membership_plans WHERE id = ? AND status = 'active'");
        $stmt->execute([$plan_id]);
        $plan = $stmt->fetch();

        if (!$plan) {
            $_SESSION['message'] = "Invalid or inactive membership plan!";
            $_SESSION['message_type'] = "error";
            header("Location: user_dashboard.php#membership");
            exit();
        }

        // Calculate start and end dates
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime("+{$plan['duration']} months"));

        // Create the membership record
        $stmt = $pdo->prepare("INSERT INTO user_memberships 
            (user_id, plan_id, start_date, end_date, status, payment_status) 
            VALUES (?, ?, ?, ?, 'active', 'pending')");
        $stmt->execute([$user_id, $plan_id, $start_date, $end_date]);

        // Commit transaction
        $pdo->commit();

        $_SESSION['message'] = "Thank you for selecting the " . htmlspecialchars($plan['plan_name']) . " plan! 
            Your membership will be activated once the payment is confirmed.";
        $_SESSION['message_type'] = "success";

    } catch(PDOException $e) {
        // Rollback transaction on error
        $pdo->rollBack();
        $_SESSION['message'] = "Failed to select plan: " . $e->getMessage();
        $_SESSION['message_type'] = "error";
    }
}

header("Location: user_dashboard.php#membership");
exit();
?>
