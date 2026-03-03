<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $class_name = $_POST['class_name'];
    $instructor = $_POST['instructor'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $duration = $_POST['duration'];
    $capacity = $_POST['capacity'];

    try {
        $stmt = $pdo->prepare("INSERT INTO classes (class_name, instructor, date, time, duration, capacity) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$class_name, $instructor, $date, $time, $duration, $capacity]);
        $_SESSION['success'] = "Class added successfully!";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Failed to add class: " . $e->getMessage();
    }
}

header("Location: admin_dashboard.php#classes");
exit();
?>
