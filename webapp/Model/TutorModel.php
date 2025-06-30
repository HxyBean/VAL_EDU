<?php
require_once(__DIR__ . '/../Base/BaseModel.php');

class TutorModel extends BaseModel {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getTutorById($user_id) {
        try {
            $sql = "SELECT u.* 
                    FROM users u 
                    WHERE u.id = ? AND u.role = 'tutor'";
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return null;
            }
            
            $stmt->bind_param("i", $user_id);
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                return null;
            }
            
            $result = $stmt->get_result();
            $tutor = $result->fetch_assoc();
            $stmt->close();
            
            return $tutor;
        } catch (Exception $e) {
            error_log("Error getting tutor data: " . $e->getMessage());
            return null;
        }
    }
    
    public function getTutorClasses($user_id) {
        try {
            $sql = "SELECT c.*, ct.assigned_date, ct.salary_per_session, ct.status as assignment_status,
                           (SELECT COUNT(*) FROM enrollments e WHERE e.class_id = c.id AND e.status = 'active') as student_count,
                           (SELECT COUNT(*) FROM sessions s WHERE s.class_id = c.id AND s.tutor_id = ? AND s.status = 'completed') as sessions_completed,
                           (SELECT COUNT(*) FROM sessions s WHERE s.class_id = c.id AND s.tutor_id = ?) as total_sessions_scheduled
                    FROM classes c 
                    INNER JOIN class_tutors ct ON c.id = ct.class_id 
                    WHERE ct.tutor_id = ?
                    AND ct.status = 'active'
                    ORDER BY ct.assigned_date DESC";
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return [];
            }
            
            $stmt->bind_param("iii", $user_id, $user_id, $user_id);
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                return [];
            }
            
            $result = $stmt->get_result();
            $classes = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            error_log("Found " . count($classes) . " classes for tutor " . $user_id);
            return $classes;
            
        } catch (Exception $e) {
            error_log("Error getting tutor classes: " . $e->getMessage());
            return [];
        }
    }
    
    public function getTutorSessions($user_id, $class_id = null) {
        try {
            $sql = "SELECT s.*, c.class_name, c.subject
                    FROM sessions s
                    INNER JOIN classes c ON s.class_id = c.id
                    WHERE s.tutor_id = ?";
            
            if ($class_id) {
                $sql .= " AND c.id = ?";
            }
            
            $sql .= " ORDER BY s.session_date DESC LIMIT 20";
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return [];
            }
            
            if ($class_id) {
                $stmt->bind_param("ii", $user_id, $class_id);
            } else {
                $stmt->bind_param("i", $user_id);
            }
            
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                return [];
            }
            
            $result = $stmt->get_result();
            $sessions = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            return $sessions;
        } catch (Exception $e) {
            error_log("Error getting tutor sessions: " . $e->getMessage());
            return [];
        }
    }
    
    public function updateTutorProfile($user_id, $data) {
        try {
            error_log("Updating tutor profile for user ID: " . $user_id);
            error_log("Update data: " . json_encode($data));
            
            $this->db->begin_transaction();
            
            // Update users table
            $sql_user = "UPDATE users SET 
                        full_name = ?, 
                        email = ?, 
                        phone = ?,
                        updated_at = NOW()
                        WHERE id = ? AND role = 'tutor'";
            
            $stmt_user = $this->db->prepare($sql_user);
            if (!$stmt_user) {
                error_log("Prepare failed: " . $this->db->error);
                $this->db->rollback();
                return false;
            }
            
            $stmt_user->bind_param("sssi", 
                $data['full_name'], 
                $data['email'], 
                $data['phone'], 
                $user_id
            );
            
            if (!$stmt_user->execute()) {
                error_log("Execute failed: " . $stmt_user->error);
                $this->db->rollback();
                $stmt_user->close();
                return false;
            }
            
            $affected_rows = $stmt_user->affected_rows;
            $stmt_user->close();
            
            error_log("Affected rows: " . $affected_rows);
            
            if ($affected_rows > 0) {
                $this->db->commit();
                error_log("Tutor profile updated successfully");
                return true;
            } else {
                $this->db->rollback();
                error_log("No rows were updated");
                return false;
            }
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error updating tutor profile: " . $e->getMessage());
            return false;
        }
    }
    
    public function changePassword($user_id, $new_password) {
        try {
            error_log("Changing password for tutor ID: " . $user_id);
            
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            error_log("New hashed password: " . $hashed_password);
            
            $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ? AND role = 'tutor'";
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return false;
            }
            
            $stmt->bind_param("si", $hashed_password, $user_id);
            
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                $stmt->close();
                return false;
            }
            
            $affected_rows = $stmt->affected_rows;
            $stmt->close();
            
            error_log("Password change affected rows: " . $affected_rows);
            
            if ($affected_rows > 0) {
                error_log("Password changed successfully");
                return true;
            } else {
                error_log("No rows were updated - user may not exist or not be a tutor");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Error changing password: " . $e->getMessage());
            return false;
        }
    }
    
    public function getTutorStats($user_id) {
        try {
            $stats = [];
            
            // Get total classes assigned
            $sql = "SELECT COUNT(*) as total_classes 
                    FROM class_tutors ct 
                    WHERE ct.tutor_id = ?
                    AND ct.status = 'active'";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['total_classes'] = $result->fetch_assoc()['total_classes'];
            $stmt->close();
            
            // Get total students across all classes
            $sql = "SELECT COUNT(DISTINCT e.student_id) as total_students 
                    FROM enrollments e
                    INNER JOIN class_tutors ct ON e.class_id = ct.class_id
                    WHERE ct.tutor_id = ?
                    AND ct.status = 'active'
                    AND e.status = 'active'";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['total_students'] = $result->fetch_assoc()['total_students'];
            $stmt->close();
            
            // Get total sessions taught
            $sql = "SELECT COUNT(*) as total_sessions 
                    FROM sessions s 
                    WHERE s.tutor_id = ?
                    AND s.status = 'completed'";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['total_sessions'] = $result->fetch_assoc()['total_sessions'];
            $stmt->close();
            
            // Get total earnings (estimated)
            $sql = "SELECT COUNT(s.id) * COALESCE(ct.salary_per_session, 0) as total_earnings
                    FROM sessions s 
                    INNER JOIN class_tutors ct ON s.class_id = ct.class_id AND s.tutor_id = ct.tutor_id
                    WHERE s.tutor_id = ?
                    AND s.status = 'completed'
                    AND ct.status = 'active'";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['total_earnings'] = $result->fetch_assoc()['total_earnings'] ?? 0;
            $stmt->close();
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error getting tutor stats: " . $e->getMessage());
            return [
                'total_classes' => 0,
                'total_students' => 0,
                'total_sessions' => 0,
                'total_earnings' => 0
            ];
        }
    }
    
    public function getTutorPayments($user_id) {
        try {
            $sql = "SELECT p.*, c.class_name, c.subject
                    FROM tutor_payments p
                    INNER JOIN classes c ON p.class_id = c.id
                    WHERE p.tutor_id = ?
                    ORDER BY p.payment_date DESC
                    LIMIT 10";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $payments = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            return $payments;
        } catch (Exception $e) {
            error_log("Error getting tutor payments: " . $e->getMessage());
            return [];
        }
    }
    
    public function getStudentsInClass($class_id, $tutor_id) {
        try {
            // Verify tutor is assigned to this class
            $sql_verify = "SELECT id FROM class_tutors WHERE class_id = ? AND tutor_id = ? AND status = 'active'";
            $stmt_verify = $this->db->prepare($sql_verify);
            $stmt_verify->bind_param("ii", $class_id, $tutor_id);
            $stmt_verify->execute();
            $result = $stmt_verify->get_result();
            $stmt_verify->close();
            
            if ($result->num_rows == 0) {
                throw new Exception("Tutor is not assigned to this class");
            }
            
            $sql = "SELECT u.id, u.full_name, u.email, u.phone, e.enrollment_date,
                           e.status as enrollment_status,
                           (SELECT COUNT(*) FROM attendance att 
                            INNER JOIN sessions s ON att.session_id = s.id 
                            WHERE s.class_id = ? AND att.student_id = u.id AND att.status = 'present') as sessions_attended,
                           (SELECT COUNT(*) FROM sessions s WHERE s.class_id = ? AND s.status = 'completed') as total_sessions
                    FROM users u 
                    INNER JOIN enrollments e ON u.id = e.student_id 
                    WHERE e.class_id = ? AND e.status = 'active' AND u.role = 'student'
                    ORDER BY u.full_name";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("iii", $class_id, $class_id, $class_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $students = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            return $students;
        } catch (Exception $e) {
            error_log("Error getting students in class: " . $e->getMessage());
            return [];
        }
    }
    
    public function getNextSessionNumber($class_id) {
        try {
            $sql = "SELECT COUNT(*) + 1 as next_session FROM sessions WHERE class_id = ? AND status = 'completed'";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $class_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stmt->close();
            
            return $row['next_session'];
        } catch (Exception $e) {
            error_log("Error getting next session number: " . $e->getMessage());
            return 1;
        }
    }
    
    public function createSession($class_id, $tutor_id, $session_date, $topic = '') {
        try {
            $this->db->begin_transaction();
            
            // Get class schedule info
            $sql_class = "SELECT schedule_time, schedule_duration FROM classes WHERE id = ?";
            $stmt_class = $this->db->prepare($sql_class);
            $stmt_class->bind_param("i", $class_id);
            $stmt_class->execute();
            $result = $stmt_class->get_result();
            $class_info = $result->fetch_assoc();
            $stmt_class->close();
            
            $schedule_time = $class_info['schedule_time'] ?? '09:00:00';
            $duration = $class_info['schedule_duration'] ?? 120;
            
            // Create session
            $sql = "INSERT INTO sessions (class_id, tutor_id, session_date, session_time, duration_minutes, topic, status) 
                    VALUES (?, ?, ?, ?, ?, ?, 'completed')";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("iissis", $class_id, $tutor_id, $session_date, $schedule_time, $duration, $topic);
            $stmt->execute();
            
            if ($stmt->error) {
                throw new Exception("Error creating session: " . $stmt->error);
            }
            
            $session_id = $this->db->insert_id;
            $stmt->close();
            
            $this->db->commit();
            return $session_id;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error creating session: " . $e->getMessage());
            return false;
        }
    }
    
    public function saveAttendance($session_id, $attendance_data, $tutor_id) {
        try {
            $this->db->begin_transaction();
            
            // Clear existing attendance for this session (in case of re-taking attendance)
            $sql_delete = "DELETE FROM attendance WHERE session_id = ?";
            $stmt_delete = $this->db->prepare($sql_delete);
            $stmt_delete->bind_param("i", $session_id);
            $stmt_delete->execute();
            $stmt_delete->close();
            
            // Insert new attendance records
            $sql_insert = "INSERT INTO attendance (session_id, student_id, status, recorded_by) VALUES (?, ?, ?, ?)";
            $stmt_insert = $this->db->prepare($sql_insert);
            
            foreach ($attendance_data as $student_id => $status) {
                $stmt_insert->bind_param("iisi", $session_id, $student_id, $status, $tutor_id);
                $stmt_insert->execute();
                
                if ($stmt_insert->error) {
                    throw new Exception("Error saving attendance for student $student_id: " . $stmt_insert->error);
                }
            }
            
            $stmt_insert->close();
            
            // Update sessions_attended count for each student
            $this->updateStudentAttendanceCount($attendance_data);
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error saving attendance: " . $e->getMessage());
            return false;
        }
    }
    
    private function updateStudentAttendanceCount($attendance_data) {
        foreach ($attendance_data as $student_id => $status) {
            if ($status === 'present') {
                $sql = "UPDATE enrollments SET sessions_attended = sessions_attended + 1 
                        WHERE student_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("i", $student_id);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
    
    public function checkIfAttendanceTakenToday($class_id, $date) {
        try {
            $sql = "SELECT id FROM sessions WHERE class_id = ? AND session_date = ? AND status = 'completed'";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("is", $class_id, $date);
            $stmt->execute();
            $result = $stmt->get_result();
            $session = $result->fetch_assoc();
            $stmt->close();
            
            return $session ? $session['id'] : false;
        } catch (Exception $e) {
            error_log("Error checking attendance: " . $e->getMessage());
            return false;
        }
    }
    
    public function getAttendanceHistory($class_id, $tutor_id) {
        try {
            // Verify tutor is assigned to this class
            $sql_verify = "SELECT id FROM class_tutors WHERE class_id = ? AND tutor_id = ? AND status = 'active'";
            $stmt_verify = $this->db->prepare($sql_verify);
            $stmt_verify->bind_param("ii", $class_id, $tutor_id);
            $stmt_verify->execute();
            $result = $stmt_verify->get_result();
            $stmt_verify->close();
            
            if ($result->num_rows == 0) {
                throw new Exception("Tutor is not assigned to this class");
            }
            
            // Get all students in the class
            $students_sql = "SELECT u.id, u.full_name 
                        FROM users u 
                        INNER JOIN enrollments e ON u.id = e.student_id 
                        WHERE e.class_id = ? AND e.status = 'active' AND u.role = 'student'
                        ORDER BY u.full_name";
        
            $stmt_students = $this->db->prepare($students_sql);
            $stmt_students->bind_param("i", $class_id);
            $stmt_students->execute();
            $result_students = $stmt_students->get_result();
            $students = $result_students->fetch_all(MYSQLI_ASSOC);
            $stmt_students->close();
        
            // Get all sessions for this class (both completed and future)
            $sessions_sql = "SELECT s.id, s.session_date, s.topic, s.status,
                               ROW_NUMBER() OVER (ORDER BY s.session_date) as session_number
                        FROM sessions s 
                        WHERE s.class_id = ? AND s.tutor_id = ?
                        ORDER BY s.session_date";
        
            $stmt_sessions = $this->db->prepare($sessions_sql);
            $stmt_sessions->bind_param("ii", $class_id, $tutor_id);
            $stmt_sessions->execute();
            $result_sessions = $stmt_sessions->get_result();
            $sessions = $result_sessions->fetch_all(MYSQLI_ASSOC);
            $stmt_sessions->close();
        
            // Get attendance data for all sessions
            $attendance_data = [];
            if (!empty($sessions)) {
                $session_ids = array_column($sessions, 'id');
                $placeholders = str_repeat('?,', count($session_ids) - 1) . '?';
            
                $attendance_sql = "SELECT att.session_id, att.student_id, att.status 
                              FROM attendance att 
                              WHERE att.session_id IN ($placeholders)";
            
                $stmt_attendance = $this->db->prepare($attendance_sql);
                $types = str_repeat('i', count($session_ids));
                $stmt_attendance->bind_param($types, ...$session_ids);
                $stmt_attendance->execute();
                $result_attendance = $stmt_attendance->get_result();
            
                while ($row = $result_attendance->fetch_assoc()) {
                    $attendance_data[$row['session_id']][$row['student_id']] = $row['status'];
                }
                $stmt_attendance->close();
            }
        
            return [
                'students' => $students,
                'sessions' => $sessions,
                'attendance' => $attendance_data
            ];
        
        } catch (Exception $e) {
            error_log("Error getting attendance history: " . $e->getMessage());
            return [
                'students' => [],
                'sessions' => [],
                'attendance' => []
            ];
        }
    }
    
    public function getClassScheduledSessions($class_id, $tutor_id) {
        try {
            // Get class info to calculate future sessions
            $class_sql = "SELECT c.*, ct.assigned_date 
                     FROM classes c 
                     INNER JOIN class_tutors ct ON c.id = ct.class_id 
                     WHERE c.id = ? AND ct.tutor_id = ? AND ct.status = 'active'";
        
            $stmt = $this->db->prepare($class_sql);
            $stmt->bind_param("ii", $class_id, $tutor_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $class_info = $result->fetch_assoc();
            $stmt->close();
        
            if (!$class_info) {
                return [];
            }
        
            // Get existing sessions
            $existing_sessions_sql = "SELECT session_date FROM sessions WHERE class_id = ? AND tutor_id = ? ORDER BY session_date";
            $stmt_existing = $this->db->prepare($existing_sessions_sql);
            $stmt_existing->bind_param("ii", $class_id, $tutor_id);
            $stmt_existing->execute();
            $result_existing = $stmt_existing->get_result();
            $existing_dates = [];
            while ($row = $result_existing->fetch_assoc()) {
                $existing_dates[] = $row['session_date'];
            }
            $stmt_existing->close();
        
            // Generate future sessions based on schedule
            $scheduled_sessions = [];
            $today = new DateTime();
            $start_date = new DateTime($class_info['start_date']);
            $end_date = new DateTime($class_info['end_date']);
        
            // Parse schedule days (e.g., "T2,T4,T6" -> [1,3,5])
            $schedule_days = [];
            if ($class_info['schedule_days']) {
                $days_map = ['T2' => 1, 'T3' => 2, 'T4' => 3, 'T5' => 4, 'T6' => 5, 'T7' => 6, 'CN' => 0];
                $days = explode(',', $class_info['schedule_days']);
                foreach ($days as $day) {
                    if (isset($days_map[trim($day)])) {
                        $schedule_days[] = $days_map[trim($day)];
                    }
                }
            }
        
            // Generate sessions from start date to end date
            $current_date = clone $start_date;
            $session_count = 0;
        
            while ($current_date <= $end_date && $session_count < $class_info['sessions_total']) {
                if (in_array($current_date->format('w'), $schedule_days)) {
                    $date_str = $current_date->format('Y-m-d');
                
                    // Check if session already exists
                    if (!in_array($date_str, $existing_dates)) {
                        $scheduled_sessions[] = [
                            'session_date' => $date_str,
                            'session_number' => $session_count + 1,
                            'status' => $current_date > $today ? 'future' : 'missed',
                            'is_scheduled' => true
                        ];
                    }
                    $session_count++;
                }
                $current_date->add(new DateInterval('P1D'));
            }
        
            return $scheduled_sessions;
        
        } catch (Exception $e) {
            error_log("Error getting scheduled sessions: " . $e->getMessage());
            return [];
        }
    }
    
    // Thêm methods mới vào TutorModel

    public function getDetailedStudentList($class_id, $tutor_id) {
        try {
            // Verify tutor is assigned to this class
            $sql_verify = "SELECT id FROM class_tutors WHERE class_id = ? AND tutor_id = ? AND status = 'active'";
            $stmt_verify = $this->db->prepare($sql_verify);
            $stmt_verify->bind_param("ii", $class_id, $tutor_id);
            $stmt_verify->execute();
            $result = $stmt_verify->get_result();
            $stmt_verify->close();
            
            if ($result->num_rows == 0) {
                throw new Exception("Tutor is not assigned to this class");
            }
            
            $sql = "SELECT 
                        u.id, 
                        u.full_name, 
                        u.email, 
                        u.phone, 
                        e.enrollment_date,
                        e.status as enrollment_status,
                        
                        -- Count total sessions for this class
                        (SELECT COUNT(*) 
                         FROM sessions s 
                         WHERE s.class_id = ? AND s.status = 'completed') as total_sessions,
                        
                        -- Count sessions this student attended
                        (SELECT COUNT(*) 
                         FROM attendance att 
                         INNER JOIN sessions s ON att.session_id = s.id 
                         WHERE s.class_id = ? 
                         AND att.student_id = u.id 
                         AND att.status = 'present' 
                         AND s.status = 'completed') as sessions_attended,
                        
                        -- Count sessions this student was absent
                        (SELECT COUNT(*) 
                         FROM attendance att 
                         INNER JOIN sessions s ON att.session_id = s.id 
                         WHERE s.class_id = ? 
                         AND att.student_id = u.id 
                         AND att.status = 'absent' 
                         AND s.status = 'completed') as sessions_absent,
                        
                        -- Get last attendance date
                        (SELECT MAX(s.session_date) 
                         FROM attendance att 
                         INNER JOIN sessions s ON att.session_id = s.id 
                         WHERE s.class_id = ? 
                         AND att.student_id = u.id 
                         AND s.status = 'completed') as last_session_date
                        
                    FROM users u 
                    INNER JOIN enrollments e ON u.id = e.student_id 
                    WHERE e.class_id = ? 
                    AND e.status = 'active' 
                    AND u.role = 'student'
                    ORDER BY u.full_name";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("iiiii", $class_id, $class_id, $class_id, $class_id, $class_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $students = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            // Calculate attendance rate for each student
            foreach ($students as &$student) {
                $totalSessions = intval($student['total_sessions']);
                $attendedSessions = intval($student['sessions_attended']);
                
                if ($totalSessions > 0) {
                    $student['attendance_rate'] = round(($attendedSessions / $totalSessions) * 100, 1);
                } else {
                    $student['attendance_rate'] = 0;
                }
                
                // Format dates
                if ($student['last_session_date']) {
                    $student['last_session_date_formatted'] = date('d/m/Y', strtotime($student['last_session_date']));
                } else {
                    $student['last_session_date_formatted'] = 'Chưa có';
                }
                
                if ($student['enrollment_date']) {
                    $student['enrollment_date_formatted'] = date('d/m/Y', strtotime($student['enrollment_date']));
                }
            }
            
            return $students;
            
        } catch (Exception $e) {
            error_log("Error getting detailed student list: " . $e->getMessage());
            return [];
        }
    }

    public function getClassAttendanceStats($class_id, $tutor_id) {
        try {
            // Verify tutor is assigned to this class
            $sql_verify = "SELECT id FROM class_tutors WHERE class_id = ? AND tutor_id = ? AND status = 'active'";
            $stmt_verify = $this->db->prepare($sql_verify);
            $stmt_verify->bind_param("ii", $class_id, $tutor_id);
            $stmt_verify->execute();
            $result = $stmt_verify->get_result();
            $stmt_verify->close();
            
            if ($result->num_rows == 0) {
                throw new Exception("Tutor is not assigned to this class");
            }
            
            $sql = "SELECT 
                        COUNT(DISTINCT u.id) as total_students,
                        COUNT(DISTINCT s.id) as total_sessions,
                        
                        -- Total possible attendances (students × sessions)
                        (COUNT(DISTINCT u.id) * COUNT(DISTINCT s.id)) as total_possible_attendances,
                        
                        -- Total actual attendances
                        SUM(CASE WHEN att.status = 'present' THEN 1 ELSE 0 END) as total_present,
                        
                        -- Total absences
                        SUM(CASE WHEN att.status = 'absent' THEN 1 ELSE 0 END) as total_absent
                        
                    FROM users u 
                    INNER JOIN enrollments e ON u.id = e.student_id 
                    CROSS JOIN sessions s 
                    LEFT JOIN attendance att ON s.id = att.session_id AND u.id = att.student_id
                    WHERE e.class_id = ? 
                    AND s.class_id = ?
                    AND e.status = 'active' 
                    AND u.role = 'student'
                    AND s.status = 'completed'";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ii", $class_id, $class_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats = $result->fetch_assoc();
            $stmt->close();
            
            // Calculate percentages
            if ($stats['total_possible_attendances'] > 0) {
                $stats['average_attendance_rate'] = round(
                    ($stats['total_present'] / $stats['total_possible_attendances']) * 100, 1
                );
            } else {
                $stats['average_attendance_rate'] = 0;
            }
            
            // Get additional stats
            $additional_sql = "SELECT 
                                COUNT(DISTINCT CASE WHEN attendance_rate >= 90 THEN student_id END) as excellent_students,
                                COUNT(DISTINCT CASE WHEN attendance_rate >= 80 AND attendance_rate < 90 THEN student_id END) as good_students,
                                COUNT(DISTINCT CASE WHEN attendance_rate >= 60 AND attendance_rate < 80 THEN student_id END) as average_students,
                                COUNT(DISTINCT CASE WHEN attendance_rate < 60 THEN student_id END) as poor_students
                            FROM (
                                SELECT 
                                    u.id as student_id,
                                    CASE 
                                        WHEN COUNT(s.id) > 0 THEN 
                                            ROUND((SUM(CASE WHEN att.status = 'present' THEN 1 ELSE 0 END) / COUNT(s.id)) * 100, 1)
                                        ELSE 0 
                                    END as attendance_rate
                                FROM users u 
                                INNER JOIN enrollments e ON u.id = e.student_id 
                                LEFT JOIN sessions s ON s.class_id = e.class_id AND s.status = 'completed'
                                LEFT JOIN attendance att ON s.id = att.session_id AND u.id = att.student_id
                                WHERE e.class_id = ? 
                                AND e.status = 'active' 
                                AND u.role = 'student'
                                GROUP BY u.id
                            ) as student_rates";
            
            $stmt_add = $this->db->prepare($additional_sql);
            $stmt_add->bind_param("i", $class_id);
            $stmt_add->execute();
            $result_add = $stmt_add->get_result();
            $additional_stats = $result_add->fetch_assoc();
            $stmt_add->close();
            
            // Merge stats
            $stats = array_merge($stats, $additional_stats);
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Error getting class attendance stats: " . $e->getMessage());
            return [
                'total_students' => 0,
                'total_sessions' => 0,
                'total_possible_attendances' => 0,
                'total_present' => 0,
                'total_absent' => 0,
                'average_attendance_rate' => 0,
                'excellent_students' => 0,
                'good_students' => 0,
                'average_students' => 0,
                'poor_students' => 0
            ];
        }
    }
}
?>