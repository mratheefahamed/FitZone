<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'store_admin'])) {
    header("Location: login.php");
    exit();
}

$store_id = $_SESSION['store_id'];

// Handle service addition
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    if ($_POST['action'] == 'add') {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $duration = $_POST['duration'];
        $price = $_POST['price'];

        try {
            $stmt = $pdo->prepare("INSERT INTO services (store_id, name, description, duration, price) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$store_id, $name, $description, $duration, $price]);
            $_SESSION['success'] = "Service added successfully!";
        } catch(PDOException $e) {
            $_SESSION['error'] = "Failed to add service: " . $e->getMessage();
        }
    } 
    elseif ($_POST['action'] == 'delete' && isset($_POST['service_id'])) {
        try {
            $stmt = $pdo->prepare("DELETE FROM services WHERE id = ? AND store_id = ?");
            $stmt->execute([$_POST['service_id'], $store_id]);
            $_SESSION['success'] = "Service deleted successfully!";
        } catch(PDOException $e) {
            $_SESSION['error'] = "Failed to delete service: " . $e->getMessage();
        }
    }
    header("Location: manage_services.php");
    exit();
}

// Fetch existing services
$stmt = $pdo->prepare("SELECT * FROM services WHERE store_id = ? ORDER BY name");
$stmt->execute([$store_id]);
$services = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Services - FitZone</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include 'sidebar.php'; ?>
        
        <main class="content">
            <section class="section">
                <h2>Manage Services</h2>
                
                <?php if(isset($_SESSION['error'])): ?>
                    <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['success'])): ?>
                    <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>

                <div class="add-service-form">
                    <h3>Add New Service</h3>
                    <form action="manage_services.php" method="POST">
                        <input type="hidden" name="action" value="add">
                        <div class="form-group">
                            <input type="text" name="name" required placeholder="Service Name">
                        </div>
                        <div class="form-group">
                            <textarea name="description" required placeholder="Service Description"></textarea>
                        </div>
                        <div class="form-group">
                            <input type="number" name="duration" required placeholder="Duration (minutes)">
                        </div>
                        <div class="form-group">
                            <input type="number" name="price" step="0.01" required placeholder="Price">
                        </div>
                        <button type="submit" class="btn">Add Service</button>
                    </form>
                </div>

                <div class="services-list">
                    <h3>Current Services</h3>
                    <div class="services-grid">
                        <?php foreach($services as $service): ?>
                            <div class="service-card">
                                <h4><?php echo htmlspecialchars($service['name']); ?></h4>
                                <p><?php echo htmlspecialchars($service['description']); ?></p>
                                <p><strong>Duration:</strong> <?php echo htmlspecialchars($service['duration']); ?> minutes</p>
                                <p><strong>Price:</strong> $<?php echo htmlspecialchars($service['price']); ?></p>
                                <form action="manage_services.php" method="POST" class="delete-form">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this service?')">Delete</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
