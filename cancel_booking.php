<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'])) {
    $booking_id = $_POST['booking_id'];
    $user_id = $_SESSION['user_id'];

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Get booking and class details
        $stmt = $pdo->prepare("
            SELECT b.*, fc.* 
            FROM bookings b 
            JOIN fitness_classes fc ON b.class_id = fc.id 
            WHERE b.id = ? AND b.user_id = ?
            FOR UPDATE
        ");
        $stmt->execute([$booking_id, $user_id]);
        $booking = $stmt->fetch();

        if ($booking) {
            // Delete booking
            $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ? AND user_id = ?");
            $stmt->execute([$booking_id, $user_id]);

            // Update available spots
            $stmt = $pdo->prepare("UPDATE fitness_classes SET spots_available = spots_available + 1 WHERE id = ?");
            $stmt->execute([$booking['class_id']]);

            // Send notification to admin
            $stmt = $pdo->prepare("
                INSERT INTO messages (from_user_id, to_user_id, subject, message, created_at) 
                SELECT ?, id, ?, ?, NOW()
                FROM users 
                WHERE role = 'admin' AND status = 'active'
            ");
            $subject = "Class Booking Cancelled";
            $message = "User " . $_SESSION['fullname'] . " has cancelled their booking for the class '" . $booking['class_name'] . 
                      "' scheduled for " . date('F j, Y', strtotime($booking['class_date'])) . 
                      " at " . date('g:i A', strtotime($booking['start_time']));
            $stmt->execute([$user_id, $subject, $message]);

            $_SESSION['message'] = "Booking cancelled successfully!";
            $_SESSION['message_type'] = "success";

            $pdo->commit();
        } else {
            $_SESSION['message'] = "Invalid booking or you don't have permission to cancel it.";
            $_SESSION['message_type'] = "error";
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['message'] = "An error occurred while cancelling the booking.";
        $_SESSION['message_type'] = "error";
    }
}

header("Location: user_dashboard.php#schedule");
exit();
?>
