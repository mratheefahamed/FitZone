<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $from_user_id = $_SESSION['user_id'];
    $to_user_id = $_POST['to_user_id'];
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    // Validate inputs
    if (empty($to_user_id) || empty($subject) || empty($message)) {
        $_SESSION['message'] = "All fields are required.";
        $_SESSION['message_type'] = "error";
        header("Location: user_dashboard.php#messages");
        exit();
    }

    try {
        // Insert the message
        $stmt = $pdo->prepare("
            INSERT INTO messages (from_user_id, to_user_id, subject, message, created_at, status) 
            VALUES (?, ?, ?, ?, NOW(), 'unread')
        ");
        $stmt->execute([$from_user_id, $to_user_id, $subject, $message]);

        // Add notification for the recipient
        $stmt = $pdo->prepare("
            INSERT INTO notifications (user_id, type, message, related_id, created_at, status) 
            VALUES (?, 'new_message', ?, ?, NOW(), 'unread')
        ");
        
        $notification_message = sprintf(
            "New message from %s: %s",
            $_SESSION['fullname'],
            $subject
        );
        
        $stmt->execute([$to_user_id, $notification_message, $pdo->lastInsertId()]);

        $_SESSION['message'] = "Message sent successfully.";
        $_SESSION['message_type'] = "success";
    } catch (Exception $e) {
        $_SESSION['message'] = "Error sending message.";
        $_SESSION['message_type'] = "error";
    }
}

header("Location: user_dashboard.php#messages");
exit();
?>
