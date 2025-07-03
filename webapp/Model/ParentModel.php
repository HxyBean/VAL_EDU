<?php
require_once(__DIR__ . '/../Base/BaseModel.php');

class ParentModel extends BaseModel {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function getParentById($user_id) {
        return $this->getUserByIdAndRole($user_id, 'parent');
    }
    
    public function changePassword($user_id, $new_password) {
        return $this->changeUserPassword($user_id, $new_password, 'parent');
    }
    
    public function updateParentProfile($user_id, $data) {
        return $this->updateUserProfile($user_id, $data, 'parent');
    }
    
    public function getParentChildren($parent_id) {
        try {
            $sql = "SELECT u.id, u.full_name, u.email, u.phone, u.created_at as registration_date,
                           ps.relationship_type, ps.is_primary,
                           COUNT(DISTINCT e.class_id) as enrolled_classes,
                           COUNT(DISTINCT p.id) as payment_count,
                           COALESCE(SUM(p.final_amount), 0) as total_paid
                    FROM parent_student ps
                    INNER JOIN users u ON ps.student_id = u.id
                    LEFT JOIN enrollments e ON u.id = e.student_id AND e.status = 'active'
                    LEFT JOIN payments p ON u.id = p.student_id AND p.status = 'completed'
                    WHERE ps.parent_id = ? AND u.role = 'student' AND u.is_active = 1
                    GROUP BY u.id, ps.relationship_type, ps.is_primary
                    ORDER BY ps.is_primary DESC, u.full_name ASC";
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return [];
            }
            
            $stmt->bind_param("i", $parent_id);
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                return [];
            }
            
            $result = $stmt->get_result();
            $children = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            // Get detailed information for each child
            foreach ($children as &$child) {
                $child['classes'] = $this->getChildClasses($child['id']);
                $child['recent_attendance'] = $this->getChildRecentAttendance($child['id']);
                $child['academic_progress'] = $this->getChildAcademicProgress($child['id']);
            }
            
