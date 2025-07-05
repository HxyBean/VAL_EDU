<?php
// Test creating a course with time "7:00" to see where the bug occurs
require_once 'Base/Database.php';
require_once 'Model/AdminModel.php';

echo "Real Course Creation Test with Time 7:00\n";
echo "=========================================\n";

try {
    $adminModel = new AdminModel();
    
    // Test data with "7:00" time
    $courseData = [
        'class_name' => 'Test Course 7AM',
        'class_year' => 2024,
        'class_level' => 'Beginner',
        'subject' => 'English',
        'description' => 'Test course at 7:00 AM',
        'max_students' => 10,
        'sessions_total' => 20,
        'price_per_session' => 100000,
        'schedule_time' => '7:00',  // This is the problematic input
        'schedule_duration' => 90,
        'schedule_days' => 'T2,T4',
        'start_date' => '2024-08-01',
        'end_date' => '2024-12-31',
        'tutor_id' => null
    ];
    
    echo "Input data:\n";
    echo "schedule_time: '{$courseData['schedule_time']}'\n";
    
    // This should apply the same validation as AdminController
    echo "\nTesting time validation...\n";
    
    if (!empty($courseData['schedule_time'])) {
        // Accept both H:MM and HH:MM formats
        if (preg_match('/^\d{1,2}:\d{2}$/', $courseData['schedule_time'])) {
            // Format to ensure HH:MM format (pad hour with zero if needed)
            $timeComponents = explode(':', $courseData['schedule_time']);
            $hour = str_pad($timeComponents[0], 2, '0', STR_PAD_LEFT);
            $minute = $timeComponents[1];
            $courseData['schedule_time'] = $hour . ':' . $minute;
            echo "✓ Time formatted to: '{$courseData['schedule_time']}'\n";
        } else {
            // Try to convert from other formats
            $time = date('H:i', strtotime($courseData['schedule_time']));
            if ($time === false) {
                throw new Exception('Invalid time format');
            }
            $courseData['schedule_time'] = $time;
            echo "✓ Time converted to: '{$courseData['schedule_time']}'\n";
        }
    }
    
    echo "\nActual course creation:\n";
    $courseId = $adminModel->createCourse($courseData);
    echo "✓ Course created successfully with ID: $courseId\n";
    
    // Verify what was actually stored in the database
    echo "\nVerifying database storage:\n";
    $course = $adminModel->getCourseById($courseId);
    if ($course) {
        echo "Database schedule_time: '{$course['schedule_time']}'\n";
        
        if ($course['schedule_time'] === '07:00:00') {
            echo "✓ SUCCESS: Time stored correctly as 07:00:00\n";
        } else {
            echo "✗ ERROR: Time stored incorrectly as '{$course['schedule_time']}'\n";
        }
    }
    
    // Clean up - delete the test course
    echo "\nCleaning up test course...\n";
    $db = $adminModel->getConnection();
    $deleteStmt = $db->prepare("DELETE FROM classes WHERE id = ?");
    $deleteStmt->bind_param("i", $courseId);
    if ($deleteStmt->execute()) {
        echo "✓ Test course deleted\n";
    }
    $deleteStmt->close();
    
} catch (Exception $e) {
    echo "✗ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

?>
