<?php
require_once(__DIR__ . '/Base/Database.php');

try {
    $db = new Database();
    $connection = $db->getConnection();
    
    echo "<h2>Creating Sample Data</h2>";
    
    if (!$connection) {
        die("Database connection failed");
    }
    
    // Insert sample users if they don't exist
    $users = [
        ['admin', 'admin@valedu.com', password_hash('admin123', PASSWORD_DEFAULT), 'Admin User', 'admin'],
        ['tutor1', 'tutor1@valedu.com', password_hash('tutor123', PASSWORD_DEFAULT), 'John Smith', 'tutor'],
        ['student1', 'student1@valedu.com', password_hash('student123', PASSWORD_DEFAULT), 'Alice Johnson', 'student'],
        ['student2', 'student2@valedu.com', password_hash('student123', PASSWORD_DEFAULT), 'Bob Wilson', 'student'],
    ];
    
    foreach ($users as $user) {
        $stmt = $connection->prepare("INSERT IGNORE INTO users (username, email, password, full_name, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $user[0], $user[1], $user[2], $user[3], $user[4]);
        $result = $stmt->execute();
        if ($result) {
            echo "<p>✓ Created user: " . $user[3] . " (" . $user[4] . ")</p>";
        }
    }
    
    // Insert sample classes if they don't exist
    $classes = [
        ['IELTS_A1', 2024, 'Beginner', 'IELTS', 'Basic IELTS preparation', 15, 30, 150000, '09:00:00', 120, 'T2,T4,T6', '2024-01-15', '2024-06-15', 'active'],
        ['IELTS_A2', 2024, 'Intermediate', 'IELTS', 'Intermediate IELTS preparation', 12, 25, 180000, '14:00:00', 120, 'T3,T5', '2024-02-01', '2024-07-01', 'active'],
        ['TOEIC_B1', 2024, 'Advanced', 'TOEIC', 'Advanced TOEIC preparation', 10, 20, 200000, '18:00:00', 90, 'T2,T4', '2024-01-20', '2024-05-20', 'active'],
    ];
    
    foreach ($classes as $class) {
        $stmt = $connection->prepare("INSERT IGNORE INTO classes (class_name, class_year, class_level, subject, description, max_students, sessions_total, price_per_session, schedule_time, schedule_duration, schedule_days, start_date, end_date, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("siisssiisisss", $class[0], $class[1], $class[2], $class[3], $class[4], $class[5], $class[6], $class[7], $class[8], $class[9], $class[10], $class[11], $class[12], $class[13]);
        $result = $stmt->execute();
        if ($result) {
            echo "<p>✓ Created class: " . $class[0] . "</p>";
        }
    }
    
    // Get user IDs
    $result = $connection->query("SELECT id, username FROM users WHERE role IN ('tutor', 'student')");
    $user_ids = [];
    while ($row = $result->fetch_assoc()) {
        $user_ids[$row['username']] = $row['id'];
    }
    
    // Get class IDs
    $result = $connection->query("SELECT id, class_name FROM classes");
    $class_ids = [];
    while ($row = $result->fetch_assoc()) {
        $class_ids[$row['class_name']] = $row['id'];
    }
    
    // Assign tutor to classes
    if (isset($user_ids['tutor1']) && isset($class_ids['IELTS_A1'])) {
        $stmt = $connection->prepare("INSERT IGNORE INTO class_tutors (tutor_id, class_id, assigned_date, salary_per_session) VALUES (?, ?, CURDATE(), 100000)");
        $stmt->bind_param("ii", $user_ids['tutor1'], $class_ids['IELTS_A1']);
        $stmt->execute();
        echo "<p>✓ Assigned tutor to IELTS_A1</p>";
    }
    
    if (isset($user_ids['tutor1']) && isset($class_ids['IELTS_A2'])) {
        $stmt = $connection->prepare("INSERT IGNORE INTO class_tutors (tutor_id, class_id, assigned_date, salary_per_session) VALUES (?, ?, CURDATE(), 120000)");
        $stmt->bind_param("ii", $user_ids['tutor1'], $class_ids['IELTS_A2']);
        $stmt->execute();
        echo "<p>✓ Assigned tutor to IELTS_A2</p>";
    }
    
    // Enroll students in classes
    if (isset($user_ids['student1']) && isset($class_ids['IELTS_A1'])) {
        $stmt = $connection->prepare("INSERT IGNORE INTO enrollments (student_id, class_id, enrollment_date, total_fee) VALUES (?, ?, CURDATE(), 4500000)");
        $stmt->bind_param("ii", $user_ids['student1'], $class_ids['IELTS_A1']);
        $stmt->execute();
        echo "<p>✓ Enrolled student1 in IELTS_A1</p>";
    }
    
    if (isset($user_ids['student1']) && isset($class_ids['TOEIC_B1'])) {
        $stmt = $connection->prepare("INSERT IGNORE INTO enrollments (student_id, class_id, enrollment_date, total_fee) VALUES (?, ?, CURDATE(), 4000000)");
        $stmt->bind_param("ii", $user_ids['student1'], $class_ids['TOEIC_B1']);
        $stmt->execute();
        echo "<p>✓ Enrolled student1 in TOEIC_B1</p>";
    }
    
    if (isset($user_ids['student2']) && isset($class_ids['IELTS_A2'])) {
        $stmt = $connection->prepare("INSERT IGNORE INTO enrollments (student_id, class_id, enrollment_date, total_fee) VALUES (?, ?, CURDATE(), 4500000)");
        $stmt->bind_param("ii", $user_ids['student2'], $class_ids['IELTS_A2']);
        $stmt->execute();
        echo "<p>✓ Enrolled student2 in IELTS_A2</p>";
    }
    
    echo "<h3>Sample data creation completed!</h3>";
    echo "<p>Login credentials:</p>";
    echo "<ul>";
    echo "<li>Student: student1@valedu.com / student123</li>";
    echo "<li>Student: student2@valedu.com / student123</li>";
    echo "<li>Tutor: tutor1@valedu.com / tutor123</li>";
    echo "<li>Admin: admin@valedu.com / admin123</li>";
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Full error: " . print_r($e, true) . "</p>";
}
?>
