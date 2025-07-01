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
    
    public function getAllTutors($limit = 20, $offset = 0) {
        try {
            // If no parameters provided, return all tutors for form selection
            if ($limit === null && $offset === null) {
                $sql = "SELECT id, full_name, email, phone FROM users WHERE role = 'tutor' AND is_active = 1 ORDER BY full_name ASC";
                $result = $this->db->query($sql);
                if (!$result) {
                    error_log("Query failed: " . $this->db->error);
                    return [];
                }
                return $result->fetch_all(MYSQLI_ASSOC);
            }
            
            // Otherwise return paginated results with class count
            $sql = "SELECT u.id, u.username, u.full_name, u.email, u.phone, u.created_at,
                           COUNT(DISTINCT ct.class_id) as classes_assigned,
                           u.is_active
                    FROM users u
                    LEFT JOIN class_tutors ct ON u.id = ct.tutor_id AND ct.status = 'active'
                    WHERE u.role = 'tutor'
                    GROUP BY u.id
                    ORDER BY u.created_at DESC
                    LIMIT ? OFFSET ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ii", $limit, $offset);
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
                        class_level,
                        COUNT(*) as class_count,
                        COUNT(DISTINCT e.student_id) as student_count
                    FROM classes c
                    LEFT JOIN enrollments e ON c.id = e.class_id AND e.status = 'active'
                    WHERE c.is_active = 1
                    GROUP BY class_level
                    ORDER BY class_count DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();
            $distribution = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            // Calculate percentages
            $total_classes = array_sum(array_column($distribution, 'class_count'));
            
            foreach ($distribution as &$level) {
                $level['percentage'] = $total_classes > 0 ? 
                    round(($level['class_count'] / $total_classes) * 100, 1) : 0;
                $level['class_count'] = intval($level['class_count']);
                $level['student_count'] = intval($level['student_count']);
            }
            
            return $distribution;
            
        } catch (Exception $e) {
            error_log("Error getting class level distribution: " . $e->getMessage());
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
            error_log("Creating course with data: " . json_encode($data));
            
            $this->db->begin_transaction();
            
            $sql = "INSERT INTO classes (class_name, class_year, class_level, subject, description, 
                    max_students, sessions_total, price_per_session, schedule_time, schedule_duration, 
                    schedule_days, start_date, end_date, status, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW(), NOW())";
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                $this->db->rollback();
                return false;
            }
            
            $stmt->bind_param("sisssiiisisss",
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
                error_log("Execute failed: " . $stmt->error);
                $this->db->rollback();
                $stmt->close();
                return false;
            }
            
            $courseId = $this->db->insert_id;
            $stmt->close();
            
            // If tutor is assigned, create assignment
            if (!empty($data['tutor_id'])) {
                $sql = "INSERT INTO class_tutors (tutor_id, class_id, assigned_date, status, created_at) 
                        VALUES (?, ?, CURDATE(), 'active', NOW())";
                $stmt = $this->db->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("ii", $data['tutor_id'], $courseId);
                    $stmt->execute();
                    $stmt->close();
                }
            }
            
            $this->db->commit();
            error_log("Course created successfully with ID: " . $courseId);
            return $courseId;
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error creating course: " . $e->getMessage());
            return false;
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
}
?>