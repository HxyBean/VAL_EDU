<?php
// filepath: d:\SCHOOL\PTWeb\XAMPP\htdocs\webapp\Model\AdminModel.php
require_once(__DIR__ . '/../Base/BaseModel.php');

class AdminModel extends BaseModel {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getAdminById($user_id) {
        try {
            $sql = "SELECT u.* 
                    FROM users u 
                    WHERE u.id = ? AND u.role = 'admin'";
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
            $admin = $result->fetch_assoc();
            $stmt->close();
            
            return $admin;
        } catch (Exception $e) {
            error_log("Error getting admin data: " . $e->getMessage());
            return null;
        }
    }
    
    public function getSystemStats() {
        try {
            $stats = [];
            
            // Get total students
            $sql = "SELECT COUNT(*) as total_students FROM users WHERE role = 'student' AND is_active = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['total_students'] = $result->fetch_assoc()['total_students'];
            $stmt->close();
            
            // Get total tutors
            $sql = "SELECT COUNT(*) as total_tutors FROM users WHERE role = 'tutor' AND is_active = 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['total_tutors'] = $result->fetch_assoc()['total_tutors'];
            $stmt->close();
            
            // Get total classes - sử dụng status thay vì is_active
            $sql = "SELECT COUNT(*) as total_classes FROM classes WHERE status IN ('active', 'completed')";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['total_classes'] = $result->fetch_assoc()['total_classes'];
            $stmt->close();
            
            // Get total revenue (estimated from payments)
            $sql = "SELECT SUM(amount) as total_revenue FROM payments WHERE status = 'completed'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['total_revenue'] = $result->fetch_assoc()['total_revenue'] ?? 0;
            $stmt->close();
            
            // Get active enrollments
            $sql = "SELECT COUNT(*) as active_enrollments FROM enrollments WHERE status = 'active'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['active_enrollments'] = $result->fetch_assoc()['active_enrollments'];
            $stmt->close();
            
            // Get sessions this month
            $sql = "SELECT COUNT(*) as sessions_this_month 
                    FROM sessions 
                    WHERE status = 'completed' 
                    AND MONTH(session_date) = MONTH(CURRENT_DATE()) 
                    AND YEAR(session_date) = YEAR(CURRENT_DATE())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['sessions_this_month'] = $result->fetch_assoc()['sessions_this_month'];
            $stmt->close();
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error getting system stats: " . $e->getMessage());
            return [
                'total_students' => 0,
                'total_tutors' => 0,
                'total_classes' => 0,
                'total_revenue' => 0,
                'active_enrollments' => 0,
                'sessions_this_month' => 0
            ];
        }
    }
    
    public function getAllStudents($limit = 20, $offset = 0) {
        try {
            $sql = "SELECT u.id, u.username, u.full_name, u.email, u.phone, u.created_at,
                           COUNT(DISTINCT e.class_id) as classes_enrolled,
                           u.is_active
                    FROM users u
                    LEFT JOIN enrollments e ON u.id = e.student_id AND e.status = 'active'
                    WHERE u.role = 'student'
                    GROUP BY u.id
                    ORDER BY u.created_at DESC
                    LIMIT ? OFFSET ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ii", $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            $students = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            return $students;
        } catch (Exception $e) {
            error_log("Error getting students: " . $e->getMessage());
            return [];
        }
    }
    
