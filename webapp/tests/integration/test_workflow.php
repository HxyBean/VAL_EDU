<?php
// Test the actual user workflow including form population and update
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Set up session as admin
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';

require_once(__DIR__ . '/Model/AdminModel.php');
require_once(__DIR__ . '/Controller/AdminController.php');

echo "=== Testing Complete User Workflow ===\n";

try {
    $adminModel = new AdminModel();
    
    // Step 1: Create a test course
    echo "Step 1: Creating test course...\n";
    $createData = [
        'class_name' => 'Workflow Test Course',
        'class_year' => 2025,
        'class_level' => 'Sơ cấp',
        'subject' => 'IELTS Speaking',
        'description' => 'Test course for workflow',
        'max_students' => 15,
        'sessions_total' => 30,
        'price_per_session' => 300000,
        'schedule_time' => '08:30:00',
        'schedule_duration' => 120,
        'schedule_days' => 'T2,T4,T6',
        'start_date' => '2025-01-15',
        'end_date' => '2025-06-15',
        'tutor_id' => null
    ];
    
    $courseId = $adminModel->createCourse($createData);
    echo "✅ Course created with ID: $courseId\n";
    
    // Step 2: Simulate getting course details (like the frontend does)
    echo "\nStep 2: Getting course details...\n";
    $courseDetails = $adminModel->getCourseDetails($courseId);
    
    if ($courseDetails) {
        echo "✅ Course details retrieved\n";
        echo "Original time in database: " . $courseDetails['schedule_time'] . "\n";
        
        // Step 3: Simulate JavaScript time formatting (HH:MM:SS -> HH:MM)
        $timeForInput = substr($courseDetails['schedule_time'], 0, 5);
        echo "Time formatted for HTML input: " . $timeForInput . "\n";
        
        // Step 4: Simulate form submission with problematic time value
        echo "\nStep 3: Simulating form submission with time '7:00'...\n";
        
        $_POST = [
            'course_id' => (string)$courseId,
            'class_name' => 'Workflow Test Course Updated',
            'class_year' => '2025',
            'class_level' => 'Sơ cấp',
            'subject' => 'IELTS Speaking',
            'description' => 'Updated description',
            'max_students' => '20',
            'sessions_total' => '25',
            'price_per_session' => '350000',
            'schedule_time' => '7:00', // This is the problematic format
            'schedule_duration' => '90',
            'schedule_days' => 'T3,T5,T7',
            'start_date' => '2025-02-01',
            'end_date' => '2025-07-01',
            'tutor_id' => ''
        ];
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        // Capture the controller response
        ob_start();
        
        $adminController = new AdminController();
        $adminController->updateCourse();
        
        $response = ob_get_clean();
        
        echo "API Response: " . trim($response) . "\n";
        
        // Step 5: Check what was stored
        echo "\nStep 4: Checking stored result...\n";
        $db = $adminModel->getConnection();
        $result = $db->query("SELECT schedule_time FROM classes WHERE id = $courseId");
        
        if ($result && $row = $result->fetch_assoc()) {
            echo "Time stored in database: " . $row['schedule_time'] . "\n";
            
            if ($row['schedule_time'] === '07:00:00') {
                echo "✅ SUCCESS: Time '7:00' correctly stored as '07:00:00'\n";
            } else {
                echo "❌ ERROR: Expected '07:00:00' but got '" . $row['schedule_time'] . "'\n";
            }
        }
        
        // Step 6: Test with another problematic time
        echo "\nStep 5: Testing with time '09:15'...\n";
        $_POST['schedule_time'] = '09:15';
        
        ob_start();
        $adminController = new AdminController();
        $adminController->updateCourse();
        $response = ob_get_clean();
        
        echo "API Response: " . trim($response) . "\n";
        
        $result = $db->query("SELECT schedule_time FROM classes WHERE id = $courseId");
        if ($result && $row = $result->fetch_assoc()) {
            echo "Time stored in database: " . $row['schedule_time'] . "\n";
            
            if ($row['schedule_time'] === '09:15:00') {
                echo "✅ SUCCESS: Time '09:15' correctly stored as '09:15:00'\n";
            } else {
                echo "❌ ERROR: Expected '09:15:00' but got '" . $row['schedule_time'] . "'\n";
            }
        }
        
    } else {
        echo "❌ ERROR: Could not retrieve course details\n";
    }
    
    // Clean up
    echo "\n--- Cleanup ---\n";
    $db = $adminModel->getConnection();
    $db->query("DELETE FROM classes WHERE class_name LIKE 'Workflow Test Course%'");
    echo "✅ Cleanup completed\n";
    
} catch (Exception $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
