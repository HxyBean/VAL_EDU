<?php
require_once(__DIR__ . '/../Base/BaseModel.php');

class StudentModel extends BaseModel {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getStudentById($user_id) {
        try {
            $sql = "SELECT u.* 
                    FROM users u 
                    WHERE u.id = ? AND u.role = 'student'";
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
            $student = $result->fetch_assoc();
            $stmt->close();
            
            return $student;
        } catch (Exception $e) {
            error_log("Error getting student data: " . $e->getMessage());
            return null;
        }
    }
    
    public function getStudentCourses($user_id) {
        try {
            $sql = "SELECT c.*, e.enrollment_date, e.status as enrollment_status,
                           ct.tutor_id, u.full_name as instructor_name, u.email as instructor_email,
                           (SELECT COUNT(*) FROM sessions s WHERE s.class_id = c.id AND s.status = 'completed') as sessions_completed,
                           (SELECT COUNT(*) FROM sessions s WHERE s.class_id = c.id) as total_sessions_scheduled
                    FROM classes c 
                    INNER JOIN enrollments e ON c.id = e.class_id 
                    LEFT JOIN class_tutors ct ON c.id = ct.class_id AND ct.status = 'active'
                    LEFT JOIN users u ON ct.tutor_id = u.id
                    WHERE e.student_id = ?
                    AND e.status = 'active'
                    ORDER BY e.enrollment_date DESC";
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return [];
            }
            
            $stmt->bind_param("i", $user_id);
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                return [];
            }
            