    public function getAllTutors($limit = null, $offset = null) {
        try {
            $sql = "SELECT u.id, u.username, u.full_name, u.email, u.phone, u.created_at,
                    (SELECT COUNT(*) FROM class_tutors ct 
                     WHERE ct.tutor_id = u.id AND ct.status = 'active') as active_classes
                    FROM users u
                    WHERE u.role = 'tutor' AND u.is_active = 1
                    ORDER BY u.created_at DESC";

            if ($limit !== null && $offset !== null) {
                $sql .= " LIMIT ? OFFSET ?";
                $stmt = $this->db->prepare($sql);
                $stmt->bind_param("ii", $limit, $offset);
            } else {
                $stmt = $this->db->prepare($sql);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            $tutors = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            return $tutors;

        } catch (Exception $e) {
            error_log("Error getting tutors: " . $e->getMessage());
            return [];
        }
    }
    
    public function getAllClasses($limit = 20, $offset = 0) {
        try {
            $sql = "SELECT c.*, 
                       u.full_name as tutor_name,
                       COUNT(DISTINCT e.student_id) as student_count
                FROM classes c
                LEFT JOIN class_tutors ct ON c.id = ct.class_id AND ct.status = 'active'
                LEFT JOIN users u ON ct.tutor_id = u.id
                LEFT JOIN enrollments e ON c.id = e.class_id AND e.status = 'active'
                WHERE c.status IN ('active', 'completed')
                GROUP BY c.id
                ORDER BY c.created_at DESC
                LIMIT ? OFFSET ?";
        
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ii", $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();
            $classes = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            return $classes;
        } catch (Exception $e) {
            error_log("Error getting classes: " . $e->getMessage());
            return [];
        }
    }
    
    public function getRecentActivities($limit = 10) {
        try {
            $activities = [];
            
            // Get recent student registrations
            $sql = "SELECT 'student_registration' as type, full_name as name, created_at as activity_date
                    FROM users 
                    WHERE role = 'student' 
                    ORDER BY created_at DESC 
                    LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $activities[] = $row;
            }
            $stmt->close();
            
            // Get recent class enrollments
            $sql = "SELECT 'enrollment' as type, 
                           CONCAT(u.full_name, ' - ', c.class_name) as name, 
                           e.enrollment_date as activity_date
                    FROM enrollments e
                    INNER JOIN users u ON e.student_id = u.id
                    INNER JOIN classes c ON e.class_id = c.id
                    ORDER BY e.enrollment_date DESC 
                    LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $activities[] = $row;
            }
            $stmt->close();
            
            // Sort by date and limit
            usort($activities, function($a, $b) {
                return strtotime($b['activity_date']) - strtotime($a['activity_date']);
            });
            
            return array_slice($activities, 0, $limit);
        } catch (Exception $e) {
            error_log("Error getting recent activities: " . $e->getMessage());
            return [];
        }
    }
    
    public function updateAdminProfile($user_id, $data) {
        try {
            error_log("Updating admin profile for user ID: " . $user_id);
            error_log("Update data: " . json_encode($data));
            
            $this->db->begin_transaction();
            
            // Update users table
            $sql_user = "UPDATE users SET 
                        full_name = ?, 
                        email = ?, 
                        phone = ?,
                        updated_at = NOW()
                        WHERE id = ? AND role = 'admin'";
            
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
                error_log("Admin profile updated successfully");
                return true;
            } else {
                $this->db->rollback();
                error_log("No rows were updated");
                return false;
            }
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error updating admin profile: " . $e->getMessage());
            return false;
        }
    }
    
    public function changePassword($user_id, $new_password) {
        try {
            error_log("Changing password for admin ID: " . $user_id);
            
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            error_log("New hashed password: " . $hashed_password);
            
            $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ? AND role = 'admin'";
            
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
                error_log("No rows were updated - user may not exist or not be an admin");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Error changing password: " . $e->getMessage());
            return false;
        }
    }
    
    public function getStudentRegistrationTrend($year = null) {
        try {
            if (!$year) {
                $year = date('Y');
            }
            
            $sql = "SELECT 
                        MONTH(created_at) as month,
                        COUNT(*) as student_count
                    FROM users 
                    WHERE role = 'student' 
                    AND YEAR(created_at) = ?
                    GROUP BY MONTH(created_at)
                    ORDER BY MONTH(created_at)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $year);
            $stmt->execute();
            $result = $stmt->get_result();
            $monthly_data = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            // Fill missing months with 0
            $trend_data = [];
            for ($month = 1; $month <= 12; $month++) {
                $trend_data[$month] = [
                    'month' => $month,
                    'student_count' => 0,
                    'month_name' => $this->getMonthName($month)
                ];
            }
            
            // Fill actual data
            foreach ($monthly_data as $data) {
                $trend_data[$data['month']]['student_count'] = intval($data['student_count']);
            }
            
            return array_values($trend_data);
            
        } catch (Exception $e) {
            error_log("Error getting student registration trend: " . $e->getMessage());
            return [];
        }
    }
    
    public function getClassLevelDistribution() {
        try {
            $sql = "SELECT 
                    COALESCE(class_level, 'Không xác định') as class_level,
                    COUNT(*) as class_count,
                    COUNT(DISTINCT e.student_id) as student_count
                FROM classes c
                LEFT JOIN enrollments e ON c.id = e.class_id AND e.status = 'active'
                WHERE c.status IN ('active', 'completed')
                GROUP BY class_level
                ORDER BY class_count DESC";
        
            // Kiểm tra prepare statement
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed in getClassLevelDistribution: " . $this->db->error);
                return [];
            }
        
            // Kiểm tra execute
            if (!$stmt->execute()) {
                error_log("Execute failed in getClassLevelDistribution: " . $stmt->error);
                $stmt->close();
                return [];
            }
        
            $result = $stmt->get_result();
            $distribution = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        
            // Tính toán phần trăm
            $total_classes = array_sum(array_column($distribution, 'class_count'));
        
            foreach ($distribution as &$level) {
                $level['percentage'] = $total_classes > 0 ? 
                    round(($level['class_count'] / $total_classes) * 100, 1) : 0;
                $level['class_count'] = intval($level['class_count']);
                $level['student_count'] = intval($level['student_count']);
            }
        
            return $distribution;
        
        } catch (Exception $e) {
            error_log("Error in getClassLevelDistribution: " . $e->getMessage());
            return [];
        }
    }
    
    public function getCurrentMonthStats() {
        try {
            $current_month = date('n');
            $current_year = date('Y');
            
            $stats = [];
            
            // Students registered this month
            $sql = "SELECT COUNT(*) as new_students 
                    FROM users 
                    WHERE role = 'student' 
                    AND MONTH(created_at) = ? 
                    AND YEAR(created_at) = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ii", $current_month, $current_year);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['new_students_this_month'] = $result->fetch_assoc()['new_students'];
            $stmt->close();
            
            // Enrollments this month
            $sql = "SELECT COUNT(*) as new_enrollments 
                    FROM enrollments 
                    WHERE MONTH(enrollment_date) = ? 
                    AND YEAR(enrollment_date) = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ii", $current_month, $current_year);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['new_enrollments_this_month'] = $result->fetch_assoc()['new_enrollments'];
            $stmt->close();
            
            // Classes started this month - sử dụng status thay vì is_active
            $sql = "SELECT COUNT(*) as new_classes 
                    FROM classes 
                    WHERE MONTH(start_date) = ? 
                    AND YEAR(start_date) = ?
                    AND status IN ('active', 'completed')";
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ii", $current_month, $current_year);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats['new_classes_this_month'] = $result->fetch_assoc()['new_classes'];
            $stmt->close();
            
            return $stats;
            
        } catch (Exception $e) {
            error_log("Error getting current month stats: " . $e->getMessage());
            return [
                'new_students_this_month' => 0,
                'new_enrollments_this_month' => 0,
                'new_classes_this_month' => 0
            ];
        }
    }
    
    private function getMonthName($month) {
        $months = [
            1 => 'Tháng 1', 2 => 'Tháng 2', 3 => 'Tháng 3', 4 => 'Tháng 4',
            5 => 'Tháng 5', 6 => 'Tháng 6', 7 => 'Tháng 7', 8 => 'Tháng 8',
            9 => 'Tháng 9', 10 => 'Tháng 10', 11 => 'Tháng 11', 12 => 'Tháng 12'
        ];
        return $months[$month] ?? 'Không xác định';
    }
    
    public function getEnrollmentTrend($year = null) {
        try {
            if (!$year) {
                $year = date('Y');
            }
            
            $sql = "SELECT 
                        MONTH(enrollment_date) as month,
                        COUNT(*) as enrollment_count
                    FROM enrollments 
                    WHERE YEAR(enrollment_date) = ?
                    GROUP BY MONTH(enrollment_date)
                    ORDER BY MONTH(enrollment_date)";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("i", $year);
            $stmt->execute();
            $result = $stmt->get_result();
            $monthly_data = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            // Fill missing months with 0
            $trend_data = [];
            for ($month = 1; $month <= 12; $month++) {
                $trend_data[$month] = [
                    'month' => $month,
                    'enrollment_count' => 0,
                    'month_name' => $this->getMonthName($month)
                ];
            }
            
            // Fill actual data
            foreach ($monthly_data as $data) {
                $trend_data[$data['month']]['enrollment_count'] = intval($data['enrollment_count']);
            }
            
            return array_values($trend_data);
            
        } catch (Exception $e) {
            error_log("Error getting enrollment trend: " . $e->getMessage());
            return [];
        }
    }

    public function createCourse($data) {
        try {
            $this->db->begin_transaction();
            
            // **THÊM KIỂM TRA TRÙNG LỊCH GIẢNG VIÊN**
            if (!empty($data['tutor_id'])) {
                $conflictCheck = $this->checkTutorScheduleConflict(
                    $data['tutor_id'],
                    $data['schedule_time'],
                    $data['schedule_duration'],
                    $data['schedule_days'],
                    $data['start_date'],
                    $data['end_date']
                );
                
                if ($conflictCheck['conflict']) {
                    throw new Exception($conflictCheck['message']);
                }
            }
            
            // Insert into classes table
            $sql = "INSERT INTO classes (
                        class_name, class_year, class_level, subject, description,
                        max_students, sessions_total, price_per_session,
                        schedule_time, schedule_duration, schedule_days,
                        start_date, end_date, status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Database prepare error: " . $this->db->error);
            }
            
            $stmt->bind_param("sisssiidsisss",
                $data['class_name'],
                $data['class_year'],
                $data['class_level'],
                $data['subject'],
                $data['description'],
                $data['max_students'],
                $data['sessions_total'],
                $data['price_per_session'],
                $data['schedule_time'],
                $data['schedule_duration'],
                $data['schedule_days'],
                $data['start_date'],
                $data['end_date']
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create course: " . $stmt->error);
            }
            
            $courseId = $this->db->insert_id;
            $stmt->close();
            
            // Assign tutor to the class if specified
            if (!empty($data['tutor_id'])) {
                $tutorSql = "INSERT INTO class_tutors (tutor_id, class_id, assigned_date, status) 
                            VALUES (?, ?, CURDATE(), 'active')";
                $tutorStmt = $this->db->prepare($tutorSql);
                
                if (!$tutorStmt) {
                    throw new Exception("Failed to prepare tutor assignment: " . $this->db->error);
                }
                
                $tutorStmt->bind_param("ii", $data['tutor_id'], $courseId);
                
                if (!$tutorStmt->execute()) {
                    throw new Exception("Failed to assign tutor: " . $tutorStmt->error);
                }
                
                $tutorStmt->close();
            }
            
            $this->db->commit();
            return $courseId;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error creating course: " . $e->getMessage());
            throw $e;
        }
    }

    public function getAllCourses() {
        try {
            error_log("=== getAllCourses DEBUG START ===");
            
            // Bao gồm cả status 'closed' để hiển thị khóa học đã đóng
            $sql = "SELECT c.*, 
                    COALESCE(u.full_name, 'Chưa phân công') as tutor_name,
                    COUNT(DISTINCT e.student_id) as current_students,
                    COALESCE(completed_sessions.sessions_completed, 0) as actual_sessions_completed
                    FROM classes c
                    LEFT JOIN class_tutors ct ON c.id = ct.class_id AND ct.status = 'active'
                    LEFT JOIN users u ON ct.tutor_id = u.id
                    LEFT JOIN enrollments e ON c.id = e.class_id AND e.status = 'active'
                    LEFT JOIN (
                        SELECT class_id, COUNT(*) as sessions_completed
                        FROM sessions 
                        WHERE status = 'completed'
                        GROUP BY class_id
                    ) completed_sessions ON c.id = completed_sessions.class_id
                    WHERE c.status IN ('active', 'completed', 'closed')
                    GROUP BY c.id 
                    ORDER BY c.status = 'active' DESC, c.status = 'completed' DESC, c.created_at DESC";
            
            error_log("SQL Query: " . $sql);
            
            $result = $this->db->query($sql);
            if (!$result) {
                error_log("Query failed: " . $this->db->error);
                return [];
            }
            
            $courses = $result->fetch_all(MYSQLI_ASSOC);
            error_log("Query successful, found " . count($courses) . " courses");
            
            if (count($courses) > 0) {
                error_log("First course data: " . json_encode($courses[0]));
            }
            
            return $courses;
            
        } catch (Exception $e) {
            error_log("Exception in getAllCourses: " . $e->getMessage());
            return [];
        }
    }

    public function getCourseById($courseId) {
        try {
            $sql = "SELECT c.*, 
                       ct.tutor_id,
                       u.full_name as tutor_name,
                       COUNT(DISTINCT e.student_id) as current_students
                FROM classes c
                LEFT JOIN class_tutors ct ON c.id = ct.class_id AND ct.status = 'active'
                LEFT JOIN users u ON ct.tutor_id = u.id
                LEFT JOIN enrollments e ON c.id = e.class_id AND e.status = 'active'
                WHERE c.id = ?
                GROUP BY c.id";
        
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return null;
            }
        
            $stmt->bind_param("i", $courseId);
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                $stmt->close();
                return null;
            }
        
            $result = $stmt->get_result();
            $course = $result->fetch_assoc();
            $stmt->close();
        
            return $course;
        
        } catch (Exception $e) {
            error_log("Error getting course: " . $e->getMessage());
            return null;
        }
    }

    public function closeCourse($courseId) {
        try {
            error_log("Closing course with ID: " . $courseId);
            
            $sql = "UPDATE classes SET status = 'closed', updated_at = NOW() WHERE id = ? AND status = 'active'";
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return false;
            }
            
            $stmt->bind_param("i", $courseId);
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                $stmt->close();
                return false;
            }
            
            $affected_rows = $stmt->affected_rows;
            $stmt->close();
            
            if ($affected_rows > 0) {
                error_log("Course closed successfully");
                return true;
            } else {
                error_log("No course was closed - may not exist or already closed");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Error closing course: " . $e->getMessage());
            return false;
        }
    }

    public function reopenCourse($courseId) {
        try {
            error_log("Reopening course with ID: " . $courseId);
            
            $sql = "UPDATE classes SET status = 'active', updated_at = NOW() WHERE id = ? AND status = 'closed'";
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return false;
            }
            
            $stmt->bind_param("i", $courseId);
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                $stmt->close();
                return false;
            }
            
            $affected_rows = $stmt->affected_rows;
            $stmt->close();
            
            if ($affected_rows > 0) {
                error_log("Course reopened successfully");
                return true;
            } else {
                error_log("No course was reopened - may not exist or not closed");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Error reopening course: " . $e->getMessage());
            return false;
        }
    }

    // Helper method to get tutors for form dropdown
