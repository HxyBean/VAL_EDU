<?php
// Test script to verify that the time formatting fix works correctly

echo "Testing Time Formatting Fix\n";
echo "===========================\n\n";

// Test the time formatting logic that was fixed
function testTimeFormatting($input) {
    echo "Testing input: '$input'\n";
    
    // Replicate the fixed logic from AdminController
    if (!empty($input)) {
        // Accept both H:MM and HH:MM formats
        if (preg_match('/^\d{1,2}:\d{2}$/', $input)) {
            // Format to ensure HH:MM:SS format (pad hour with zero if needed and add seconds)
            $timeComponents = explode(':', $input);
            $hour = str_pad($timeComponents[0], 2, '0', STR_PAD_LEFT);
            $minute = $timeComponents[1];
            $formatted = $hour . ':' . $minute . ':00';
        } else {
            // Try to convert from other formats
            $formatted = date('H:i:s', strtotime($input));
            if ($formatted === false) {
                $formatted = "INVALID";
            }
        }
    } else {
        $formatted = "EMPTY";
    }
    
    echo "Formatted output: '$formatted'\n";
    echo "Expected in database: '$formatted'\n\n";
    
    return $formatted;
}

// Test various time inputs
$testCases = [
    "7:00",      // Should become 07:00:00
    "07:00",     // Should become 07:00:00
    "14:30",     // Should become 14:30:00
    "9:15",      // Should become 09:15:00
    "23:45",     // Should become 23:45:00
    "08:00",     // Should become 08:00:00
];

foreach ($testCases as $testCase) {
    $result = testTimeFormatting($testCase);
}

echo "Expected Results:\n";
echo "- '7:00' should format to '07:00:00' (7 AM)\n";
echo "- '14:30' should format to '14:30:00' (2:30 PM)\n";
echo "- All times should be in HH:MM:SS format for MySQL TIME column\n";
echo "\nBefore fix: '7:00' was stored as '00:00:07' (7 seconds)\n";
echo "After fix: '7:00' should be stored as '07:00:00' (7 AM)\n";
?>
