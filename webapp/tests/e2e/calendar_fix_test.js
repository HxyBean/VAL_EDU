// CALENDAR FIX TEST - Potential solution for day offset issue

// Test if the issue is in calendar rendering by implementing a shifted version
function renderCalendarWithFix() {
    console.log('🔧 [FIX TEST] Testing calendar with potential offset correction...');
    
    const year = currentDate.getFullYear();
    const month = currentDate.getMonth();
    const monthYearElement = document.getElementById('current-month-year');
    const calendarGrid = document.getElementById('calendar-grid');
    
    if (!monthYearElement || !calendarGrid) return;

    const monthNames = [
        'Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6',
        'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'
    ];
    monthYearElement.textContent = `${monthNames[month]} ${year}`;

    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    let firstDayOfWeek = firstDay.getDay();
    const daysInMonth = lastDay.getDate();

    // POTENTIAL FIX: Adjust firstDayOfWeek by -1 to correct the offset
    // This would shift all days one position to the left
    firstDayOfWeek = (firstDayOfWeek - 1 + 7) % 7;
    
    console.log('🔧 [FIX TEST] Original firstDayOfWeek:', firstDay.getDay());
    console.log('🔧 [FIX TEST] Adjusted firstDayOfWeek:', firstDayOfWeek);

    const prevMonth = new Date(year, month, 0);
    const daysInPrevMonth = prevMonth.getDate();

    calendarGrid.innerHTML = '';

    // Add previous month's trailing days
    for (let i = firstDayOfWeek - 1; i >= 0; i--) {
        const day = daysInPrevMonth - i;
        const date = new Date(year, month - 1, day);
        calendarGrid.appendChild(createCalendarDay(date, true));
    }

    // Add current month's days
    for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(year, month, day);
        calendarGrid.appendChild(createCalendarDay(date, false));
    }

    // Add next month's leading days
    const totalCells = Math.ceil((firstDayOfWeek + daysInMonth) / 7) * 7;
    const remainingCells = totalCells - (firstDayOfWeek + daysInMonth);

    for (let day = 1; day <= remainingCells; day++) {
        const date = new Date(year, month + 1, day);
        calendarGrid.appendChild(createCalendarDay(date, true));
    }
    
    console.log('🔧 [FIX TEST] Calendar rendered with offset correction');
}

// Alternative fix: Adjust the parseScheduleDays to shift days by -1
function parseScheduleDaysWithFix(scheduleDaysStr) {
    const daysMap = {
        'CN': 6,  // Shift: CN from 0 to 6 (Saturday) 
        'T2': 0,  // Shift: T2 from 1 to 0 (Sunday) - This would be wrong
        'T3': 1,  // Shift: T3 from 2 to 1 (Monday)
        'T4': 2,  // Shift: T4 from 3 to 2 (Tuesday) 
        'T5': 3,  // Shift: T5 from 4 to 3 (Wednesday)
        'T6': 4,  // Shift: T6 from 5 to 4 (Thursday)
        'T7': 5   // Shift: T7 from 6 to 5 (Friday)
    };

    if (!scheduleDaysStr) return [];

    return scheduleDaysStr.split(',').map(day => {
        const trimmedDay = day.trim();
        return daysMap[trimmedDay];
    }).filter(day => day !== undefined);
}

console.log('📝 Calendar fix test functions loaded. The actual fix should be implemented after testing.');