            return $children;
        } catch (Exception $e) {
            error_log("Error getting parent children: " . $e->getMessage());
            return [];
        }
    }
    
    public function getChildClasses($student_id) {
        try {
            $sql = "SELECT c.*, e.enrollment_date, e.status as enrollment_status,
                           u.full_name as tutor_name, u.email as tutor_email,
                           (SELECT COUNT(*) FROM sessions s WHERE s.class_id = c.id AND s.status = 'completed') as sessions_completed,
                           (SELECT COUNT(*) FROM attendance a 
                            INNER JOIN sessions s ON a.session_id = s.id 
                            WHERE s.class_id = c.id AND a.student_id = ? AND a.status = 'present') as sessions_attended
                    FROM classes c 
                    INNER JOIN enrollments e ON c.id = e.class_id 
                    LEFT JOIN class_tutors ct ON c.id = ct.class_id AND ct.status = 'active'
                    LEFT JOIN users u ON ct.tutor_id = u.id
                    WHERE e.student_id = ? AND e.status = 'active'
                    ORDER BY c.start_date DESC";
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return [];
            }
            
            $stmt->bind_param("ii", $student_id, $student_id);
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                return [];
            }
            
            $result = $stmt->get_result();
            $classes = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            return $classes;
        } catch (Exception $e) {
            error_log("Error getting child classes: " . $e->getMessage());
            return [];
        }
    }
    
    public function getChildAttendance($student_id, $class_id = null) {
        try {
            $sql = "SELECT a.*, s.session_date, s.session_number, s.topic, c.class_name
                    FROM attendance a
                    INNER JOIN sessions s ON a.session_id = s.id
                    INNER JOIN classes c ON s.class_id = c.id
                    WHERE a.student_id = ?";
            
            $params = [$student_id];
            $types = "i";
            
            if ($class_id) {
                $sql .= " AND s.class_id = ?";
                $params[] = $class_id;
                $types .= "i";
            }
            
            $sql .= " ORDER BY s.session_date DESC";
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return [];
            }
            
            $stmt->bind_param($types, ...$params);
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                return [];
            }
            
            $result = $stmt->get_result();
            $attendance = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            return $attendance;
        } catch (Exception $e) {
            error_log("Error getting child attendance: " . $e->getMessage());
            return [];
        }
    }
    
    public function getParentStats($parent_id) {
        try {
            $sql = "SELECT 
                        COUNT(DISTINCT ps.student_id) as total_children,
                        COUNT(DISTINCT e.class_id) as total_classes,
                        COUNT(DISTINCT s.id) as total_sessions,
                        COUNT(DISTINCT CASE WHEN a.status = 'present' THEN a.id END) as attended_sessions,
                        COUNT(DISTINCT p.id) as total_payments,
                        COALESCE(SUM(p.final_amount), 0) as total_paid,
                        COALESCE(AVG(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) * 100, 0) as average_attendance_rate
                    FROM parent_student ps
                    LEFT JOIN enrollments e ON ps.student_id = e.student_id AND e.status = 'active'
                    LEFT JOIN sessions s ON e.class_id = s.class_id AND s.status = 'completed'
                    LEFT JOIN attendance a ON s.id = a.session_id AND a.student_id = ps.student_id
                    LEFT JOIN payments p ON ps.student_id = p.student_id AND p.status = 'completed'
                    WHERE ps.parent_id = ?
                    GROUP BY ps.parent_id";
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return [];
            }
            
            $stmt->bind_param("i", $parent_id);
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                return [];
            }
            
            $result = $stmt->get_result();
            $stats = $result->fetch_assoc();
            $stmt->close();
            
            // Nếu không có dữ liệu, trả về giá trị mặc định
            if (!$stats) {
                $stats = [
                    'total_children' => 0,
                    'total_classes' => 0,
                    'total_sessions' => 0,
                    'attended_sessions' => 0,
                    'total_payments' => 0,
                    'total_paid' => 0,
                    'average_attendance_rate' => 0
                ];
            }
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error getting parent stats: " . $e->getMessage());
            return [
                'total_children' => 0,
                'total_classes' => 0,
                'total_sessions' => 0,
                'attended_sessions' => 0,
                'total_payments' => 0,
                'total_paid' => 0,
                'average_attendance_rate' => 0
            ];
        }
    }
    
    public function getParentNotifications($parent_id, $limit = 10) {
        try {
            // Tạo thông báo từ các hoạt động của con
            $sql = "SELECT 
                        'attendance' as type,
                        CONCAT('Con ', u.full_name, ' đã ', 
                               CASE WHEN a.status = 'present' THEN 'có mặt' 
                                    WHEN a.status = 'absent' THEN 'vắng mặt' 
                                    ELSE 'đi muộn' END, 
                               ' trong buổi học ', c.class_name) as message,
                        s.session_date as created_at,
                        u.full_name as student_name,
                        c.class_name
                    FROM parent_student ps
                    INNER JOIN users u ON ps.student_id = u.id
                    INNER JOIN enrollments e ON u.id = e.student_id
                    INNER JOIN sessions s ON e.class_id = s.class_id
                    INNER JOIN attendance a ON s.id = a.session_id AND a.student_id = u.id
                    INNER JOIN classes c ON s.class_id = c.id
                    WHERE ps.parent_id = ? AND s.session_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    
                    UNION ALL
                    
                    SELECT 
                        'payment' as type,
                        CONCAT('Đã thanh toán ', FORMAT(p.final_amount, 0), 'đ cho lớp ', c.class_name, ' của con ', u.full_name) as message,
                        p.payment_date as created_at,
                        u.full_name as student_name,
                        c.class_name
                    FROM parent_student ps
                    INNER JOIN users u ON ps.student_id = u.id
                    INNER JOIN payments p ON u.id = p.student_id
                    INNER JOIN classes c ON p.class_id = c.id
                    WHERE ps.parent_id = ? AND p.payment_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    
                    ORDER BY created_at DESC
                    LIMIT ?";
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return [];
            }
            
            $stmt->bind_param("iii", $parent_id, $parent_id, $limit);
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                return [];
            }
            
            $result = $stmt->get_result();
            $notifications = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            return $notifications;
        } catch (Exception $e) {
            error_log("Error getting parent notifications: " . $e->getMessage());
            return [];
        }
    }
    
    public function getChildRecentAttendance($student_id, $limit = 5) {
        try {
            $sql = "SELECT a.*, s.session_date, s.topic, c.class_name
                    FROM attendance a
                    INNER JOIN sessions s ON a.session_id = s.id
                    INNER JOIN classes c ON s.class_id = c.id
                    WHERE a.student_id = ?
                    ORDER BY s.session_date DESC
                    LIMIT ?";
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return [];
            }
            
            $stmt->bind_param("ii", $student_id, $limit);
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                return [];
            }
            
            $result = $stmt->get_result();
            $attendance = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            return $attendance;
        } catch (Exception $e) {
            error_log("Error getting child recent attendance: " . $e->getMessage());
            return [];
        }
    }
    
    public function getChildAcademicProgress($student_id) {
        try {
            $sql = "SELECT 
                        COUNT(DISTINCT e.class_id) as enrolled_classes,
                        COUNT(DISTINCT s.id) as total_sessions,
                        COUNT(DISTINCT CASE WHEN a.status = 'present' THEN a.id END) as attended_sessions,
                        COUNT(DISTINCT CASE WHEN a.status = 'absent' THEN a.id END) as absent_sessions,
                        COALESCE(COUNT(DISTINCT CASE WHEN a.status = 'present' THEN a.id END) * 100.0 / NULLIF(COUNT(DISTINCT s.id), 0), 0) as attendance_rate
                    FROM enrollments e
                    INNER JOIN sessions s ON e.class_id = s.class_id AND s.status = 'completed'
                    LEFT JOIN attendance a ON s.id = a.session_id AND a.student_id = e.student_id
                    WHERE e.student_id = ? AND e.status = 'active'";
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return [];
            }
            
            $stmt->bind_param("i", $student_id);
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                return [];
            }
            
            $result = $stmt->get_result();
            $progress = $result->fetch_assoc();
            $stmt->close();
            
            return $progress ?: [
                'enrolled_classes' => 0,
                'total_sessions' => 0,
                'attended_sessions' => 0,
                'absent_sessions' => 0,
                'attendance_rate' => 0
            ];
        } catch (Exception $e) {
            error_log("Error getting child academic progress: " . $e->getMessage());
            return [
                'enrolled_classes' => 0,
                'total_sessions' => 0,
                'attended_sessions' => 0,
                'absent_sessions' => 0,
                'attendance_rate' => 0
            ];
        }
    }
    
    public function getChildrenPayments($parent_id) {
        try {
            $sql = "SELECT p.*, c.class_name, c.subject, u.full_name as student_name
                    FROM parent_student ps
                    INNER JOIN users u ON ps.student_id = u.id
                    INNER JOIN payments p ON u.id = p.student_id
                    INNER JOIN classes c ON p.class_id = c.id
                    WHERE ps.parent_id = ?
                    ORDER BY p.payment_date DESC
                    LIMIT 20";
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return [];
            }
            
            $stmt->bind_param("i", $parent_id);
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                return [];
            }
            
            $result = $stmt->get_result();
            $payments = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            return $payments;
        } catch (Exception $e) {
            error_log("Error getting children payments: " . $e->getMessage());
            return [];
        }
    }
    
    public function getChildDetails($childId, $parentId) {
        try {
            // Verify parent-child relationship
            $sql = "SELECT ps.*, u.id, u.full_name, u.email, u.phone, u.created_at as registration_date,
                       u.birthdate, u.address, u.is_active
                FROM parent_student ps
                INNER JOIN users u ON ps.student_id = u.id
                WHERE ps.parent_id = ? AND ps.student_id = ? AND u.role = 'student'";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return null;
        }
        
        $stmt->bind_param("ii", $parentId, $childId);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return null;
        }
        
        $result = $stmt->get_result();
        $child = $result->fetch_assoc();
        $stmt->close();
        
        if (!$child) {
            return null; // Parent doesn't have access to this child
        }
        
        // Get child's classes with detailed information
        $child['classes'] = $this->getChildClassesDetailed($childId);
        
        // Get child's attendance history
        $child['attendance_history'] = $this->getChildAttendanceHistory($childId);
        
        // Get child's academic progress
        $child['academic_progress'] = $this->getChildAcademicProgress($childId);
        
        // Get child's payment history
        $child['payment_history'] = $this->getChildPaymentHistory($childId);
        
        return $child;
        
    } catch (Exception $e) {
        error_log("Error getting child details: " . $e->getMessage());
        return null;
    }
}

