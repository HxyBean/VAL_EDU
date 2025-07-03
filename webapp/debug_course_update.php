<?php
// This debug file is no longer needed - course update is working
// You can delete this file
echo "Course update functionality has been fixed and is working properly.\n";
echo "The closeCourse function already exists in AdminModel.php\n";
echo "You can now delete this debug file.\n";
?>
echo "Display errors: " . ini_get('display_errors') . "\n";
echo "Log errors: " . ini_get('log_errors') . "\n";
echo "Error log: " . ini_get('error_log') . "\n";

// Test database connection
try {
    require_once(__DIR__ . '/Model/AdminModel.php');
    $model = new AdminModel();
    echo "Database connection: OK\n";
} catch (Exception $e) {
    echo "Database connection error: " . $e->getMessage() . "\n";
    exit;
}

// Test basic update data
$testData = [
    'class_name' => 'Test',
    'class_year' => 2025,
    'class_level' => 'Sơ cấp',
    'subject' => 'IELTS Speaking',
    'description' => 'Test description',
    'max_students' => 15,
    'sessions_total' => 30,
    'price_per_session' => 300000,
    'schedule_time' => '09:00',
    'schedule_duration' => 120,
    'schedule_days' => 'T2,T4,T6',
    'start_date' => '2025-01-15',
    'end_date' => '2025-06-15',
    'tutor_id' => null
];

echo "Test data prepared\n";
echo json_encode($testData) . "\n";

// Test if we have any courses to update
try {
    echo "\n=== Testing Course Operations ===\n";
    
    // Get all courses first
    $courses = $model->getAllCourses();
    echo "Found " . count($courses) . " courses\n";
    
    if (count($courses) > 0) {
        $firstCourse = $courses[0];
        echo "First course ID: " . $firstCourse['id'] . "\n";
        echo "First course name: " . $firstCourse['class_name'] . "\n";
        
        // Test getting course details
        $courseDetails = $model->getCourseDetails($firstCourse['id']);
        if ($courseDetails) {
            echo "Course details retrieved successfully\n";
            echo "Course tutor_id: " . ($courseDetails['tutor_id'] ?? 'NULL') . "\n";
            
            // Test updating this course
            echo "\n=== Testing Course Update ===\n";
            $updateResult = $model->updateCourse($firstCourse['id'], $testData);
            if ($updateResult) {
                echo "Course update: SUCCESS\n";
            } else {
                echo "Course update: FAILED\n";
            }
        } else {
            echo "Failed to get course details\n";
        }
    } else {
        echo "No courses found to test update\n";
        
        // Try creating a course first
        echo "\n=== Testing Course Creation ===\n";
        $createResult = $model->createCourse($testData);
        if ($createResult) {
            echo "Course created with ID: " . $createResult . "\n";
            
            // Now test updating the newly created course
            $testData['class_name'] = 'Updated Test';
            $updateResult = $model->updateCourse($createResult, $testData);
            if ($updateResult) {
                echo "Course update after creation: SUCCESS\n";
            } else {
                echo "Course update after creation: FAILED\n";
            }
        } else {
            echo "Failed to create test course\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error during course operations: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

// Test database table structure
try {
    echo "\n=== Testing Database Structure ===\n";
    
    // Check if classes table exists and its structure
    $result = $model->getConnection()->query("DESCRIBE classes");
    if ($result) {
        echo "Classes table structure:\n";
        while ($row = $result->fetch_assoc()) {
            echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
    }
    
    // Check if class_tutors table exists
    $result = $model->getConnection()->query("DESCRIBE class_tutors");
    if ($result) {
        echo "\nClass_tutors table structure:\n";
        while ($row = $result->fetch_assoc()) {
            echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error checking database structure: " . $e->getMessage() . "\n";
}

echo "\n=== Debug Complete ===\n";
?>
?>
