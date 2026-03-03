<?php
session_start();
require_once(__DIR__ . '/Model/StudentModel.php');

// Simulate a logged-in student
$_SESSION['user_id'] = 3; // Assuming user ID 3 is a student
$_SESSION['user_role'] = 'student';

$studentModel = new StudentModel();

// Get student's enrolled courses
$courses = $studentModel->getStudentCourses($_SESSION['user_id']);

echo "<h2>Debug: Student Course Data</h2>";
echo "<h3>Number of courses: " . count($courses) . "</h3>";

if (count($courses) > 0) {
    echo "<h3>First course structure:</h3>";
    echo "<pre>";
    print_r($courses[0]);
    echo "</pre>";
    
    echo "<h3>All courses:</h3>";
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
        echo "\n";
    }
    echo "</pre>";
} else {
    echo "<p>No courses found!</p>";
}

// Also check the database directly
require_once(__DIR__ . '/Base/Database.php');
$db = new Database();
$connection = $db->getConnection();

echo "<h3>Direct database query:</h3>";
$sql = "SELECT * FROM classes LIMIT 3";
$result = $connection->query($sql);
if ($result) {
    echo "<pre>";
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
    echo "</pre>";
} else {
    echo "Query failed: " . $connection->error;
}

// Check enrollments
echo "<h3>Enrollments for user ID 3:</h3>";
$sql = "SELECT * FROM enrollments WHERE student_id = 3";
$result = $connection->query($sql);
if ($result) {
    echo "<pre>";
    while ($row = $result->fetch_assoc()) {
        print_r($row);
    }
    echo "</pre>";
} else {
    echo "Query failed: " . $connection->error;
}
?>