            $result = $stmt->get_result();
            $courses = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            error_log("Found " . count($courses) . " courses for user " . $user_id);
            return $courses;
            
        } catch (Exception $e) {
            error_log("Error getting student courses: " . $e->getMessage());
            return [];
        }
    }
    
    public function getStudentAttendance($user_id, $class_id = null) {
        try {
            $sql = "SELECT att.*, c.class_name, c.subject, s.session_date, s.topic, s.session_time, s.duration_minutes, c.id as class_id
                    FROM attendance att
                    INNER JOIN sessions s ON att.session_id = s.id
                    INNER JOIN classes c ON s.class_id = c.id
                    INNER JOIN enrollments e ON c.id = e.class_id
                    WHERE e.student_id = ?
                    AND e.status = 'active'";
            
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
            $attendance = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            return $attendance;
        } catch (Exception $e) {
            error_log("Error getting student attendance: " . $e->getMessage());
            return [];
        }
    }
    
    public function getStudentSchedule($user_id, $start_date = null, $end_date = null) {
        try {
            $sql = "SELECT c.*, e.enrollment_date, e.status as enrollment_status,
                           ct.tutor_id, u.full_name as instructor_name, u.email as instructor_email,
                           COUNT(DISTINCT s.id) as sessions_completed
                    FROM classes c 
                    INNER JOIN enrollments e ON c.id = e.class_id
                    LEFT JOIN class_tutors ct ON c.id = ct.class_id AND ct.status = 'active'
                    LEFT JOIN users u ON ct.tutor_id = u.id
                    LEFT JOIN sessions s ON c.id = s.class_id AND s.status = 'completed'
                    WHERE e.student_id = ? AND e.status = 'active' AND c.status = 'active'";
            
            $params = [$user_id];
            
            if ($start_date && $end_date) {
                $sql .= " AND ((c.start_date BETWEEN ? AND ?) OR (c.end_date BETWEEN ? AND ?) OR (c.start_date <= ? AND c.end_date >= ?))";
                $params = array_merge($params, [$start_date, $end_date, $start_date, $end_date, $start_date, $end_date]);
            }
            
            $sql .= " GROUP BY c.id ORDER BY c.start_date";
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return [];
            }
            
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
            
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                return [];
            }
            
            $result = $stmt->get_result();
            $schedule = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            return $schedule;
            
        } catch (Exception $e) {
            error_log("Error getting student schedule: " . $e->getMessage());
            return [];
        }
    }
    public function updateStudentProfile($user_id, $data) {
        try {
            error_log("Updating student profile for user ID: " . $user_id);
            error_log("Update data: " . json_encode($data));
            
            // Validate input data first
            if (empty($data['full_name']) || empty($data['email'])) {
                error_log("Missing required fields");
                return false;
            }
            
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                error_log("Invalid email format");
                return false;
            }
            
            $this->db->begin_transaction();
            
            // Check if user exists and is a student
            $check_sql = "SELECT id, full_name, email, phone FROM users WHERE id = ? AND role = 'student'";
            $check_stmt = $this->db->prepare($check_sql);
            if (!$check_stmt) {
                error_log("Check prepare failed: " . $this->db->error);
                $this->db->rollback();
                return false;
            }
            
            $check_stmt->bind_param("i", $user_id);
            if (!$check_stmt->execute()) {
                error_log("Check execute failed: " . $check_stmt->error);
                $this->db->rollback();
                $check_stmt->close();
                return false;
            }
            
            $check_result = $check_stmt->get_result();
            $current_data = $check_result->fetch_assoc();
            $check_stmt->close();
            
            if (!$current_data) {
                error_log("User not found or not a student");
                $this->db->rollback();
                return false;
            }
            
            // Check if data is actually different
            $data_changed = (
                $current_data['full_name'] !== $data['full_name'] ||
                $current_data['email'] !== $data['email'] ||
                ($current_data['phone'] ?? '') !== ($data['phone'] ?? '')
            );
            
            if (!$data_changed) {
                error_log("No changes detected, transaction successful");
                $this->db->commit();
                return true;
            }
            
            // Check if email already exists for other users
            $email_check_sql = "SELECT id FROM users WHERE email = ? AND id != ?";
            $email_check_stmt = $this->db->prepare($email_check_sql);
            if (!$email_check_stmt) {
                error_log("Email check prepare failed: " . $this->db->error);
                $this->db->rollback();
                return false;
            }
            
            $email_check_stmt->bind_param("si", $data['email'], $user_id);
            if (!$email_check_stmt->execute()) {
                error_log("Email check execute failed: " . $email_check_stmt->error);
                $this->db->rollback();
                $email_check_stmt->close();
                return false;
            }
            
            $email_result = $email_check_stmt->get_result();
            if ($email_result->num_rows > 0) {
                error_log("Email already exists for another user");
                $this->db->rollback();
                $email_check_stmt->close();
                return false;
            }
            $email_check_stmt->close();
            
            // Update users table
            $sql_user = "UPDATE users SET 
                        full_name = ?, 
                        email = ?, 
                        phone = ?,
                        updated_at = NOW()
                        WHERE id = ? AND role = 'student'";
            
            $stmt_user = $this->db->prepare($sql_user);
            if (!$stmt_user) {
                error_log("Update prepare failed: " . $this->db->error);
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
                error_log("Update execute failed: " . $stmt_user->error);
                $this->db->rollback();
                $stmt_user->close();
                return false;
            }
            
            $affected_rows = $stmt_user->affected_rows;
            $stmt_user->close();
            
            error_log("Update affected rows: " . $affected_rows);
            
            if ($affected_rows >= 0) { // 0 is also success (no changes needed)
                $this->db->commit();
                error_log("Profile updated successfully");
                return true;
            } else {
                $this->db->rollback();
                error_log("Update failed - no rows affected");
                return false;
            }
            
        } catch (Exception $e) {
            if ($this->db->in_transaction) {
                $this->db->rollback();
            }
            error_log("Error updating student profile: " . $e->getMessage());
            return false;
        }
    }
    
    public function changePassword($user_id, $new_password) {
        try {
            error_log("Changing password for student ID: " . $user_id);
            
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            error_log("New hashed password created");
            
            $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ? AND role = 'student'";
            
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
                error_log("No rows were updated - user may not exist or not be a student");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Error changing password: " . $e->getMessage());
            return false;
        }
    }
    
    public function getStudentStats($user_id) {
        try {
            $stats = [];
            
            // Get total classes
            $sql = "SELECT COUNT(*) as total_classes 
                    FROM enrollments e 
                    WHERE e.student_id = ?
                    AND e.status = 'active'";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['total_classes'] = $result->fetch_assoc()['total_classes'];
            $stmt->close();
            
            // Get total sessions
            $sql = "SELECT COUNT(*) as total_sessions 
                    FROM sessions s 
                    INNER JOIN classes c ON s.class_id = c.id
                    INNER JOIN enrollments e ON c.id = e.class_id
                    WHERE e.student_id = ?
                    AND e.status = 'active'
                    AND s.status = 'completed'";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['total_sessions'] = $result->fetch_assoc()['total_sessions'];
            $stmt->close();
            
            // Get attendance rate
            $sql = "SELECT 
                        COUNT(*) as total_attended_sessions,
                        SUM(CASE WHEN att.status = 'present' THEN 1 ELSE 0 END) as attended_sessions
                    FROM attendance att
                    INNER JOIN sessions s ON att.session_id = s.id
                    INNER JOIN classes c ON s.class_id = c.id
                    INNER JOIN enrollments e ON c.id = e.class_id
                    WHERE e.student_id = ?
                    AND e.status = 'active'";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $attendance_data = $result->fetch_assoc();
            $stmt->close();
            
            $stats['total_attended_sessions'] = $attendance_data['total_attended_sessions'];
            $stats['attended_sessions'] = $attendance_data['attended_sessions'];
            $stats['attendance_rate'] = $attendance_data['total_attended_sessions'] > 0 
                ? round(($attendance_data['attended_sessions'] / $attendance_data['total_attended_sessions']) * 100, 1) 
                : 0;
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error getting student stats: " . $e->getMessage());
            return [
                'total_classes' => 0,
                'total_sessions' => 0,
                'total_attended_sessions' => 0,
                'attended_sessions' => 0,
                'attendance_rate' => 0
            ];
        }
    }
    
    public function getStudentPayments($user_id) {
        try {
            $sql = "SELECT p.*, c.class_name, c.subject
                    FROM payments p
                    INNER JOIN classes c ON p.class_id = c.id
                    WHERE p.student_id = ?
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
            error_log("Error getting student payments: " . $e->getMessage());
            return [];
        }
    }
    
    public function sendParentConnectionRequest($student_id, $parent_email, $message = '') {
        try {
            // Check if parent exists
            $sql = "SELECT id FROM users WHERE email = ? AND role = 'parent'";
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return false;
            }
            
            $stmt->bind_param("s", $parent_email);
            $stmt->execute();
            $result = $stmt->get_result();
            $parent = $result->fetch_assoc();
            $stmt->close();
            
            if (!$parent) {
                throw new Exception('Không tìm thấy phụ huynh với email này trong hệ thống');
            }
            
            // Check if connection already exists
            $sql = "SELECT id FROM parent_student_connections 
                    WHERE parent_id = ? AND student_id = ? 
                    AND status IN ('pending', 'active')";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ii", $parent['id'], $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $stmt->close();
                throw new Exception('Đã có yêu cầu kết nối hoặc đã kết nối với phụ huynh này');
            }
            $stmt->close();
            
            // Create connection request
            $sql = "INSERT INTO parent_student_connections 
                    (parent_id, student_id, status, request_message, created_at) 
                    VALUES (?, ?, 'pending', ?, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("iis", $parent['id'], $student_id, $message);
            
            if ($stmt->execute()) {
                $stmt->close();
                return true;
            } else {
                $stmt->close();
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Error sending parent connection request: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function getConnection() {
        return $this->db;
    }
}
?>