public function getTutorsForForm() {
        try {
            error_log("getTutorsForForm method called in AdminModel");
            
            $sql = "SELECT id, full_name, email, phone 
                    FROM users 
                    WHERE role = 'tutor' AND is_active = 1 
                    ORDER BY full_name ASC";
        
            error_log("Tutors SQL Query: " . $sql);
        
            $result = $this->db->query($sql);
            if (!$result) {
                error_log("Tutors query failed: " . $this->db->error);
                return [];
            }
        
            $tutors = $result->fetch_all(MYSQLI_ASSOC);
            error_log("Tutors query successful, found " . count($tutors) . " tutors");
        
            return $tutors;
        } catch (Exception $e) {
            error_log("Exception in getTutorsForForm: " . $e->getMessage());
            return [];
        }
    }

public function getCourseDetails($courseId) {
    try {
        error_log("Getting course details for ID: " . $courseId);
        
        // Get course basic info với tutor information
        $sql = "SELECT c.*, 
                COALESCE(u.full_name, 'Chưa phân công') as tutor_name,
                u.email as tutor_email,
                u.phone as tutor_phone,
                ct.tutor_id,
                COUNT(DISTINCT e.student_id) as enrolled_students,
                COALESCE(cs.completed_sessions, 0) as completed_sessions
            FROM classes c
            LEFT JOIN class_tutors ct ON c.id = ct.class_id AND ct.status = 'active'
            LEFT JOIN users u ON ct.tutor_id = u.id
            LEFT JOIN enrollments e ON c.id = e.class_id AND e.status = 'active'
            LEFT JOIN (
                SELECT class_id, COUNT(*) as completed_sessions
                FROM sessions 
                WHERE status = 'completed'
                GROUP BY class_id
            ) cs ON c.id = cs.class_id
            WHERE c.id = ?
            GROUP BY c.id";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return null;
        }

        $stmt->bind_param("i", $courseId);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return null;
        }

        $result = $stmt->get_result();
        $course = $result->fetch_assoc();
        $stmt->close();

        if (!$course) {
            error_log("Course not found with ID: " . $courseId);
            return null;
        }

        // QUAN TRỌNG: Lấy chi tiết danh sách học viên
        $sql = "SELECT 
                    s.id,
                    s.full_name,
                    s.email,
                    s.phone,
                    s.created_at as registration_date,
                    e.enrollment_date,
                    e.status as enrollment_status,
                    e.sessions_attended,
                    -- Count số buổi học thực tế đã tham gia
                    COUNT(DISTINCT CASE WHEN a.status = 'present' THEN a.id END) as actual_attended_sessions,
                    -- Count tổng số buổi học đã hoàn thành
                    COUNT(DISTINCT CASE WHEN sess.status = 'completed' THEN sess.id END) as total_possible_sessions,
                    -- Tính tỷ lệ tham gia
                    CASE 
                        WHEN COUNT(DISTINCT CASE WHEN sess.status = 'completed' THEN sess.id END) > 0 
                        THEN ROUND((COUNT(DISTINCT CASE WHEN a.status = 'present' THEN a.id END) * 100.0 / COUNT(DISTINCT CASE WHEN sess.status = 'completed' THEN sess.id END)), 1)
                        ELSE 0 
                    END as attendance_rate
                FROM enrollments e
                INNER JOIN users s ON e.student_id = s.id AND s.role = 'student'
                LEFT JOIN sessions sess ON e.class_id = sess.class_id
                LEFT JOIN attendance a ON sess.id = a.session_id AND a.student_id = s.id
                WHERE e.class_id = ? AND e.status = 'active'
                GROUP BY s.id, s.full_name, s.email, s.phone, s.created_at, 
                         e.enrollment_date, e.status, e.sessions_attended
                ORDER BY e.enrollment_date DESC";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed for students: " . $this->db->error);
            $course['students'] = []; // Đặt mảng rỗng nếu lỗi
            return $course;
        }

        $stmt->bind_param("i", $courseId);
        if (!$stmt->execute()) {
            error_log("Execute failed for students: " . $stmt->error);
            $course['students'] = []; // Đặt mảng rỗng nếu lỗi
            return $course;
        }

        $result = $stmt->get_result();
        $students = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Thêm students vào course data
        $course['students'] = $students;
        $course['current_students'] = count($students);

        error_log("Course details loaded successfully:");
        error_log("- Course ID: " . $course['id']);
        error_log("- Course Name: " . $course['class_name']);
        error_log("- Students found: " . count($students));
        
        // Log từng học viên để debug
        foreach ($students as $i => $student) {
            error_log("Student " . ($i+1) . ": " . $student['full_name'] . " (ID: " . $student['id'] . ") - Attendance: " . $student['attendance_rate'] . "%");
        }

        return $course;

    } catch (Exception $e) {
        error_log("Error in getCourseDetails: " . $e->getMessage());
        return null;
    }
}