public function getChildClassesDetailed($studentId) {
    try {
        $sql = "SELECT c.*, e.enrollment_date, e.status as enrollment_status,
                       u.full_name as tutor_name, u.email as tutor_email, u.phone as tutor_phone,
                       (SELECT COUNT(*) FROM sessions s WHERE s.class_id = c.id AND s.status = 'completed') as total_sessions,
                       (SELECT COUNT(*) FROM attendance a 
                        INNER JOIN sessions s ON a.session_id = s.id 
                        WHERE s.class_id = c.id AND a.student_id = ? AND a.status = 'present') as attended_sessions,
                       (SELECT COUNT(*) FROM attendance a 
                        INNER JOIN sessions s ON a.session_id = s.id 
                        WHERE s.class_id = c.id AND a.student_id = ? AND a.status = 'absent') as absent_sessions,
                       CASE 
                           WHEN (SELECT COUNT(*) FROM sessions s WHERE s.class_id = c.id AND s.status = 'completed') > 0 
                           THEN ROUND(
                               (SELECT COUNT(*) FROM attendance a INNER JOIN sessions s ON a.session_id = s.id 
                                WHERE s.class_id = c.id AND a.student_id = ? AND a.status = 'present') * 100.0 / 
                               (SELECT COUNT(*) FROM sessions s WHERE s.class_id = c.id AND s.status = 'completed'), 1
                           )
                           ELSE 0 
                       END as attendance_rate
                FROM classes c 
                INNER JOIN enrollments e ON c.id = e.class_id 
                LEFT JOIN class_tutors ct ON c.id = ct.class_id AND ct.status = 'active'
                LEFT JOIN users u ON ct.tutor_id = u.id
                WHERE e.student_id = ? AND e.status = 'active'
                ORDER BY c.start_date DESC";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return [];
        }
        
        $stmt->bind_param("iiii", $studentId, $studentId, $studentId, $studentId);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return [];
        }
        
        $result = $stmt->get_result();
        $classes = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $classes;
    } catch (Exception $e) {
        error_log("Error getting child classes detailed: " . $e->getMessage());
        return [];
    }
}

