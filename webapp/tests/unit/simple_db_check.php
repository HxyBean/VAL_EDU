<?php
require_once(__DIR__ . '/Base/Database.php');

try {
    $db = new Database();
    $connection = $db->getConnection();
    
    echo "<h2>Database Check</h2>";
    
    // Check if we can connect
    if ($connection) {
        echo "<p>✓ Database connection successful</p>";
        
        // Check for users
        $result = $connection->query("SELECT COUNT(*) as count FROM users");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "<p>Users count: " . $row['count'] . "</p>";
        }
        
        // Check for classes
        $result = $connection->query("SELECT COUNT(*) as count FROM classes");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "<p>Classes count: " . $row['count'] . "</p>";
        }
        
        // Check for enrollments
        $result = $connection->query("SELECT COUNT(*) as count FROM enrollments");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "<p>Enrollments count: " . $row['count'] . "</p>";
        }
        
        // Show some sample data
        echo "<h3>Sample Classes:</h3>";
        $result = $connection->query("SELECT id, class_name, schedule_days, schedule_time, start_date, end_date FROM classes LIMIT 5");
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
        } else {
            echo "<p>No classes found</p>";
        }
        
        // Show enrollments
        echo "<h3>Sample Enrollments:</h3>";
        $result = $connection->query("SELECT e.*, u.full_name, c.class_name FROM enrollments e JOIN users u ON e.student_id = u.id JOIN classes c ON e.class_id = c.id LIMIT 5");
        if ($result && $result->num_rows > 0) {
            echo "<table border='1'>";
            echo "<tr><th>Student</th><th>Class</th><th>Status</th><th>Enrollment Date</th></tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['full_name'] . "</td>";
                echo "<td>" . $row['class_name'] . "</td>";
                echo "<td>" . $row['status'] . "</td>";
                echo "<td>" . $row['enrollment_date'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No enrollments found</p>";
        }
        
    } else {
        echo "<p>✗ Database connection failed</p>";
    }
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
}
?>
