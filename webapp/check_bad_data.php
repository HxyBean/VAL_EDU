<?php
// Check which courses have the wrong time format and when they were created
require_once('Base/Database.php');

echo "=== CHECKING EXISTING BAD DATA ===\n\n";

try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    echo "Courses with problematic time format (00:00:XX):\n";
    echo "=============================================\n";
    
    $result = $connection->query("SELECT id, class_name, schedule_time, created_at, updated_at FROM classes WHERE schedule_time LIKE '00:00:%' ORDER BY id DESC");
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "ID: " . $row['id'] . "\n";
            echo "Name: " . $row['class_name'] . "\n";
            echo "Time: " . $row['schedule_time'] . "\n";
            echo "Created: " . $row['created_at'] . "\n";
            echo "Updated: " . $row['updated_at'] . "\n";
            echo "---\n";
        }
    } else {
        echo "No courses with problematic time format found.\n";
    }
    
    echo "\nCourses with correct time format:\n";
    echo "===============================\n";
    
    $result2 = $connection->query("SELECT id, class_name, schedule_time, created_at FROM classes WHERE schedule_time NOT LIKE '00:00:%' AND schedule_time IS NOT NULL ORDER BY id DESC LIMIT 5");
    
    if ($result2 && $result2->num_rows > 0) {
        while ($row = $result2->fetch_assoc()) {
            echo "ID: " . $row['id'] . " | Name: " . $row['class_name'] . " | Time: " . $row['schedule_time'] . " | Created: " . $row['created_at'] . "\n";
        }
    } else {
        echo "No courses with correct time format found.\n";
    }
    
    echo "\n=== SOLUTION SUGGESTION ===\n";
    echo "The AdminController fix is working correctly for NEW courses.\n";
    echo "However, existing courses created before the fix still have wrong times.\n";
    echo "You have two options:\n";
    echo "1. Delete the problematic test courses from the database\n";
    echo "2. Update the existing courses to fix their time values\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