public function updateCourse($courseId, $data) {
    try {
        error_log("Starting updateCourse - ID: $courseId");
        error_log("Update data: " . json_encode($data));

        $this->db->begin_transaction();

        // Verify course exists
        $stmt = $this->db->prepare("SELECT id FROM classes WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Database prepare error: " . $this->db->error);
        }
        
        $stmt->bind_param("i", $courseId);
        if (!$stmt->execute()) {
            throw new Exception("Failed to verify course: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $stmt->close();
            throw new Exception("Course not found");
        }
        $stmt->close();

        // **THÊM KIỂM TRA TRÙNG LỊCH GIẢNG VIÊN**
        if (!empty($data['tutor_id'])) {
            $conflictCheck = $this->checkTutorScheduleConflict(
                $data['tutor_id'],
                $data['schedule_time'],
                $data['schedule_duration'],
                $data['schedule_days'],
                $data['start_date'],
                $data['end_date'],
                $courseId // Exclude current class from conflict check
            );
            
            if ($conflictCheck['conflict']) {
                throw new Exception($conflictCheck['message']);
            }
        }

        // Update course information
        $sql = "UPDATE classes SET 
                class_name = ?, 
                class_year = ?, 
                class_level = ?, 
                subject = ?, 
                description = ?, 
                max_students = ?, 
                sessions_total = ?, 
                price_per_session = ?, 
                schedule_time = ?, 
                schedule_duration = ?, 
                schedule_days = ?, 
                start_date = ?, 
                end_date = ?,
                updated_at = NOW()
                WHERE id = ?";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database prepare error: " . $this->db->error);
        }

        // Bind parameters with proper data validation
        $class_name = $data['class_name'] ?? '';
        $class_year = intval($data['class_year'] ?? 0);
        $class_level = $data['class_level'] ?? '';
        $subject = $data['subject'] ?? '';
        $description = $data['description'] ?? '';
        $max_students = intval($data['max_students'] ?? 0);
        $sessions_total = intval($data['sessions_total'] ?? 0);
        $price_per_session = floatval($data['price_per_session'] ?? 0);
        $schedule_time = $data['schedule_time'] ?? '';
        $schedule_duration = intval($data['schedule_duration'] ?? 0);
        $schedule_days = $data['schedule_days'] ?? '';
        $start_date = $data['start_date'] ?? '';
        $end_date = $data['end_date'] ?? '';
        
        $stmt->bind_param("sisssiidsisssi",
            $class_name,
            $class_year,
            $class_level,
            $subject,
            $description,
            $max_students,
            $sessions_total,
            $price_per_session,
            $schedule_time,
            $schedule_duration,
            $schedule_days,
            $start_date,
            $end_date,
            $courseId
        );

        if (!$stmt->execute()) {
            throw new Exception("Failed to update course: " . $stmt->error);
        }

        $affected_rows = $stmt->affected_rows;
        $stmt->close();

        error_log("Course update affected rows: " . $affected_rows);

        // Handle tutor assignment
        if (isset($data['tutor_id'])) {
            // Remove existing tutor assignments
            $deleteSql = "DELETE FROM class_tutors WHERE class_id = ?";
            $deleteStmt = $this->db->prepare($deleteSql);
            if (!$deleteStmt) {
                throw new Exception("Failed to prepare delete statement: " . $this->db->error);
            }
            
            $deleteStmt->bind_param("i", $courseId);
            if (!$deleteStmt->execute()) {
                throw new Exception("Failed to delete existing tutor assignments: " . $deleteStmt->error);
            }
            $deleteStmt->close();
            
            // Add new tutor assignment if specified
            if (!empty($data['tutor_id']) && intval($data['tutor_id']) > 0) {
                $insertSql = "INSERT INTO class_tutors (tutor_id, class_id, assigned_date, status) 
                             VALUES (?, ?, CURDATE(), 'active')";
                $insertStmt = $this->db->prepare($insertSql);
                if (!$insertStmt) {
                    throw new Exception("Failed to prepare insert statement: " . $this->db->error);
                }
                
                $tutorId = intval($data['tutor_id']);
                $insertStmt->bind_param("ii", $tutorId, $courseId);
                
                if (!$insertStmt->execute()) {
                    throw new Exception("Failed to assign tutor: " . $insertStmt->error);
                }
                $insertStmt->close();
            }
        }

        $this->db->commit();
        error_log("Course update completed successfully");
        return true;

    } catch (Exception $e) {
        if ($this->db->in_transaction) {
            $this->db->rollback();
        }
        error_log("Error in updateCourse: " . $e->getMessage());
        throw $e;
    }
}

public function createTutor($data) {
    try {
        $this->db->begin_transaction();

        // Check if username exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $data['username']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception('Username already exists');
        }
        $stmt->close();

        // Check if email exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $data['email']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception('Email already exists');
        }
        $stmt->close();

        // Insert new tutor
        $sql = "INSERT INTO users (username, email, password, full_name, role, phone) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ssssss",
            $data['username'],
            $data['email'],
            $data['password'],
            $data['full_name'],
            $data['role'],
            $data['phone']
        );

        if (!$stmt->execute()) {
            throw new Exception($stmt->error);
        }

        $this->db->commit();
        return true;

    } catch (Exception $e) {
        $this->db->rollback();
        error_log("Error creating tutor: " . $e->getMessage());
        throw $e;
    }
}

public function getTutorDetails($tutorId) {
    try {
        // Get tutor basic info
        $sql = "SELECT u.id, u.username, u.full_name, u.email, u.phone, u.created_at, 
                u.is_active, u.notes,
                (SELECT COUNT(*) FROM class_tutors ct 
                 WHERE ct.tutor_id = u.id AND ct.status = 'active') as active_classes
                FROM users u 
                WHERE u.id = ? AND u.role = 'tutor'";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        $stmt->bind_param("i", $tutorId);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $tutor = $result->fetch_assoc();
        $stmt->close();

        if (!$tutor) {
            return null;
        }

        // Get tutor's active classes
        $sql = "SELECT c.*, 
                COUNT(DISTINCT e.student_id) as enrolled_students
                FROM classes c
                INNER JOIN class_tutors ct ON c.id = ct.class_id
                LEFT JOIN enrollments e ON c.id = e.class_id AND e.status = 'active'
                WHERE ct.tutor_id = ? AND ct.status = 'active'
                GROUP BY c.id
                ORDER BY c.start_date DESC";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        $stmt->bind_param("i", $tutorId);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $tutor['classes'] = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $tutor;

    } catch (Exception $e) {
        error_log("Error getting tutor details: " . $e->getMessage());
        throw $e;
    }
}

public function updateTutor($tutorId, $data) {
    try {
        $this->db->begin_transaction();

        // Check if email already exists for other tutors
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id != ? AND role = 'tutor'");
        $stmt->bind_param("si", $data['email'], $tutorId);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception('Email already exists');
        }
        $stmt->close();

        // Update tutor information
        $sql = "UPDATE users SET 
                full_name = ?,
                email = ?,
                phone = ?,
                updated_at = NOW()
                WHERE id = ? AND role = 'tutor'";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        $stmt->bind_param("sssi", 
            $data['full_name'],
            $data['email'],
            $data['phone'],
            $tutorId
        );

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $affected_rows = $stmt->affected_rows;
        $stmt->close();

        if ($affected_rows === 0) {
            throw new Exception("No tutor found with ID: $tutorId");
        }

        $this->db->commit();
        return true;

    } catch (Exception $e) {
        $this->db->rollback();
        error_log("Error updating tutor: " . $e->getMessage());
        throw $e;
    }
}

public function getStudentDetails($studentId) {
    try {
        // Get student basic info
        $sql = "SELECT u.id, u.username, u.full_name, u.email, u.phone, 
                u.created_at, u.is_active,
                (SELECT COUNT(*) FROM enrollments e 
                 WHERE e.student_id = u.id AND e.status = 'active') as active_classes
                FROM users u 
                WHERE u.id = ? AND u.role = 'student'";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        $stmt->bind_param("i", $studentId);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $student = $result->fetch_assoc();
        $stmt->close();

        if (!$student) {
            return null;
        }

        // Get student's active enrollments
        $sql = "SELECT c.*, e.enrollment_date, e.status as enrollment_status
                FROM enrollments e
                INNER JOIN classes c ON e.class_id = c.id
                WHERE e.student_id = ? AND e.status = 'active'
                ORDER BY e.enrollment_date DESC";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        $stmt->bind_param("i", $studentId);
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $student['enrollments'] = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $student;

    } catch (Exception $e) {
        error_log("Error getting student details: " . $e->getMessage());
        throw $e;
    }
}

public function updateStudent($studentId, $data) {
    try {
        $this->db->begin_transaction();

        // Check if email already exists for other students
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ? AND id != ? AND role = 'student'");
        $stmt->bind_param("si", $data['email'], $studentId);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception('Email đã tồn tại');
        }
        $stmt->close();

        // Update student information
        $sql = "UPDATE users SET 
                full_name = ?,
                email = ?,
                phone = ?,
                is_active = ?,
                updated_at = NOW()
                WHERE id = ? AND role = 'student'";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        $stmt->bind_param("sssii", 
            $data['full_name'],
            $data['email'],
            $data['phone'],
            $data['is_active'],
            $studentId
        );

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $affected_rows = $stmt->affected_rows;
        $stmt->close();

        if ($affected_rows === 0) {
            throw new Exception("Không tìm thấy học viên với ID: $studentId");
        }

        $this->db->commit();
        return true;

    } catch (Exception $e) {
        $this->db->rollback();
        error_log("Error updating student: " . $e->getMessage());
        throw $e;
    }
}

