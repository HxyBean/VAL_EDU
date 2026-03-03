<?php
require_once 'Base/Database.php';

echo "<h1>Database Payment Records Check</h1>";

// Initialize database
$database = Database::getInstance();
$db = $database->getConnection();

// Check all payments in the database
echo "<h2>All Payment Records</h2>";
$sql = "SELECT p.*, u.full_name as student_name, c.class_name 
        FROM payments p 
        LEFT JOIN users u ON p.student_id = u.id 
        LEFT JOIN classes c ON p.class_id = c.id 
        ORDER BY p.created_at DESC";

$result = $db->query($sql);
if ($result) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Student</th><th>Class</th><th>Amount</th><th>Status</th><th>Payment Date</th><th>Payer ID</th><th>Created At</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['id'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($row['student_name'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['class_name'] ?? 'N/A') . "</td>";
        echo "<td>$" . htmlspecialchars($row['amount'] ?? '0') . "</td>";
        echo "<td>" . htmlspecialchars($row['status'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($row['payment_date'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['payer_id'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($row['created_at'] ?? '') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<p><strong>Total payment records: " . $result->num_rows . "</strong></p>";
} else {
    echo "Error: " . $db->error;
}

// Check recent payments (last 10)
echo "<h2>Recent Payments (Last 10)</h2>";
$sql = "SELECT * FROM payments ORDER BY created_at DESC LIMIT 10";
$result = $db->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        echo "<div style='border: 1px solid #ccc; margin: 10px 0; padding: 10px;'>";
        echo "<strong>Payment ID:</strong> " . $row['id'] . "<br>";
        echo "<strong>Student ID:</strong> " . $row['student_id'] . "<br>";
        echo "<strong>Class ID:</strong> " . $row['class_id'] . "<br>";
        echo "<strong>Amount:</strong> $" . $row['amount'] . "<br>";
        echo "<strong>Status:</strong> " . $row['status'] . "<br>";
        echo "<strong>Created:</strong> " . $row['created_at'] . "<br>";
        if ($row['payment_date']) {
            echo "<strong>Paid:</strong> " . $row['payment_date'] . "<br>";
        }
        if ($row['payer_id']) {
            echo "<strong>Payer ID:</strong> " . $row['payer_id'] . "<br>";
        }
        echo "</div>";
    }
} else {
    echo "Error: " . $db->error;
}
?>
