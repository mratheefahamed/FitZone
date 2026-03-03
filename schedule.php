<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Schedule - FitZone</title>
    <link rel="icon" href="IMG/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="shortcut icon" href="IMG/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="user-info">
                <img src="IMG/person-01.png" alt="Profile" class="profile-img">
                <h3><?php echo htmlspecialchars($_SESSION['fullname']); ?></h3>
                <span class="role-badge">Member ⭐</span>
            </div>
            <ul>
                <li><a href="user_dashboard.php#profile">My Profile</a></li>
                <li><a href="user_dashboard.php#messages">Messages</a></li>
                <li><a href="schedule.php" class="active">Schedule & Classes</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </nav>
        
        <main class="content">
            <?php if (isset($_SESSION['message'])) { ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?>">
                    <i class="fas fa-<?php echo $_SESSION['message_type'] === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php 
                    echo $_SESSION['message']; 
                    unset($_SESSION['message']);
                    unset($_SESSION['message_type']);
                    ?>
                </div>
            <?php } ?>

            <section id="schedule" class="section">
                <h2><i class="fas fa-calendar-alt"></i> Schedule & Classes</h2>
                
                <!-- Available Classes -->
                <div class="classes-grid">
                    <?php
                    // Get available classes
                    $stmt = $pdo->prepare("SELECT * FROM fitness_classes WHERE status = 'active' ORDER BY class_date, start_time");
                    $stmt->execute();
                    $classes = $stmt->fetchAll();

                    foreach ($classes as $class) {
                        $class_datetime = date('Y-m-d', strtotime($class['class_date'])) . ' ' . $class['start_time'];
                        $is_past = strtotime($class_datetime) < time();
                        ?>
                        <div class="class-card <?php echo $is_past ? 'past' : ''; ?>">
                            <div class="class-header">
                                <h3><?php echo htmlspecialchars($class['class_name']); ?></h3>
                                <span class="class-type"><?php echo htmlspecialchars($class['class_type']); ?></span>
                            </div>
                            <div class="class-details">
                                <p><i class="far fa-calendar"></i> <?php echo date('F j, Y', strtotime($class['class_date'])); ?></p>
                                <p><i class="far fa-clock"></i> <?php echo date('g:i A', strtotime($class['start_time'])); ?> - <?php echo date('g:i A', strtotime($class['end_time'])); ?></p>
                                <p><i class="fas fa-user-friends"></i> <?php echo $class['spots_available']; ?> spots available</p>
                                <p><i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($class['instructor']); ?></p>
                            </div>
                            <?php if (!$is_past && $class['spots_available'] > 0) { ?>
                                <form action="book_class.php" method="POST" class="booking-form">
                                    <input type="hidden" name="class_id" value="<?php echo $class['id']; ?>">
                                    <button type="submit" class="btn book-btn">
                                        <i class="fas fa-bookmark"></i> Book Class
                                    </button>
                                </form>
                            <?php } else if ($is_past) { ?>
                                <span class="class-status past">Class Ended</span>
                            <?php } else { ?>
                                <span class="class-status full">Class Full</span>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>

                <!-- My Bookings -->
                <div class="my-bookings">
                    <h3><i class="fas fa-bookmark"></i> My Booked Classes</h3>
                    <?php
                    $stmt = $pdo->prepare("
                        SELECT b.*, fc.* 
                        FROM bookings b 
                        JOIN fitness_classes fc ON b.class_id = fc.id 
                        WHERE b.user_id = ? 
                        ORDER BY fc.class_date, fc.start_time
                    ");
                    $stmt->execute([$_SESSION['user_id']]);
                    $bookings = $stmt->fetchAll();

                    if (count($bookings) > 0) { ?>
                        <div class="bookings-grid">
                            <?php foreach ($bookings as $booking) {
                                $class_datetime = date('Y-m-d', strtotime($booking['class_date'])) . ' ' . $booking['start_time'];
                                $is_past = strtotime($class_datetime) < time();
                                ?>
                                <div class="booking-card <?php echo $is_past ? 'past' : ''; ?>">
                                    <div class="booking-header">
                                        <h4><?php echo htmlspecialchars($booking['class_name']); ?></h4>
                                        <span class="booking-status"><?php echo $is_past ? 'Completed' : 'Upcoming'; ?></span>
                                    </div>
                                    <div class="booking-details">
                                        <p><i class="far fa-calendar"></i> <?php echo date('F j, Y', strtotime($booking['class_date'])); ?></p>
                                        <p><i class="far fa-clock"></i> <?php echo date('g:i A', strtotime($booking['start_time'])); ?></p>
                                        <p><i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($booking['instructor']); ?></p>
                                    </div>
                                    <?php if (!$is_past) { ?>
                                        <form action="cancel_booking.php" method="POST" class="cancel-form">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                            <button type="submit" class="btn cancel-btn">
                                                <i class="fas fa-times"></i> Cancel Booking
                                            </button>
                                        </form>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <p class="no-bookings">You haven't booked any classes yet.</p>
                    <?php } ?>
                </div>
            </section>
        </main>
    </div>
    <script src="dashboard.js"></script>
</body>
</html>
