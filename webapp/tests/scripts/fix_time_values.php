<?php
require_once(__DIR__ . '/Model/AdminModel.php');

echo "=== Fixing Bad Time Values ===\n";

$adminModel = new AdminModel();
$db = $adminModel->getConnection();

// Fix specific bad time values
$fixes = [
    // Map bad values to correct time values
    '00:00:05' => '05:00:00',
    '00:00:07' => '07:00:00',
    '00:00:08' => '08:00:00',
    '00:00:09' => '09:00:00',
    '00:00:10' => '10:00:00',
    '00:00:18' => '18:00:00'
];

foreach ($fixes as $badTime => $goodTime) {
    $sql = "UPDATE classes SET schedule_time = ? WHERE schedule_time = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("ss", $goodTime, $badTime);
    
    if ($stmt->execute()) {
        $affected = $stmt->affected_rows;
        if ($affected > 0) {
            echo "✅ Fixed $affected course(s): '$badTime' -> '$goodTime'\n";
        } else {
            echo "⚪ No courses found with time '$badTime'\n";
        }
    } else {
        echo "❌ Error fixing '$badTime': " . $stmt->error . "\n";
    }
    $stmt->close();
}

echo "\n=== Verification ===\n";

// Check for remaining bad values
$result = $db->query("
    SELECT id, class_name, schedule_time 
    FROM classes 
    WHERE schedule_time IS NOT NULL 
    AND (
        schedule_time NOT REGEXP '^[0-9]{2}:[0-9]{2}:[0-9]{2}$'
        OR schedule_time LIKE '00:00:%'
    )
    ORDER BY id
");

if ($result && $result->num_rows > 0) {
    echo "⚠️  Still found bad time values:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- Course ID {$row['id']}: '{$row['class_name']}' has time '{$row['schedule_time']}'\n";
    }
} else {
    echo "✅ All time values are now properly formatted!\n";
}

echo "\n=== All Times After Fix ===\n";
$result = $db->query("SELECT id, class_name, schedule_time FROM classes WHERE schedule_time IS NOT NULL ORDER BY schedule_time, id");

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "Course ID {$row['id']}: '{$row['class_name']}' -> {$row['schedule_time']}\n";
    }
}
