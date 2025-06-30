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
            $sql = "SELECT s.*, u.full_name, u.email, u.phone, u.created_at as registration_date,
                           COUNT(DISTINCT e.class_id) as enrolled_classes,
                           COUNT(DISTINCT p.id) as payment_count
                    FROM student_parent_relations spr
                    INNER JOIN users s ON spr.student_id = s.id
                    LEFT JOIN users u ON s.id = u.id
                    LEFT JOIN enrollments e ON s.id = e.student_id AND e.status = 'active'
                    LEFT JOIN payments p ON s.id = p.student_id AND p.status = 'completed'
                    WHERE spr.parent_id = ? AND spr.status = 'active'
                    GROUP BY s.id
                    ORDER BY u.full_name ASC";
            
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
                           (SELECT COUNT(*) FROM sessions s WHERE s.class_id = c.id AND s.status = 'completed') as completed_sessions,
                           (SELECT COUNT(*) FROM sessions s WHERE s.class_id = c.id) as total_sessions
                    FROM enrollments e
                    INNER JOIN classes c ON e.class_id = c.id
                    LEFT JOIN class_tutors ct ON c.id = ct.class_id AND ct.status = 'active'
                    LEFT JOIN users u ON ct.tutor_id = u.id
                    WHERE e.student_id = ? AND e.status = 'active'
                    ORDER BY c.class_name ASC";
            
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
                        COUNT(DISTINCT CASE WHEN a.status = 'present' THEN a.id END) as present_count,
                        COUNT(DISTINCT a.id) as total_attendance_records,
                        AVG(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) * 100 as attendance_rate
                    FROM enrollments e
                    LEFT JOIN classes c ON e.class_id = c.id
                    LEFT JOIN sessions s ON c.id = s.class_id
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
            
            return $progress;
        } catch (Exception $e) {
            error_log("Error getting child academic progress: " . $e->getMessage());
            return [];
        }
    }
    
    public function getChildrenPayments($parent_id) {
        try {
            $sql = "SELECT p.*, u.full_name as student_name, c.class_name
                    FROM payments p
                    INNER JOIN student_parent_relations spr ON p.student_id = spr.student_id
                    INNER JOIN users u ON p.student_id = u.id
                    LEFT JOIN classes c ON p.class_id = c.id
                    WHERE spr.parent_id = ? AND spr.status = 'active'
                    ORDER BY p.payment_date DESC";
            
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
    
    public function getChildPayments($student_id) {
        try {
            $sql = "SELECT p.*, c.class_name
                    FROM payments p
                    LEFT JOIN classes c ON p.class_id = c.id
                    WHERE p.student_id = ?
                    ORDER BY p.payment_date DESC";
            
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
            $payments = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            return $payments;
        } catch (Exception $e) {
            error_log("Error getting child payments: " . $e->getMessage());
            return [];
        }
    }
    
    public function getParentStats($parent_id) {
        try {
            $sql = "SELECT 
                        COUNT(DISTINCT spr.student_id) as total_children,
                        COUNT(DISTINCT e.class_id) as total_enrollments,
                        SUM(CASE WHEN p.status = 'completed' THEN p.amount ELSE 0 END) as total_paid,
                        SUM(CASE WHEN p.status = 'pending' THEN p.amount ELSE 0 END) as pending_payments,
                        AVG(prog.attendance_rate) as avg_attendance_rate
                    FROM student_parent_relations spr
                    LEFT JOIN enrollments e ON spr.student_id = e.student_id AND e.status = 'active'
                    LEFT JOIN payments p ON spr.student_id = p.student_id
                    LEFT JOIN (
                        SELECT 
                            e.student_id,
                            AVG(CASE WHEN a.status = 'present' THEN 1 ELSE 0 END) * 100 as attendance_rate
                        FROM enrollments e
                        LEFT JOIN classes c ON e.class_id = c.id
                        LEFT JOIN sessions s ON c.id = s.class_id
                        LEFT JOIN attendance a ON s.id = a.session_id AND a.student_id = e.student_id
                        WHERE e.status = 'active'
                        GROUP BY e.student_id
                    ) prog ON spr.student_id = prog.student_id
                    WHERE spr.parent_id = ? AND spr.status = 'active'";
            
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
            
            return $stats;
        } catch (Exception $e) {
            error_log("Error getting parent stats: " . $e->getMessage());
            return [];
        }
    }
    
    public function getParentNotifications($parent_id, $limit = 10) {
        try {
            $sql = "SELECT n.*, u.full_name as student_name
                    FROM notifications n
                    INNER JOIN student_parent_relations spr ON n.student_id = spr.student_id
                    LEFT JOIN users u ON n.student_id = u.id
                    WHERE spr.parent_id = ? AND spr.status = 'active'
                    ORDER BY n.created_at DESC
                    LIMIT ?";
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return [];
            }
            
            $stmt->bind_param("ii", $parent_id, $limit);
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
}
?>