<?php
// Test the AdminController endpoint directly
session_start();

// Simulate admin login
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';

// Simulate POST request to create course
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'class_name' => 'Test Course 7AM Controller',
    'class_year' => 2024,
    'class_level' => 'Beginner',
    'subject' => 'English',
    'description' => 'Test course at 7:00 AM via controller',
    'max_students' => 10,
    'sessions_total' => 20,
    'price_per_session' => 100000,
    'schedule_time' => '7:00',  // This is the problematic input
    'schedule_duration' => 90,
    'schedule_days' => 'T2,T4',
    'start_date' => '2024-08-01',
    'end_date' => '2024-12-31',
    'tutor_id' => ''
];

echo "Testing AdminController::createCourse() with time '7:00'\n";
echo "=====================================================\n";

require_once 'Controller/AdminController.php';

try {
    $adminController = new AdminController();
    
    // Capture the JSON output
    ob_start();
    $adminController->createCourse();
    $output = ob_get_clean();
    
    echo "Controller output:\n";
    echo $output . "\n";
    
    $result = json_decode($output, true);
    if ($result && $result['success']) {
        echo "\n✓ Course created successfully via AdminController\n";
        echo "Course ID: " . $result['course_id'] . "\n";
        
        // Verify what was stored in the database
        require_once 'Model/AdminModel.php';
        $adminModel = new AdminModel();
        $course = $adminModel->getCourseById($result['course_id']);
        
        if ($course) {
            echo "Database schedule_time: '{$course['schedule_time']}'\n";
            
            if ($course['schedule_time'] === '07:00:00') {
                echo "✓ SUCCESS: Time stored correctly as 07:00:00 through AdminController\n";
            } else {
                echo "✗ ERROR: Time stored incorrectly as '{$course['schedule_time']}' through AdminController\n";
            }
        }
        
        // Clean up
        $db = $adminModel->getConnection();
        $deleteStmt = $db->prepare("DELETE FROM classes WHERE id = ?");
        $deleteStmt->bind_param("i", $result['course_id']);
        $deleteStmt->execute();
        $deleteStmt->close();
        echo "✓ Test course deleted\n";
    } else {
        echo "✗ ERROR: " . ($result['message'] ?? 'Unknown error') . "\n";
    }
    
} catch (Exception $e) {
    echo "✗ EXCEPTION: " . $e->getMessage() . "\n";
}

?>
