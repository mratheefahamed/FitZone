<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['class_id'])) {
    $class_id = $_POST['class_id'];
    $user_id = $_SESSION['user_id'];

    try {
        // Start transaction
        $pdo->beginTransaction();

        // Check if class exists and has available spots
        $stmt = $pdo->prepare("
            SELECT fc.*, u.fullname as instructor_name 
            FROM fitness_classes fc 
            LEFT JOIN users u ON fc.instructor_id = u.id 
            WHERE fc.id = ? AND fc.spots_available > 0 AND fc.status = 'active' 
            FOR UPDATE
        ");
        $stmt->execute([$class_id]);
        $class = $stmt->fetch();

        if ($class) {
            // Check if user already booked this class
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ? AND class_id = ?");
            $stmt->execute([$user_id, $class_id]);
            $existing_booking = $stmt->fetchColumn();

            if ($existing_booking > 0) {
                $_SESSION['message'] = "You have already booked this class.";
                $_SESSION['message_type'] = "error";
            } else {
                // Create booking
                $stmt = $pdo->prepare("
                    INSERT INTO bookings (user_id, class_id, booking_date, status) 
                    VALUES (?, ?, NOW(), 'confirmed')
                ");
                $stmt->execute([$user_id, $class_id]);
                $booking_id = $pdo->lastInsertId();

                // Update available spots
                $stmt = $pdo->prepare("
                    UPDATE fitness_classes 
                    SET spots_available = spots_available - 1,
                        spots_booked = spots_booked + 1 
                    WHERE id = ?
                ");
                $stmt->execute([$class_id]);

                // Get user's name for notification
                $stmt = $pdo->prepare("SELECT fullname, email FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();

                // Send notification to all admin users
                $stmt = $pdo->prepare("
                    INSERT INTO notifications (user_id, type, message, related_id, created_at, status) 
                    SELECT 
                        id as user_id,
                        'class_booking',
                        ?,
                        ?,
                        NOW(),
                        'unread'
                    FROM users 
                    WHERE role = 'admin' AND status = 'active'
                ");
                
                $notification_message = sprintf(
                    "New class booking: %s has booked the class '%s' scheduled for %s at %s",
                    $user['fullname'],
                    $class['class_name'],
                    date('F j, Y', strtotime($class['class_date'])),
                    date('g:i A', strtotime($class['start_time']))
                );
                
                $stmt->execute([$notification_message, $booking_id]);

                // Send message to admin's inbox
                $stmt = $pdo->prepare("
                    INSERT INTO messages (from_user_id, to_user_id, subject, message, created_at) 
                    SELECT 
                        ?, 
                        id, 
                        'New Class Booking', 
                        ?, 
                        NOW()
                    FROM users 
                    WHERE role = 'admin' AND status = 'active'
                ");

                $message = sprintf(
                    "Booking Details:\n\nStudent: %s\nEmail: %s\nClass: %s\nDate: %s\nTime: %s - %s\nInstructor: %s\n\nThis is an automatic notification.",
                    $user['fullname'],
                    $user['email'],
                    $class['class_name'],
                    date('F j, Y', strtotime($class['class_date'])),
                    date('g:i A', strtotime($class['start_time'])),
                    date('g:i A', strtotime($class['end_time'])),
                    $class['instructor_name']
                );

                $stmt->execute([$user_id, $message]);

                $_SESSION['message'] = "Class booked successfully! A confirmation has been sent to the admin.";
                $_SESSION['message_type'] = "success";

                $pdo->commit();
            }
        } else {
            $_SESSION['message'] = "This class is no longer available.";
            $_SESSION['message_type'] = "error";
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['message'] = "An error occurred while booking the class.";
        $_SESSION['message_type'] = "error";
    }
}

header("Location: schedule.php");
exit();