public function getChildAttendanceHistory($studentId, $limit = 50) {
    try {
        $sql = "SELECT a.*, s.session_date, s.topic, s.session_time, s.duration_minutes,
                       c.class_name, c.subject, c.class_level,
                       u.full_name as tutor_name
                FROM attendance a
                INNER JOIN sessions s ON a.session_id = s.id
                INNER JOIN classes c ON s.class_id = c.id
                LEFT JOIN class_tutors ct ON c.id = ct.class_id AND ct.status = 'active'
                LEFT JOIN users u ON ct.tutor_id = u.id
                WHERE a.student_id = ?
                ORDER BY s.session_date DESC, s.session_time DESC
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return [];
        }
        
        $stmt->bind_param("ii", $studentId, $limit);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return [];
        }
        
        $result = $stmt->get_result();
        $attendance = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $attendance;
    } catch (Exception $e) {
        error_log("Error getting child attendance history: " . $e->getMessage());
        return [];
    }
}

public function getChildPaymentHistory($studentId) {
    try {
        $sql = "SELECT p.*, c.class_name, c.subject
                FROM payments p
                INNER JOIN classes c ON p.class_id = c.id
                WHERE p.student_id = ?
                ORDER BY p.payment_date DESC";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return [];
        }
        
        $stmt->bind_param("i", $studentId);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return [];
        }
        
        $result = $stmt->get_result();
        $payments = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $payments;
    } catch (Exception $e) {
        error_log("Error getting child payment history: " . $e->getMessage());
        return [];
    }
}

public function getChildAttendanceByClass($studentId, $classId) {
    try {
        $sql = "SELECT a.*, s.session_date, s.topic, s.session_time, s.duration_minutes
                FROM attendance a
                INNER JOIN sessions s ON a.session_id = s.id
                WHERE a.student_id = ? AND s.class_id = ?
                ORDER BY s.session_date DESC";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("Prepare failed: " . $this->db->error);
            return [];
        }
        
        $stmt->bind_param("ii", $studentId, $classId);
        if (!$stmt->execute()) {
            error_log("Execute failed: " . $stmt->error);
            return [];
        }
        
        $result = $stmt->get_result();
        $attendance = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $attendance;
    } catch (Exception $e) {
        error_log("Error getting child attendance by class: " . $e->getMessage());
        return [];
    }
}
}
?>