<?php
session_start();
require_once(__DIR__ . '/Model/StudentModel.php');

// Set a valid student session for testing
$_SESSION['user_id'] = 3; // Assuming user ID 3 is a student
$_SESSION['user_role'] = 'student';
$_SESSION['user_name'] = 'Alice Johnson';
$_SESSION['username'] = 'student1';

$studentModel = new StudentModel();

// Get student's enrolled courses
$courses = $studentModel->getStudentCourses($_SESSION['user_id']);

echo "<h2>Current Student Data for Calendar</h2>";
echo "<h3>Number of courses: " . count($courses) . "</h3>";

if (count($courses) > 0) {
    echo "<h3>Course Data Structure:</h3>";
    echo "<pre>";
    foreach ($courses as $i => $course) {
        echo "Course $i:\n";
        echo "  ID: " . ($course['id'] ?? 'N/A') . "\n";
        echo "  Name: " . ($course['class_name'] ?? 'N/A') . "\n";
        echo "  Schedule Days: " . ($course['schedule_days'] ?? 'N/A') . "\n";
        echo "  Schedule Time: " . ($course['schedule_time'] ?? 'N/A') . "\n";
        echo "  Start Date: " . ($course['start_date'] ?? 'N/A') . "\n";
        echo "  End Date: " . ($course['end_date'] ?? 'N/A') . "\n";
        echo "  Duration: " . ($course['schedule_duration'] ?? 'N/A') . "\n";
        echo "  Instructor: " . ($course['instructor_name'] ?? 'N/A') . "\n";
        echo "\n";
    }
    echo "</pre>";
    
    // Create a simple calendar test
    echo "<h3>Calendar Test Data (JSON):</h3>";
    echo "<pre>";
    echo json_encode([
        'courses' => $courses,
        'attendance' => [],
        'stats' => [],
        'payments' => []
    ], JSON_PRETTY_PRINT);
    echo "</pre>";
} else {
    echo "<p>No courses found for user ID 3</p>";
    
    // Let's check if there are any users and enrollments
    require_once(__DIR__ . '/Base/Database.php');
    $db = new Database();
    $connection = $db->getConnection();
    
    echo "<h3>Debug: Users table</h3>";
    $result = $connection->query("SELECT id, username, full_name, role FROM users WHERE role = 'student'");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "<p>User ID: {$row['id']}, Username: {$row['username']}, Name: {$row['full_name']}, Role: {$row['role']}</p>";
        }
    }
    
    echo "<h3>Debug: Enrollments table</h3>";
    $result = $connection->query("SELECT * FROM enrollments");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            echo "<p>Student ID: {$row['student_id']}, Class ID: {$row['class_id']}, Status: {$row['status']}</p>";
        }
    }
}

echo "<br><a href='/webapp/student' target='_blank'>Test Student Dashboard</a>";
?>
