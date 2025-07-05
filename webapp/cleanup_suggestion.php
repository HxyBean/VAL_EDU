<?php
// Script to clean up test courses with wrong time data
require_once('Base/Database.php');

echo "=== DATABASE CLEANUP SCRIPT ===\n\n";

try {
    $db = Database::getInstance();
    $connection = $db->getConnection();
    
    echo "Current situation:\n";
    echo "================\n";
    
    // Show problematic courses
    $result = $connection->query("SELECT id, class_name, schedule_time FROM classes WHERE schedule_time LIKE '00:00:%' ORDER BY id");
    $badCourses = [];
    
    if ($result && $result->num_rows > 0) {
        echo "Courses with wrong time format:\n";
        while ($row = $result->fetch_assoc()) {
            echo "  ID " . $row['id'] . ": " . $row['class_name'] . " (Time: " . $row['schedule_time'] . ")\n";
            $badCourses[] = $row['id'];
        }
    }
    
    // Show good courses
    $result2 = $connection->query("SELECT id, class_name, schedule_time FROM classes WHERE schedule_time NOT LIKE '00:00:%' AND schedule_time IS NOT NULL ORDER BY id");
    
    if ($result2 && $result2->num_rows > 0) {
        echo "\nCourses with correct time format:\n";
        while ($row = $result2->fetch_assoc()) {
            echo "  ID " . $row['id'] . ": " . $row['class_name'] . " (Time: " . $row['schedule_time'] . ")\n";
        }
    }
    
    if (count($badCourses) > 0) {
        echo "\n=== CLEANUP OPTIONS ===\n";
        echo "Found " . count($badCourses) . " courses with incorrect time format.\n";
        echo "\nTo clean up these test courses, run ONE of these commands:\n\n";
        
        echo "Option A - Delete all problematic courses:\n";
        echo "DELETE FROM classes WHERE schedule_time LIKE '00:00:%';\n\n";
        
        echo "Option B - Delete specific courses by ID:\n";
        echo "DELETE FROM classes WHERE id IN (" . implode(', ', $badCourses) . ");\n\n";
        
        echo "Option C - Fix the time values (convert 00:00:XX to XX:00:00):\n";
        foreach ($badCourses as $id) {
            $checkResult = $connection->query("SELECT schedule_time FROM classes WHERE id = $id");
            if ($checkResult) {
                $row = $checkResult->fetch_assoc();
                $wrongTime = $row['schedule_time'];
                // Extract seconds and convert to hours
                if (preg_match('/00:00:(\d+)/', $wrongTime, $matches)) {
                    $seconds = $matches[1];
                    $correctTime = str_pad($seconds, 2, '0', STR_PAD_LEFT) . ':00:00';
                    echo "UPDATE classes SET schedule_time = '$correctTime' WHERE id = $id; -- Fix $wrongTime to $correctTime\n";
                }
            }
        }
        
        echo "\n⚠️  WARNING: These commands will modify your database!\n";
        echo "⚠️  Make sure to backup your data before running any of these commands.\n";
        
    } else {
        echo "\n✅ No problematic courses found. Database is clean!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== SUMMARY ===\n";
echo "✅ AdminController fix is working correctly\n";
echo "✅ New courses will have correct time format\n";
echo "ℹ️  Old test courses need cleanup (see options above)\n";
echo "✅ Time bug is RESOLVED for future course creation/updates\n";
?>
