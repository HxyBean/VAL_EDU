<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once(__DIR__ . '/Model/AdminModel.php');

try {
    $adminModel = new AdminModel();
    
    // Test data for creating a course
    $testData = [
        'class_name' => 'Test Time Fix Course',
        'class_year' => 2025,
        'class_level' => 'Trung cấp',
        'subject' => 'IELTS Writing',
        'description' => 'Test course to verify time fix',
        'max_students' => 12,
        'sessions_total' => 24,
        'price_per_session' => 250000,
        'schedule_time' => '08:00:00',  // This should work now
        'schedule_duration' => 90,
        'schedule_days' => 'T2,T4',
        'start_date' => '2025-08-01',
        'end_date' => '2025-12-31',
        'tutor_id' => null
    ];
    
    echo "Testing createCourse...\n";
    $courseId = $adminModel->createCourse($testData);
    
    if ($courseId) {
        echo "SUCCESS: Course created with ID: $courseId\n";
        
        // Verify the time was stored correctly
        $course = $adminModel->getCourseById($courseId);
        if ($course) {
            echo "Verification: schedule_time stored as '" . $course['schedule_time'] . "'\n";
            echo "Expected: '08:00:00'\n";
            echo "Match: " . ($course['schedule_time'] === '08:00:00' ? 'YES' : 'NO') . "\n";
        }
        
        // Clean up - delete the test course
        echo "Cleaning up test data...\n";
        $db = $adminModel->getConnection();
        $deleteStmt = $db->prepare("DELETE FROM classes WHERE id = ?");
        $deleteStmt->bind_param("i", $courseId);
        $deleteStmt->execute();
        echo "Test course deleted.\n";
        
    } else {
        echo "FAILURE: createCourse returned null\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