public function getAvailableCourses() {
    try {
        $sql = "SELECT c.*, 
                COUNT(DISTINCT e.student_id) as enrolled_students,
                (c.max_students - COUNT(DISTINCT e.student_id)) as available_slots
                FROM classes c
                LEFT JOIN enrollments e ON c.id = e.class_id AND e.status = 'active'
                WHERE c.status = 'active'
                GROUP BY c.id
                HAVING available_slots > 0
                ORDER BY c.start_date ASC";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->db->error);
        }

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $result = $stmt->get_result();
        $courses = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Add some processing for each course
        foreach ($courses as &$course) {
            $course['available_slots'] = $course['max_students'] - $course['enrolled_students'];
            $course['schedule_info'] = $this->formatScheduleInfo($course);
            $course['price_formatted'] = number_format($course['price_per_session'], 0, ',', '.') . ' VNĐ';
        }

        return $courses;

    } catch (Exception $e) {
        error_log("Error getting available courses: " . $e->getMessage());
        throw $e;
    }
}

private function formatScheduleInfo($course) {
    $days = $course['schedule_days'] ?? '';
    $time = $course['schedule_time'] ? date('H:i', strtotime($course['schedule_time'])) : '';
    $duration = $course['schedule_duration'] ?? '';
    
    return sprintf("%s %s (%d phút)", $days, $time, $duration);
}

public function enrollStudent($studentId, $courseId) {
    try {
        $this->db->begin_transaction();

        // Check if student exists
        $stmt = $this->db->prepare("SELECT id, full_name FROM users WHERE id = ? AND role = 'student' AND is_active = 1");
        $stmt->bind_param("i", $studentId);
        $stmt->execute();
        $student_result = $stmt->get_result();
        if ($student_result->num_rows === 0) {
            throw new Exception('Học viên không tồn tại hoặc đã bị vô hiệu hóa');
        }
        $student = $student_result->fetch_assoc();
        $stmt->close();

        // Check if course exists and has available slots
        $sql = "SELECT c.*, 
                       COUNT(e.student_id) as enrolled_students
                FROM classes c 
                LEFT JOIN enrollments e ON c.id = e.class_id AND e.status = 'active'
                WHERE c.id = ? AND c.status = 'active'
                GROUP BY c.id";
        
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("i", $courseId);
        $stmt->execute();
        $result = $stmt->get_result();
        $course = $result->fetch_assoc();
        $stmt->close();

        if (!$course) {
            throw new Exception('Lớp học không tồn tại hoặc đã bị đóng');
        }

        if ($course['enrolled_students'] >= $course['max_students']) {
            throw new Exception('Lớp học đã đầy. Số lượng tối đa: ' . $course['max_students'] . ' học viên');
        }

        // Check if student is already enrolled
        $stmt = $this->db->prepare(
            "SELECT id FROM enrollments 
             WHERE student_id = ? AND class_id = ? AND status IN ('active', 'completed')"
        );
        $stmt->bind_param("ii", $studentId, $courseId);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception('Học viên đã được đăng ký vào lớp học này');
        }
        $stmt->close();

        // **THÊM KIỂM TRA TRÙNG LỊCH**
        $conflictCheck = $this->checkScheduleConflict($studentId, $courseId);
        if ($conflictCheck['conflict']) {
            throw new Exception($conflictCheck['message']);
        }

        // Create enrollment
        $sql = "INSERT INTO enrollments (student_id, class_id, enrollment_date, status) 
                VALUES (?, ?, CURDATE(), 'active')";
        $stmt = $this->db->prepare($sql);
        $stmt->bind_param("ii", $studentId, $courseId);
        
        if (!$stmt->execute()) {
            throw new Exception('Không thể tạo đăng ký học: ' . $stmt->error);
        }
        $stmt->close();

        $this->db->commit();
        
        error_log("Student {$student['full_name']} (ID: $studentId) successfully enrolled in course ID: $courseId");
        return true;

    } catch (Exception $e) {
        $this->db->rollback();
        error_log("Error enrolling student: " . $e->getMessage());
        throw $e;
    }
}

public function checkScheduleConflict($studentId, $newClassId) {
    try {
        // Get schedule info of the new class
        $new_class_sql = "SELECT schedule_time, schedule_duration, schedule_days, start_date, end_date 
                         FROM classes 
                         WHERE id = ? AND status = 'active'";
        $stmt = $this->db->prepare($new_class_sql);
        $stmt->bind_param("i", $newClassId);
        $stmt->execute();
        $result = $stmt->get_result();
        $newClass = $result->fetch_assoc();
        $stmt->close();
        
        if (!$newClass) {
            return ['conflict' => true, 'message' => 'Lớp học không tồn tại hoặc đã bị đóng'];
        }
        
        // Get all active classes that student is enrolled in
        $student_classes_sql = "SELECT c.id, c.class_name, c.schedule_time, c.schedule_duration, 
                               c.schedule_days, c.start_date, c.end_date
                               FROM classes c
                               INNER JOIN enrollments e ON c.id = e.class_id
                               WHERE e.student_id = ? AND e.status = 'active' 
                               AND c.status = 'active' AND c.id != ?";
        
        $stmt = $this->db->prepare($student_classes_sql);
        $stmt->bind_param("ii", $studentId, $newClassId);
        $stmt->execute();
        $result = $stmt->get_result();
        $studentClasses = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        // Parse new class schedule
        $newScheduleDays = $this->parseScheduleDays($newClass['schedule_days']);
        $newStartTime = strtotime($newClass['schedule_time']);
        $newEndTime = $newStartTime + ($newClass['schedule_duration'] * 60);
        $newClassStart = new DateTime($newClass['start_date']);
        $newClassEnd = new DateTime($newClass['end_date']);
        
        // Check for conflicts with existing classes
        foreach ($studentClasses as $existingClass) {
            $existingScheduleDays = $this->parseScheduleDays($existingClass['schedule_days']);
            $existingStartTime = strtotime($existingClass['schedule_time']);
            $existingEndTime = $existingStartTime + ($existingClass['schedule_duration'] * 60);
            $existingClassStart = new DateTime($existingClass['start_date']);
            $existingClassEnd = new DateTime($existingClass['end_date']);
            
            // Check if there's any overlap in schedule days
            $dayConflict = array_intersect($newScheduleDays, $existingScheduleDays);
            
            if (!empty($dayConflict)) {
                // Check if there's time overlap
                $timeConflict = ($newStartTime < $existingEndTime) && ($newEndTime > $existingStartTime);
                
                if ($timeConflict) {
                    // Check if there's date range overlap
                    $dateConflict = ($newClassStart <= $existingClassEnd) && ($newClassEnd >= $existingClassStart);
                    
                    if ($dateConflict) {
                        // Convert day numbers back to Vietnamese day names for display
                        $conflictDays = array_map(function($day) {
                            $dayNames = [0 => 'CN', 1 => 'T2', 2 => 'T3', 3 => 'T4', 4 => 'T5', 5 => 'T6', 6 => 'T7'];
                            return $dayNames[$day];
                        }, $dayConflict);
                        
                        return [
                            'conflict' => true,
                            'message' => sprintf(
                                'Trùng lịch với lớp "%s" vào %s lúc %s-%s',
                                $existingClass['class_name'],
                                implode(', ', $conflictDays),
                                date('H:i', $existingStartTime),
                                date('H:i', $existingEndTime)
                            ),
                            'conflicting_class' => $existingClass
                        ];
                    }
                }
            }
        }
        
        return ['conflict' => false, 'message' => 'Không có trùng lịch'];
        
    } catch (Exception $e) {
        error_log("Error checking schedule conflict: " . $e->getMessage());
        return ['conflict' => true, 'message' => 'Lỗi kiểm tra trùng lịch'];
    }
}

