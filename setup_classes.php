<?php
require_once 'config.php';

try {
    // Create classes table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS classes (
        id INT PRIMARY KEY AUTO_INCREMENT,
        class_name VARCHAR(100) NOT NULL,
        instructor VARCHAR(100) NOT NULL,
        date DATE NOT NULL,
        time TIME NOT NULL,
        duration INT NOT NULL,
        capacity INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // Sample class data
    $classes = [
        ['Yoga Flow', 'Sarah Chen', '2025-04-28', '07:00:00', 60, 20],
        ['HIIT Training', 'Mike Johnson', '2025-04-28', '09:00:00', 45, 15],
        ['Pilates', 'Emma Davis', '2025-04-29', '08:30:00', 60, 18],
        ['Zumba Dance', 'Maria Rodriguez', '2025-04-29', '17:00:00', 45, 25],
        ['Strength Training', 'John Smith', '2025-04-30', '10:00:00', 60, 15],
        ['Spin Class', 'Lisa Wong', '2025-04-30', '16:00:00', 45, 20],
        ['Boxing Fitness', 'James Wilson', '2025-05-01', '08:00:00', 60, 15],
        ['Core Power', 'Anna Kim', '2025-05-01', '14:00:00', 45, 20],
        ['Meditation', 'David Brown', '2025-05-02', '07:30:00', 30, 25],
        ['CrossFit', 'Alex Turner', '2025-05-02', '18:00:00', 60, 12]
    ];

    // Insert classes
    $stmt = $pdo->prepare("INSERT INTO classes (class_name, instructor, date, time, duration, capacity) VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach ($classes as $class) {
        $stmt->execute($class);
    }

    echo "Classes added successfully!";

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
