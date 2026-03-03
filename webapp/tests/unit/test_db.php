<?php
require_once('Base/Database.php');

try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    echo "<h2>Database Connection Test</h2>";
    echo "<p style='color: green;'>✅ Connected successfully to ValEduDatabase!</p>";
    
    // Test query to show users
    $result = $conn->query("SELECT id, username, full_name, role FROM users");
    
    if ($result && $result->num_rows > 0) {
        echo "<h3>Sample Users in Database:</h3>";
        echo "<table border='1' style='border-collapse: collapse; margin: 10px 0; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Username</th><th>Full Name</th><th>Role</th><th>Action</th></tr>";
        
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['username']}</td>";
            echo "<td>{$row['full_name']}</td>";
            echo "<td><strong>{$row['role']}</strong></td>";
            echo "<td><a href='/webapp/login' target='_blank'>Test Login</a></td>";
            echo "</tr>";
        }
        echo "</table>";
        
        echo "<h3>Test Login Credentials:</h3>";
        echo "<ul>";
        echo "<li><strong>Admin:</strong> username = <code>admin</code>, password = <code>admin123</code></li>";
        echo "<li><strong>Tutor:</strong> username = <code>tutor1</code>, password = <code>tutor123</code></li>";
        echo "<li><strong>Student:</strong> username = <code>student1</code>, password = <code>student123</code></li>";
        echo "<li><strong>Parent:</strong> username = <code>parent1</code>, password = <code>parent123</code></li>";
        echo "</ul>";
        
        echo "<div style='margin: 20px 0;'>";
        echo "<a href='/webapp/login' style='background: #002F6C; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>→ Test Login System</a>";
        echo "<a href='/webapp/register' style='background: #0070C0; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>→ Test Registration</a>";
        echo "<a href='/webapp/' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 5px;'>→ Go to Home Page</a>";
        echo "</div>";
        
    } else {
        echo "<p style='color: red;'>❌ No users found in database!</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $e->getMessage() . "</p>";
    echo "<p><strong>Common fixes:</strong></p>";
    echo "<ul>";
    echo "<li>Make sure XAMPP MySQL service is running</li>";
    echo "<li>Check if ValEduDatabase exists in phpMyAdmin</li>";
    echo "<li>Import the ValEduDatabase.sql file</li>";
    echo "<li>Verify database credentials in Base/Database.php</li>";
    echo "</ul>";
}
?>