public function checkTutorScheduleConflict($tutorId, $scheduleTime, $scheduleDuration, $scheduleDays, $startDate, $endDate, $excludeClassId = null) {
    try {
        // Get all active classes that tutor is assigned to
        $sql = "SELECT c.id, c.class_name, c.schedule_time, c.schedule_duration, 
                       c.schedule_days, c.start_date, c.end_date
                FROM classes c
                INNER JOIN class_tutors ct ON c.id = ct.class_id
                WHERE ct.tutor_id = ? AND ct.status = 'active' 
                AND c.status = 'active'";
        
        $params = [$tutorId];
        
        if ($excludeClassId) {
            $sql .= " AND c.id != ?";
            $params[] = $excludeClassId;
        }
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return ['conflict' => true, 'message' => 'Lỗi kiểm tra trùng lịch'];
        }
        
        $types = str_repeat('i', count($params));
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $tutorClasses = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        // Parse new class schedule
        $newScheduleDays = $this->parseScheduleDays($scheduleDays);
        $newStartTime = strtotime($scheduleTime);
        $newEndTime = $newStartTime + ($scheduleDuration * 60);
        $newClassStart = new DateTime($startDate);
        $newClassEnd = new DateTime($endDate);
        
        // Check for conflicts with existing classes
        foreach ($tutorClasses as $existingClass) {
            $existingScheduleDays = $this->parseScheduleDays($existingClass['schedule_days']);
            $existingStartTime = strtotime($existingClass['schedule_time']);
            $existingEndTime = $existingStartTime + ($existingClass['schedule_duration'] * 60);
            $existingClassStart = new DateTime($existingClass['start_date']);
            $existingClassEnd = new DateTime($existingClass['end_date']);
            
            // Check if there's any overlap in schedule days
            $dayConflict = array_intersect($newScheduleDays, $existingScheduleDays);
            
            if (!empty($dayConflict)) {
                // Check if there's time overlap
                $timeConflict = ($newStartTime < $existingEndTime) && ($newEndTime > $existingStartTime);
                
                if ($timeConflict) {
                    // Check if there's date range overlap
                    $dateConflict = ($newClassStart <= $existingClassEnd) && ($newClassEnd >= $existingClassStart);
                    
                    if ($dateConflict) {
                        // Convert day numbers back to Vietnamese day names for display
                        $conflictDays = array_map(function($day) {
                            $dayNames = [0 => 'CN', 1 => 'T2', 2 => 'T3', 3 => 'T4', 4 => 'T5', 5 => 'T6', 6 => 'T7'];
                            return $dayNames[$day];
                        }, $dayConflict);
                        
                        return [
                            'conflict' => true,
                            'message' => sprintf(
                                'Giảng viên đã có lịch dạy lớp "%s" vào %s lúc %s-%s từ %s đến %s',
                                $existingClass['class_name'],
                                implode(', ', $conflictDays),
                                date('H:i', $existingStartTime),
                                date('H:i', $existingEndTime),
                                date('d/m/Y', strtotime($existingClass['start_date'])),
                                date('d/m/Y', strtotime($existingClass['end_date']))
                            ),
                            'conflicting_class' => $existingClass
                        ];
                    }
                }
            }
        }
        
        return ['conflict' => false, 'message' => 'Không có trùng lịch'];
        
    } catch (Exception $e) {
        error_log("Error checking tutor schedule conflict: " . $e->getMessage());
        return ['conflict' => true, 'message' => 'Lỗi kiểm tra trùng lịch'];
    }
}

private function parseScheduleDays($scheduleDaysStr) {
    $daysMap = [
        'CN' => 0, 'T2' => 1, 'T3' => 2, 'T4' => 3,
        'T5' => 4, 'T6' => 5, 'T7' => 6
    ];
    
    if (!$scheduleDaysStr) return [];
    
    $days = explode(',', $scheduleDaysStr);
    $result = [];
    
    foreach ($days as $day) {
        $day = trim($day);
        if (isset($daysMap[$day])) {
            $result[] = $daysMap[$day];
        }
    }
    
    return $result;
}

public function removeFromCourse($studentId, $courseId) {
    try {
        $this->db->begin_transaction();

        // Validate input
        if (!$studentId || !$courseId) {
            throw new Exception("Thông tin không hợp lệ");
        }

        // Check if enrollment exists and is active
        $sql = "SELECT id FROM enrollments 
                WHERE student_id = ? AND class_id = ? AND status = 'active'";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Lỗi truy vấn: " . $this->db->error);
        }

        $stmt->bind_param("ii", $studentId, $courseId);
        
        if (!$stmt->execute()) {
            throw new Exception("Lỗi thực thi: " . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if ($result->num_rows === 0) {
            $stmt->close();
            throw new Exception("Học viên không có trong lớp học này");
        }
        $stmt->close();

        // Update enrollment status to 'dropped'
        $sql = "UPDATE enrollments 
                SET status = 'dropped', 
                    updated_at = NOW() 
                WHERE student_id = ? 
                AND class_id = ? 
                AND status = 'active'";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Lỗi truy vấn: " . $this->db->error);
        }

        $stmt->bind_param("ii", $studentId, $courseId);
        
        if (!$stmt->execute()) {
            throw new Exception("Lỗi cập nhật: " . $stmt->error);
        }
        
        $affected_rows = $stmt->affected_rows;
        $stmt->close();

        if ($affected_rows === 0) {
            throw new Exception("Không thể xóa học viên khỏi lớp học");
        }

        $this->db->commit();
        return true;

    } catch (Exception $e) {
        $this->db->rollback();
        error_log("Error removing student from course: " . $e->getMessage());
        throw $e;
    }
}

