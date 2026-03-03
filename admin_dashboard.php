<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
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
    <title>Admin Dashboard - FitZone</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <nav class="sidebar">
            <div class="user-info">
                <img src="IMG/profile-placeholder.png" alt="Profile" class="profile-img">
                <h3><?php echo htmlspecialchars($_SESSION['fullname']); ?></h3>
                <span class="role-badge">Admin</span>
            </div>
            <ul>
                <li><a href="#classes" class="active"><i class="fas fa-dumbbell"></i>Manage Classes</a></li>
                <li><a href="#messages"><i class="fas fa-envelope"></i>Send Messages</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i>Logout</a></li>
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
            <section id="classes" class="section">
                <h2><i class="fas fa-dumbbell"></i> Manage Classes</h2>
                <form action="add_class.php" method="POST" class="add-class-form">
                    <div class="form-group">
                        <input type="text" name="class_name" required placeholder="Class Name">
                    </div>
                    <div class="form-group">
                        <input type="text" name="instructor" required placeholder="Instructor Name">
                    </div>
                    <div class="form-group">
                        <input type="date" name="date" required>
                    </div>
                    <div class="form-group">
                        <input type="time" name="time" required>
                    </div>
                    <div class="form-group">
                        <input type="number" name="duration" required placeholder="Duration (minutes)">
                    </div>
                    <div class="form-group">
                        <input type="number" name="capacity" required placeholder="Class Capacity">
                    </div>
                    <button type="submit" class="btn"><i class="fas fa-plus"></i>Add Class</button>
                </form>

                <div class="class-list">
                    <h3>Current Schedule</h3>
                    <div class="schedule-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Class Name</th>
                                    <th>Trainer</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Duration</th>
                                    <th>Capacity</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo->prepare("
                                    SELECT * FROM classes 
                                    WHERE date >= CURDATE() 
                                    ORDER BY date, time
                                ");
                                $stmt->execute();
                                
                                if ($stmt->rowCount() > 0) {
                                    while ($class = $stmt->fetch()) {
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($class['class_name']); ?></td>
                                            <td><?php echo htmlspecialchars($class['instructor']); ?></td>
                                            <td><?php echo htmlspecialchars($class['date']); ?></td>
                                            <td><?php echo htmlspecialchars($class['time']); ?></td>
                                            <td><?php echo htmlspecialchars($class['duration']); ?> min</td>
                                            <td><?php echo htmlspecialchars($class['capacity']); ?></td>
                                            <td>
                                                <form action="delete_class.php" method="POST" style="display: inline;">
                                                    <input type="hidden" name="class_id" value="<?php echo $class['id']; ?>">
                                                    <button type="submit" class="delete-btn">Delete</button>
                                                </form>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="7" class="no-classes">No upcoming classes scheduled</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section id="messages" class="section">
                <h2><i class="fas fa-envelope"></i> Send Messages</h2>
                <form action="send_message.php" method="POST" class="message-form">
                    <div class="form-group">
                        <select name="to_user_id" required>
                            <option value="">Select User</option>
                            <?php
                            $stmt = $pdo->prepare("SELECT id, fullname FROM users WHERE role = 'user'");
                            $stmt->execute();
                            while ($user = $stmt->fetch()) {
                                echo '<option value="' . $user['id'] . '">' . htmlspecialchars($user['fullname']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="text" name="subject" required placeholder="Message Subject">
                    </div>
                    <div class="form-group">
                        <textarea name="message" required placeholder="Your Message"></textarea>
                    </div>
                    <button type="submit" class="btn">Send Message</button>
                </form>
            </section>
        </main>
    </div>

    <script src="dashboard.js"></script>
</body>
</html>
