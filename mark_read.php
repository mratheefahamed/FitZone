<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_id'])) {
    $message_id = $_POST['message_id'];
    $user_id = $_SESSION['user_id'];

    try {
        // Update message status to read
        $stmt = $pdo->prepare("
            UPDATE messages 
            SET status = 'read' 
            WHERE id = ? AND to_user_id = ?
        ");
        $stmt->execute([$message_id, $user_id]);

        $_SESSION['message'] = "Message marked as read.";
        $_SESSION['message_type'] = "success";
    } catch (Exception $e) {
        $_SESSION['message'] = "Error updating message status.";
        $_SESSION['message_type'] = "error";
    }
}

// Redirect back to the messages section
header("Location: user_dashboard.php#messages");
exit();
?>