// Add this method for debugging purposes
public function getConnection() {
    return $this->db;
}

    public function getAllParents($limit = 20, $offset = 0) {
        try {
            error_log("Getting all parents with limit: $limit, offset: $offset");
            
            $sql = "SELECT 
                    u.id, 
                    u.username, 
                    u.full_name, 
                    u.email, 
                    u.phone, 
                    u.address,
                    u.created_at, 
                    u.is_active,
                    COUNT(ps.student_id) as total_children
                FROM users u
                LEFT JOIN parent_student ps ON u.id = ps.parent_id
                WHERE u.role = 'parent'
                GROUP BY u.id, u.username, u.full_name, u.email, u.phone, u.address, u.created_at, u.is_active
                ORDER BY u.created_at DESC";
        
            if ($limit !== null && $offset !== null) {
                $sql .= " LIMIT ? OFFSET ?";
                $stmt = $this->db->prepare($sql);
                if (!$stmt) {
                    error_log("Prepare failed: " . $this->db->error);
                    return [];
                }
                $stmt->bind_param("ii", $limit, $offset);
            } else {
                $stmt = $this->db->prepare($sql);
                if (!$stmt) {
                    error_log("Prepare failed: " . $this->db->error);
                    return [];
                }
            }
            
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                return [];
            }
            
            $result = $stmt->get_result();
            $parents = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            error_log("Found " . count($parents) . " parents");
            foreach ($parents as $parent) {
                error_log("Parent: " . $parent['full_name'] . " - Children: " . $parent['total_children']);
            }
            
            return $parents;
            
        } catch (Exception $e) {
            error_log("Error getting parents: " . $e->getMessage());
            return [];
        }
    }

    public function getParentDetails($parentId) {
        try {
            error_log("=== GETTING PARENT DETAILS FOR ID: " . $parentId . " ===");
            
            // Get parent basic info
            $sql = "SELECT u.id, u.username, u.full_name, u.email, u.phone, u.address,
                       u.birthdate, u.created_at, u.is_active
                FROM users u
                WHERE u.id = ? AND u.role = 'parent'";
        
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return null;
            }
        
            $stmt->bind_param("i", $parentId);
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                return null;
            }
        
            $result = $stmt->get_result();
            $parent = $result->fetch_assoc();
            $stmt->close();
        
            if (!$parent) {
                error_log("Parent not found with ID: " . $parentId);
                return null;
            }
        
            error_log("Parent found: " . $parent['full_name']);
        
            // STEP 1: Get all children linked to this parent
            $sql = "SELECT 
                    ps.id as link_id,
                    ps.parent_id, 
                    ps.student_id, 
                    ps.relationship_type, 
                    ps.is_primary,
                    s.id as student_table_id,
                    s.username, 
                    s.full_name, 
                    s.email, 
                    s.phone, 
                    s.created_at as registration_date, 
                    s.is_active
                FROM parent_student ps
                INNER JOIN users s ON ps.student_id = s.id
                WHERE ps.parent_id = ? AND s.role = 'student'
                ORDER BY ps.is_primary DESC, s.full_name ASC";
        
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed for children: " . $this->db->error);
                return null;
            }
        
            $stmt->bind_param("i", $parentId);
            if (!$stmt->execute()) {
                error_log("Execute failed for children: " . $stmt->error);
                return null;
            }
        
            $result = $stmt->get_result();
            $children = [];
        
            while ($row = $result->fetch_assoc()) {
                $children[] = $row;
                error_log("Found child: ID=" . $row['student_id'] . ", Name=" . $row['full_name']);
            }
            $stmt->close();
        
            error_log("Total children found: " . count($children));
        
            // STEP 2: Process each child individually with fresh data
            $processedChildren = [];
        
            foreach ($children as $index => $child) {
                $childId = $child['student_id'];
                error_log("=== Processing child " . ($index + 1) . "/" . count($children) . ": ID=" . $childId . ", Name=" . $child['full_name'] . " ===");
            
                // Create a fresh array for this child
                $processedChild = [
                    'id' => $childId,
                    'student_id' => $childId,
                    'link_id' => $child['link_id'],
                    'username' => $child['username'],
                    'full_name' => $child['full_name'],
                    'email' => $child['email'],
                    'phone' => $child['phone'],
                    'registration_date' => $child['registration_date'],
                    'is_active' => $child['is_active'],
                    'relationship_type' => $child['relationship_type'],
                    'is_primary' => $child['is_primary'],
                    // Initialize all stats
                    'enrolled_classes' => 0,
                    'active_classes' => 0,
                    'total_sessions' => 0,
                    'attended_sessions' => 0,
                    'absent_sessions' => 0,
                    'attendance_rate' => 0,
                    'total_paid' => 0,
                    'classes' => [],
                    'recent_attendance' => []
                ];
            
                // Get enrollment statistics for this specific child
                $sql = "SELECT 
                        COUNT(DISTINCT e.class_id) as enrolled_classes,
                        COUNT(DISTINCT CASE WHEN c.status = 'active' THEN e.class_id END) as active_classes
                    FROM enrollments e
                    INNER JOIN classes c ON e.class_id = c.id
                    WHERE e.student_id = ? AND e.status = 'active'";
            
                $stmt = $this->db->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("i", $childId);
                    if ($stmt->execute()) {
                        $result = $stmt->get_result();
                        $enrollmentData = $result->fetch_assoc();
                        $processedChild['enrolled_classes'] = intval($enrollmentData['enrolled_classes'] ?? 0);
                        $processedChild['active_classes'] = intval($enrollmentData['active_classes'] ?? 0);
                        error_log("Child $childId enrollment: " . $processedChild['enrolled_classes'] . " classes");
                    } else {
                        error_log("Failed to get enrollment for child $childId: " . $stmt->error);
                    }
                    $stmt->close();
                }
            
                // Get attendance statistics for this child
                $sql = "SELECT 
                        COUNT(DISTINCT a.id) as total_sessions,
                        COUNT(DISTINCT CASE WHEN a.status = 'present' THEN a.id END) as attended_sessions,
                        COUNT(DISTINCT CASE WHEN a.status = 'absent' THEN a.id END) as absent_sessions
                    FROM enrollments e
                    INNER JOIN classes c ON e.class_id = c.id AND c.status = 'active'
                    INNER JOIN sessions sess ON c.id = sess.class_id AND sess.status = 'completed'
                    INNER JOIN attendance a ON sess.id = a.session_id AND a.student_id = e.student_id
                    WHERE e.student_id = ? AND e.status = 'active'";
            
                $stmt = $this->db->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("i", $childId);
                    if ($stmt->execute()) {
                        $result = $stmt->get_result();
                        $attendanceData = $result->fetch_assoc();
                        $processedChild['total_sessions'] = intval($attendanceData['total_sessions'] ?? 0);
                        $processedChild['attended_sessions'] = intval($attendanceData['attended_sessions'] ?? 0);
                        $processedChild['absent_sessions'] = intval($attendanceData['absent_sessions'] ?? 0);
                        
                        if ($processedChild['total_sessions'] > 0) {
                            $processedChild['attendance_rate'] = round(($processedChild['attended_sessions'] / $processedChild['total_sessions']) * 100, 1);
                        }
                        
                        error_log("Child $childId attendance: " . $processedChild['attended_sessions'] . "/" . $processedChild['total_sessions'] . " (" . $processedChild['attendance_rate'] . "%)");
                    } else {
                        error_log("Failed to get attendance for child $childId: " . $stmt->error);
                    }
                    $stmt->close();
                }
            
                // Get payment total for this child
                $sql = "SELECT COALESCE(SUM(p.final_amount), 0) as total_paid
                    FROM payments p
                    WHERE p.student_id = ? AND p.status = 'completed'";
            
                $stmt = $this->db->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("i", $childId);
                    if ($stmt->execute()) {
                        $result = $stmt->get_result();
                        $paymentData = $result->fetch_assoc();
                        $processedChild['total_paid'] = floatval($paymentData['total_paid'] ?? 0);
                        error_log("Child $childId total paid: " . $processedChild['total_paid']);
                    } else {
                        error_log("Failed to get payments for child $childId: " . $stmt->error);
                    }
                    $stmt->close();
                }
            
                // Get current active classes for this child
                $sql = "SELECT 
                        c.id,
                        c.class_name,
                        c.class_year,
                        c.class_level,
                        c.subject,
                        c.status,
                        c.start_date,
                        c.end_date,
                        c.schedule_time,
                        c.schedule_days,
                        c.schedule_duration,
                        c.sessions_total,
                        c.sessions_completed,
                        e.enrollment_date,
                        e.status as enrollment_status,
                        COALESCE(tutor.full_name, 'Chưa phân công') as tutor_name
                    FROM enrollments e
                    INNER JOIN classes c ON e.class_id = c.id
                    LEFT JOIN class_tutors ct ON c.id = ct.class_id AND ct.status = 'active'
                    LEFT JOIN users tutor ON ct.tutor_id = tutor.id
                    WHERE e.student_id = ? AND e.status = 'active' AND c.status = 'active'
                    ORDER BY e.enrollment_date DESC";
            
            $stmt = $this->db->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("i", $childId);
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    $classes = [];
                    
                    while ($classRow = $result->fetch_assoc()) {
                        // Get attendance details for this specific class and child
                        $sql2 = "SELECT 
                                    COUNT(DISTINCT a.id) as total_sessions_attended,
                                    COUNT(DISTINCT CASE WHEN a.status = 'present' THEN a.id END) as present_sessions,
                                    COUNT(DISTINCT CASE WHEN a.status = 'absent' THEN a.id END) as absent_sessions
                                FROM sessions sess
                                INNER JOIN attendance a ON sess.id = a.session_id
                                WHERE sess.class_id = ? AND a.student_id = ? AND sess.status = 'completed'";
                        
                        $stmt2 = $this->db->prepare($sql2);
                        if ($stmt2) {
                            $stmt2->bind_param("ii", $classRow['id'], $childId);
                            if ($stmt2->execute()) {
                                $result2 = $stmt2->get_result();
                                $classAttendance = $result2->fetch_assoc();
                                $classRow['total_sessions_attended'] = intval($classAttendance['total_sessions_attended'] ?? 0);
                                $classRow['present_sessions'] = intval($classAttendance['present_sessions'] ?? 0);
                                $classRow['absent_sessions'] = intval($classAttendance['absent_sessions'] ?? 0);
                                
                                if ($classRow['total_sessions_attended'] > 0) {
                                    $classRow['class_attendance_rate'] = round(($classRow['present_sessions'] / $classRow['total_sessions_attended']) * 100, 1);
                                } else {
                                    $classRow['class_attendance_rate'] = 0;
                                }
                            }
                            $stmt2->close();
                        }
                        
                        $classes[] = $classRow;
                    }
                    
                    $processedChild['classes'] = $classes;
                    error_log("Child $childId has " . count($classes) . " classes");
                } else {
                    error_log("Failed to get classes for child $childId: " . $stmt->error);
                    $processedChild['classes'] = [];
                }
                $stmt->close();
            }
            
            // Get recent attendance for this child
            $sql = "SELECT 
                        sess.session_date,
                        a.status,
                        c.class_name,
                        c.subject
                    FROM attendance a
                    INNER JOIN sessions sess ON a.session_id = sess.id
                    INNER JOIN classes c ON sess.class_id = c.id
                    INNER JOIN enrollments e ON c.id = e.class_id AND e.student_id = a.student_id
                    WHERE a.student_id = ? AND e.status = 'active'
                    ORDER BY sess.session_date DESC
                    LIMIT 5";
            
            $stmt = $this->db->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("i", $childId);
                if ($stmt->execute()) {
                    $result = $stmt->get_result();
                    $recentAttendance = [];
                    while ($attendanceRow = $result->fetch_assoc()) {
                        $recentAttendance[] = $attendanceRow;
                    }
                    $processedChild['recent_attendance'] = $recentAttendance;
                    error_log("Child $childId has " . count($recentAttendance) . " recent attendance records");
                } else {
                    error_log("Failed to get recent attendance for child $childId: " . $stmt->error);
                    $processedChild['recent_attendance'] = [];
                }
                $stmt->close();
            }
            
            // Add this processed child to the array
            $processedChildren[] = $processedChild;
            
            error_log("=== Completed processing child " . ($index + 1) . ": " . $child['full_name'] . " ===");
            error_log("Final stats - Classes: " . $processedChild['enrolled_classes'] . ", Attendance: " . $processedChild['attendance_rate'] . "%, Paid: " . $processedChild['total_paid']);
        }
        
        $parent['children'] = $processedChildren;
        
        // Calculate overall statistics for the parent
        $totalChildren = count($processedChildren);
        $totalClasses = 0;
        $totalSessions = 0;
        $totalAttendedSessions = 0;
        $totalPaid = 0;
        
        foreach ($processedChildren as $child) {
            $totalClasses += $child['enrolled_classes'];
            $totalSessions += $child['total_sessions'];
            $totalAttendedSessions += $child['attended_sessions'];
            $totalPaid += $child['total_paid'];
        }
        
        $avgAttendanceRate = $totalSessions > 0 ? round(($totalAttendedSessions / $totalSessions) * 100, 1) : 0;
        
        $parent['statistics'] = [
            'total_children' => $totalChildren,
            'total_classes' => $totalClasses,
            'total_sessions' => $totalSessions,
            'attended_sessions' => $totalAttendedSessions,
            'total_paid' => $totalPaid,
            'average_attendance_rate' => $avgAttendanceRate
        ];
        
        error_log("=== FINAL PARENT SUMMARY ===");
        error_log("Parent: " . $parent['full_name']);
        error_log("Total children processed: " . count($processedChildren));
        error_log("Overall statistics: " . json_encode($parent['statistics']));
        
        foreach ($processedChildren as $i => $child) {
            error_log("Child " . ($i + 1) . ": " . $child['full_name'] . " - Classes: " . $child['enrolled_classes'] . ", Attendance: " . $child['attendance_rate'] . "%");
        }
        
        return $parent;

    } catch (Exception $e) {
        error_log("Error getting parent details: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        return null;
    }
}


