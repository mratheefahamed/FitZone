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
    <title>User Dashboard</title>
    <link rel="icon" href="IMG/logo.png">
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
                <li><a href="#profile" class="active">My Profile</a></li>
                <li><a href="#messages">Messages</a></li>
                <li><a href="schedule.php">Schedule & Classes</a></li>
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

            <section id="profile" class="section">
                <h2>My Profile</h2>
                <?php
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $user = $stmt->fetch();
                ?>
                <div class="profile-info simple-profile">
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($user['fullname']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                </div>
            </section>

            <section id="messages" class="section">
                <h2><i class="fas fa-envelope"></i> Messages</h2>
                
                <?php
                // Get messages for the current user
                $stmt = $pdo->prepare("
                    SELECT m.*, u.fullname as sender_name 
                    FROM messages m 
                    JOIN users u ON m.from_user_id = u.id 
                    WHERE m.to_user_id = ? 
                    ORDER BY m.created_at DESC
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $messages = $stmt->fetchAll();
                ?>

                <div class="messages-container">
                    <?php if (count($messages) > 0): ?>
                        <?php foreach ($messages as $message): ?>
                            <div class="message <?php echo $message['status'] === 'unread' ? 'unread' : ''; ?>">
                                <div class="message-header">
                                    <div class="message-meta">
                                        <span class="sender">From: <?php echo htmlspecialchars($message['sender_name']); ?></span>
                                        <span class="subject"><?php echo htmlspecialchars($message['subject']); ?></span>
                                        <span class="date"><?php echo date('F j, Y g:i A', strtotime($message['created_at'])); ?></span>
                                    </div>
                                </div>
                                <div class="message-content">
                                    <?php echo nl2br(htmlspecialchars($message['message'])); ?>
                                </div>
                                <?php if ($message['status'] === 'unread'): ?>
                                    <form class="mark-read-form" method="POST" action="mark_read.php">
                                        <input type="hidden" name="message_id" value="<?php echo $message['id']; ?>">
                                        <button type="submit" class="btn-link">Mark as Read</button>
                                    </form>
                                <?php endif; ?>
                                <button class="btn reply-btn" onclick="showReplyForm(<?php echo $message['id']; ?>, '<?php echo htmlspecialchars($message['subject'], ENT_QUOTES); ?>', <?php echo $message['from_user_id']; ?>)">
                                    <i class="fas fa-reply"></i> Reply
                                </button>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-messages">
                            <i class="fas fa-inbox"></i>
                            <p>No messages yet</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Reply Form (Hidden by default) -->
                <div id="reply-form" class="message-form" style="display: none;">
                    <h3><i class="fas fa-reply"></i> Reply to Message</h3>
                    <form method="POST" action="send_message.php">
                        <input type="hidden" name="to_user_id" id="reply_to_user_id">
                        <div class="form-group">
                            <label for="subject">Subject</label>
                            <input type="text" id="reply_subject" name="subject" required>
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" name="message" required></textarea>
                        </div>
                        <button type="submit" class="btn">
                            <i class="fas fa-paper-plane"></i> Send Reply
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="hideReplyForm()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                    </form>
                </div>
            </section>

            <script>
            function showReplyForm(messageId, subject, toUserId) {
                document.getElementById('reply-form').style.display = 'block';
                document.getElementById('reply_subject').value = 'Re: ' + subject;
                document.getElementById('reply_to_user_id').value = toUserId;
                document.getElementById('reply-form').scrollIntoView({ behavior: 'smooth' });
            }

            function hideReplyForm() {
                document.getElementById('reply-form').style.display = 'none';
            }
            </script>
        </main>
    </div>
    <script src="dashboard.js"></script>
</body>
</html>
