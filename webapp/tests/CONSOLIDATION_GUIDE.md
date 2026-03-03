# Test Consolidation Guide

This document outlines test files that could be consolidated to reduce redundancy.

## Calendar Tests - Consolidation Opportunity

**Current Files:**
- `e2e/calendar_fix_test.html` - Initial calendar fix test
- `e2e/calendar_fix_test.js` - Calendar JavaScript tests
- `e2e/calendar_fix_verification.html` - Verification after fix
- `e2e/final_calendar_verification.html` - Final verification
- `e2e/test_calendar.html` - Feature tests
- **ARCHIVED:** `e2e/debug_calendar.html` - Debugging file

**Recommendation:** Consolidate into `e2e/test_calendar_complete.html` with:
- All calendar feature tests
- Verification scenarios
- Fix validation

**Status:** Pending consolidation

## Time Tests - Significant Consolidation Opportunity

**Current Files:**
- `unit/test_time_parsing.php` - Time parsing logic
- `unit/test_time_bug_fix.php` - Bug fix test
- `unit/test_time_fix_verification.php` - Fix verification
- `unit/simple_time_test.php` - Simple test
- `unit/check_time_values.php` - Validation
- `e2e/test_time_fix.html` - UI test
- `e2e/test_time_fix_ui.html` - UI verification
- **ARCHIVED:** `unit/test_time_issue_debug.php` - Debug file
- **ARCHIVED:** `unit/test_update_time_debug.php` - Debug file

**Recommendation:** Create unified time test suite:
- `unit/test_time_utilities.php` - All time parsing/calculation tests
- `e2e/test_time_picker.html` - All UI tests

**Status:** Pending consolidation

## Data Validation Tests - Consolidation Opportunity

**Current Files:**
- `unit/simple_db_check.php` - Basic DB validation
- `unit/check_bad_data.php` - Bad data detection
- `unit/check_payment_records.php` - Payment validation
- `unit/check_time_values.php` - Time validation
- **ARCHIVED:** `unit/debug_types.php` - Type checking

**Recommendation:** Create `unit/test_data_validation.php` covering:
- Database integrity checks
- Data type validation
- Payment record validation
- Time value validation

**Status:** Pending consolidation

## Debug/Temporary Files - Archived

These files were temporary debugging aids and have been moved to `tests/archived/`:
- `debug_calendar.html`
- `debug_offset.html` 
- `debug_current_student.php`
- `debug_student_data.php`
- `debug_types.php`
- `test_time_issue_debug.php`
- `test_update_time_debug.php`

**Recommendation:** Review and delete if no longer needed (keep for 1-2 release cycles in case needed for debugging)

## Student/Course Tests

**Current Files:**
- `integration/test_create_course.php`
- `integration/test_real_course_creation.php`
- `integration/test_update_course.php`
- `e2e/test_student_dashboard.html`

**Status:** Reasonable organization, no immediate consolidation needed

## Summary

### High Priority Consolidation:
1. Time tests (9+ files → 2 files)
2. Calendar tests (6 files → 1-2 files)
3. Data validation tests (4+ files → 1 file)

### Estimated Reduction:
- **Before:** 43 test files
- **After:** ~25-30 focused test files
- **Reduction:** 30-40% fewer files

### Implementation Steps:
1. Review each consolidation suggestion
2. Verify all test scenarios are covered
3. Create new consolidated test files
4. Update test documentation
5. Delete old redundant files
6. Commit with clear message

---

*Last Updated: February 2026*