public function updateParent($parentId, $data) {
    try {
        error_log("=== AdminModel::updateParent DEBUG START ===");
        error_log("Parent ID: " . $parentId);
        error_log("Update data: " . json_encode($data));
        
        // Validate input
        if (!$parentId || $parentId <= 0) {
            throw new Exception("Invalid parent ID");
        }
        
        $this->db->begin_transaction();

        // Check if parent exists
        $check_sql = "SELECT id, full_name, email FROM users WHERE id = ? AND role = 'parent'";
        $check_stmt = $this->db->prepare($check_sql);
        if (!$check_stmt) {
            throw new Exception("Prepare check failed: " . $this->db->error);
        }
        
        $check_stmt->bind_param("i", $parentId);
        if (!$check_stmt->execute()) {
            throw new Exception("Execute check failed: " . $check_stmt->error);
        }
        
        $check_result = $check_stmt->get_result();
        if ($check_result->num_rows === 0) {
            $check_stmt->close();
            throw new Exception("Parent not found with ID: $parentId");
        }
        $current_data = $check_result->fetch_assoc();
        $check_stmt->close();
        
        error_log("Current parent data: " . json_encode($current_data));

        // Check if email already exists for other parents (exclude current parent)
        $email_check_sql = "SELECT id FROM users WHERE email = ? AND id != ? AND role = 'parent'";
        $email_stmt = $this->db->prepare($email_check_sql);
        if (!$email_stmt) {
            throw new Exception("Prepare email check failed: " . $this->db->error);
        }
        
        $email_stmt->bind_param("si", $data['email'], $parentId);
        if (!$email_stmt->execute()) {
            throw new Exception("Execute email check failed: " . $email_stmt->error);
        }
        
        $email_result = $email_stmt->get_result();
        if ($email_result->num_rows > 0) {
            $email_stmt->close();
            throw new Exception("Email đã tồn tại cho phụ huynh khác");
        }
        $email_stmt->close();

        // Update parent information
        $sql = "UPDATE users SET 
                full_name = ?, 
                email = ?, 
                phone = ?, 
                address = ?,
                is_active = ?,
                updated_at = NOW()
                WHERE id = ? AND role = 'parent'";

        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            throw new Exception("Prepare update failed: " . $this->db->error);
        }

        $stmt->bind_param("ssssii", 
            $data['full_name'], 
            $data['email'], 
            $data['phone'],
            $data['address'],
            $data['is_active'],
            $parentId
        );

        if (!$stmt->execute()) {
            throw new Exception("Execute update failed: " . $stmt->error);
        }

        $affected_rows = $stmt->affected_rows;
        $stmt->close();
        
        error_log("Update affected rows: " . $affected_rows);

        if ($affected_rows >= 0) { // 0 is also success (no changes needed)
            $this->db->commit();
            error_log("Parent update successful");
            return true;
        } else {
            $this->db->rollback();
            throw new Exception("No rows were updated");
        }

    } catch (Exception $e) {
        if ($this->db->in_transaction) {
            $this->db->rollback();
        }
        error_log("Error in updateParent: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        throw $e;
    }
}

public function createParent($data) {
    try {
        $this->db->begin_transaction();

        // Check if username exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $data['username']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("Username already exists");
        }
        $stmt->close();

        // Check if email exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $data['email']);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            throw new Exception("Email already exists");
        }
        $stmt->close();

        // Insert new parent
        $sql = "INSERT INTO users (username, email, password, full_name, role, phone, address) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
            $role = 'parent';
            
            $stmt->bind_param("sssssss",
                $data['username'],
                $data['email'],
                $hashed_password,
                $data['full_name'],
                $role,
                $data['phone'] ?? '',
                $data['address'] ?? ''
            );

            if (!$stmt->execute()) {
                throw new Exception("Failed to create parent: " . $stmt->error);
            }

            $stmt->close();
            $this->db->commit();
            return true;

    } catch (Exception $e) {
        $this->db->rollback();
        error_log("Error creating parent: " . $e->getMessage());
        throw $e;
    }
}

public function searchStudentsForLink($searchTerm, $parentId) {
        try {
            $sql = "SELECT u.id, u.username, u.full_name, u.email, u.phone, u.created_at,
                       CASE WHEN ps.parent_id IS NOT NULL THEN 1 ELSE 0 END as already_linked
                FROM users u
                LEFT JOIN parent_student ps ON u.id = ps.student_id AND ps.parent_id = ?
                WHERE u.role = 'student' 
                AND u.is_active = 1
                AND (
                    u.full_name LIKE ? OR 
                    u.email LIKE ? OR 
                    u.username LIKE ? OR
                    CONCAT('HV', LPAD(u.id, 4, '0')) LIKE ?
                )
                ORDER BY already_linked ASC, u.full_name ASC
                LIMIT 20";
        
            $searchPattern = '%' . $searchTerm . '%';
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->db->error);
            }
            
            $stmt->bind_param("issss", $parentId, $searchPattern, $searchPattern, $searchPattern, $searchPattern);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $students = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            return $students;
        } catch (Exception $e) {
            error_log("Error searching students for link: " . $e->getMessage());
            throw $e;
        }
    }

    public function linkParentStudent($parentId, $studentId, $relationshipType, $isPrimary) {
        try {
            $this->db->begin_transaction();

            // Verify parent exists and is active
            $stmt = $this->db->prepare("SELECT id FROM users WHERE id = ? AND role = 'parent' AND is_active = 1");
            $stmt->bind_param("i", $parentId);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) {
                throw new Exception("Parent not found or inactive");
            }
            $stmt->close();

            // Verify student exists and is active
            $stmt = $this->db->prepare("SELECT id FROM users WHERE id = ? AND role = 'student' AND is_active = 1");
            $stmt->bind_param("i", $studentId);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) {
                throw new Exception("Student not found or inactive");
            }
            $stmt->close();

            // Check if relationship already exists
            $stmt = $this->db->prepare("SELECT id FROM parent_student WHERE parent_id = ? AND student_id = ?");
            $stmt->bind_param("ii", $parentId, $studentId);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                throw new Exception("Student is already linked to this parent");
            }
            $stmt->close();

           

            // If this is set as primary, remove primary status from other relationships for this student
            if ($isPrimary) {
                $stmt = $this->db->prepare("UPDATE parent_student SET is_primary = 0 WHERE student_id = ?");
                $stmt->bind_param("i", $studentId);
                $stmt->execute();
                $stmt->close();
            }

            // Insert new relationship
            $sql = "INSERT INTO parent_student (parent_id, student_id, relationship_type, is_primary, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $this->db->error);
            }

            $stmt->bind_param("iisi", $parentId, $studentId, $relationshipType, $isPrimary);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            $stmt->close();

            $this->db->commit();
            return true;

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error linking parent and student: " . $e->getMessage());
            throw $e;
        }
    }

    public function getStudentSchedule($studentId, $start_date = null, $end_date = null) {
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
            
            $params = [$studentId];
            
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
}
?>