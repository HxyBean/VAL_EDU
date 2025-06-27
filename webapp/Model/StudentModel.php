<?php

require_once(__DIR__ . '/../Base/BaseModel.php');

class StudentModel extends BaseModel {
    // Lấy thông tin cá nhân học sinh
    public function getStudentInfo($studentId) {
        $sql = "SELECT id, username, email, full_name, phone, birthdate FROM users WHERE id = ? AND role = 'student'";
        $result = $this->queryPrepared($sql, ['i', $studentId]);
        return $result['data'][0] ?? [];
    }

    // Lấy danh sách lớp học mà học sinh đang theo học
    public function getStudentClasses($studentId) {
        $sql = "SELECT c.*, e.sessions_attended, e.status as enrollment_status
                FROM enrollments e
                JOIN classes c ON e.class_id = c.id
                WHERE e.student_id = ?";
        $result = $this->queryPrepared($sql, ['i', $studentId]);
        return $result['data'] ?? [];
    }
}
?>