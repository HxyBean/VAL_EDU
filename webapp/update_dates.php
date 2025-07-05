<?php
require_once(__DIR__ . '/Base/Database.php');

try {
    $db = new Database();
    $connection = $db->getConnection();
    
    echo "<h2>Updating Sample Data with Current Dates</h2>";
    
    if (!$connection) {
        die("Database connection failed");
    }
    
    // Update existing classes with current dates (July 2025 onwards)
    $updates = [
        "UPDATE classes SET start_date = '2025-07-01', end_date = '2025-12-15' WHERE class_name = 'IELTS_A1'",
        "UPDATE classes SET start_date = '2025-07-01', end_date = '2025-11-30' WHERE class_name = 'IELTS_A2'",
        "UPDATE classes SET start_date = '2025-07-01', end_date = '2025-10-20' WHERE class_name = 'TOEIC_B1'"
    ];
    
    foreach ($updates as $sql) {
        if ($connection->query($sql)) {
            echo "<p>✓ Updated class dates</p>";
        } else {
            echo "<p>✗ Failed to update: " . $connection->error . "</p>";
        }
    }
    
    // Show updated data
    echo "<h3>Updated Classes:</h3>";
    $result = $connection->query("SELECT id, class_name, schedule_days, schedule_time, start_date, end_date FROM classes");
    if ($result && $result->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Name</th><th>Schedule Days</th><th>Schedule Time</th><th>Start Date</th><th>End Date</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td>" . $row['class_name'] . "</td>";
            echo "<td>" . ($row['schedule_days'] ?? 'NULL') . "</td>";
            echo "<td>" . ($row['schedule_time'] ?? 'NULL') . "</td>";
            echo "<td>" . ($row['start_date'] ?? 'NULL') . "</td>";
            echo "<td>" . ($row['end_date'] ?? 'NULL') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    echo "<h3>Classes update completed!</h3>";
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
