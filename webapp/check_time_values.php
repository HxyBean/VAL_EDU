<?php
require_once(__DIR__ . '/Model/AdminModel.php');

echo "=== Checking for Bad Time Values ===\n";

$adminModel = new AdminModel();
$db = $adminModel->getConnection();

// Check for times that don't match HH:MM:SS format or have wrong values
$result = $db->query("
    SELECT id, class_name, schedule_time 
    FROM classes 
    WHERE schedule_time IS NOT NULL 
    AND (
        schedule_time NOT REGEXP '^[0-9]{2}:[0-9]{2}:[0-9]{2}$'
        OR schedule_time LIKE '00:00:%'
        OR TIME(schedule_time) != schedule_time
    )
    ORDER BY id
");

if ($result && $result->num_rows > 0) {
    echo "Found courses with bad time values:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- Course ID {$row['id']}: '{$row['class_name']}' has time '{$row['schedule_time']}'\n";
    }
    
    echo "\n=== Suggested Fix SQL ===\n";
    echo "-- Run this SQL to fix any remaining bad time values:\n";
    echo "UPDATE classes SET schedule_time = '07:00:00' WHERE schedule_time LIKE '00:00:07%';\n";
    echo "UPDATE classes SET schedule_time = '08:00:00' WHERE schedule_time LIKE '00:00:08%';\n";
    echo "UPDATE classes SET schedule_time = '09:00:00' WHERE schedule_time LIKE '00:00:09%';\n";
    echo "-- Add more lines as needed for other hours\n";
    
} else {
    echo "✅ No bad time values found! All times are properly formatted.\n";
}

// Show all current times for verification
echo "\n=== All Current Times ===\n";
$result = $db->query("SELECT id, class_name, schedule_time FROM classes WHERE schedule_time IS NOT NULL ORDER BY id");

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "Course ID {$row['id']}: '{$row['class_name']}' -> {$row['schedule_time']}\n";
    }
} else {
    echo "No courses found.\n";
}
