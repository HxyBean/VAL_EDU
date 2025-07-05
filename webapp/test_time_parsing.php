<?php
// Test time parsing fix

function testTimeParsing($timeInput) {
    echo "Testing input: '$timeInput'\n";
    
    // Accept both H:MM and HH:MM formats
    if (preg_match('/^\d{1,2}:\d{2}$/', $timeInput)) {
        // Format to ensure HH:MM format (pad hour with zero if needed)
        $timeComponents = explode(':', $timeInput);
        $hour = str_pad($timeComponents[0], 2, '0', STR_PAD_LEFT);
        $minute = $timeComponents[1];
        $formatted = $hour . ':' . $minute;
        echo "Result: '$formatted'\n";
    } else {
        // Try to convert from other formats
        $time = date('H:i', strtotime($timeInput));
        if ($time === false) {
            echo "FAILED: Invalid time format\n";
        } else {
            echo "Result: '$time'\n";
        }
    }
    echo "---\n";
}

// Test various time inputs
testTimeParsing('7:00');    // Should become 07:00
testTimeParsing('07:00');   // Should stay 07:00
testTimeParsing('14:30');   // Should stay 14:30
testTimeParsing('9:15');    // Should become 09:15
testTimeParsing('23:45');   // Should stay 23:45
testTimeParsing('7AM');     // Should become 07:00 (fallback to strtotime)
testTimeParsing('7:00 AM'); // Should become 07:00 (fallback to strtotime)
