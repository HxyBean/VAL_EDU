<?php
require_once(__DIR__ . '/Database.php');

abstract class BaseModel {
    protected $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    protected function query($sql) {
        try {
            $result = $this->db->query($sql);
            if ($result === false) {
                return array('code' => 1, 'error' => $this->db->error);
            }
            
            if ($result === true) {
                // For INSERT, UPDATE, DELETE operations
                return array('code' => 0, 'affected_rows' => $this->db->affected_rows);
            }
            
            // For SELECT operations
            $data = array();
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            $result->free();
            
            return array('code' => 0, 'data' => $data);
        } catch (Exception $e) {
            return array('code' => 1, 'error' => $e->getMessage());
        }
    }

    protected function queryPrepared($sql, $params) {
        try {
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                return array('code' => 1, 'error' => $this->db->error);
            }

            if (!empty($params)) {
                $types = array_shift($params);
                $stmt->bind_param($types, ...$params);
            }

            $stmt->execute();
            
            if ($stmt->error) {
                return array('code' => 1, 'error' => $stmt->error);
            }

            $result = $stmt->get_result();
            
            if ($result === false) {
                // INSERT, UPDATE, DELETE operations
                $affected_rows = $stmt->affected_rows;
                $insert_id = $this->db->insert_id;
                $stmt->close();
                return array('code' => 0, 'affected_rows' => $affected_rows, 'insert_id' => $insert_id);
            }

            // SELECT operations
            $data = array();
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            
            $stmt->close();
            return array('code' => 0, 'data' => $data);
            
        } catch (Exception $e) {
            return array('code' => 1, 'error' => $e->getMessage());
        }
    }

    protected function insert($table, $data) {
        $columns = implode(', ', array_keys($data));
        $placeholders = str_repeat('?,', count($data) - 1) . '?';
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        
        $types = '';
        $values = array();
        
        foreach ($data as $value) {
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
            $values[] = $value;
        }
        
        return $this->queryPrepared($sql, array_merge([$types], $values));
    }

    protected function update($table, $data, $where, $whereParams = []) {
        $setParts = array();
        foreach (array_keys($data) as $column) {
            $setParts[] = "{$column} = ?";
        }
        $setClause = implode(', ', $setParts);
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$where}";
        
        $types = '';
        $values = array();
        
        // Add data values
        foreach ($data as $value) {
            if (is_int($value)) {
                $types .= 'i';
            } elseif (is_float($value)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
            $values[] = $value;
        }
        
        // Add where parameters
        if (!empty($whereParams)) {
            $whereTypes = array_shift($whereParams);
            $types .= $whereTypes;
            $values = array_merge($values, $whereParams);
        }
        
        return $this->queryPrepared($sql, array_merge([$types], $values));
    }

    protected function delete($table, $where, $whereParams = []) {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        
        if (!empty($whereParams)) {
            return $this->queryPrepared($sql, $whereParams);
        } else {
            return $this->query($sql);
        }
    }
    protected function getUserByIdAndRole($user_id, $role) {
        try {
            $sql = "SELECT u.* 
                    FROM users u 
                    WHERE u.id = ? AND u.role = ?";
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return null;
            }
            
            $stmt->bind_param("is", $user_id, $role);
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                return null;
            }
            
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();
            
            return $user;
        } catch (Exception $e) {
            error_log("Error getting user data: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Change password for user with specific role
     * @param int $user_id
     * @param string $new_password
     * @param string $role
     * @return bool
     */
    protected function changeUserPassword($user_id, $new_password, $role) {
        try {
            error_log("Changing password for user ID: " . $user_id . " with role: " . $role);
            
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            error_log("New hashed password created");
            
            $sql = "UPDATE users SET password = ?, updated_at = NOW() WHERE id = ? AND role = ?";
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                return false;
            }
            
            $stmt->bind_param("sis", $hashed_password, $user_id, $role);
            
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                $stmt->close();
                return false;
            }
            
            $affected_rows = $stmt->affected_rows;
            $stmt->close();
            
            error_log("Password change affected rows: " . $affected_rows);
            
            if ($affected_rows > 0) {
                error_log("Password updated successfully");
                return true;
            } else {
                error_log("No rows were updated - user not found or role mismatch");
                return false;
            }
            
        } catch (Exception $e) {
            error_log("Error changing password: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user profile with specific role validation
     * @param int $user_id
     * @param array $data
     * @param string $role
     * @return bool
     */
    protected function updateUserProfile($user_id, $data, $role) {
        try {
            error_log("Updating profile for user ID: " . $user_id . " with role: " . $role);
            error_log("Update data: " . json_encode($data));
            
            $this->db->begin_transaction();
            
            // Build dynamic SQL based on provided data
            $setParts = [];
            $values = [];
            $types = '';
            
            $allowedFields = ['full_name', 'email', 'phone', 'address', 'bio', 'specialization', 'experience_years'];
            
            foreach ($data as $field => $value) {
                if (in_array($field, $allowedFields)) {
                    $setParts[] = "{$field} = ?";
                    $values[] = $value;
                    $types .= 's';
                }
            }
            
            if (empty($setParts)) {
                error_log("No valid fields to update");
                $this->db->rollback();
                return false;
            }
            
            $setClause = implode(', ', $setParts);
            $sql = "UPDATE users SET {$setClause}, updated_at = NOW() WHERE id = ? AND role = ?";
            
            $values[] = $user_id;
            $values[] = $role;
            $types .= 'is';
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                error_log("Prepare failed: " . $this->db->error);
                $this->db->rollback();
                return false;
            }
            
            $stmt->bind_param($types, ...$values);
            
            if (!$stmt->execute()) {
                error_log("Execute failed: " . $stmt->error);
                $this->db->rollback();
                $stmt->close();
                return false;
            }
            
            $affected_rows = $stmt->affected_rows;
            $stmt->close();
            
            error_log("Affected rows: " . $affected_rows);
            
            if ($affected_rows > 0) {
                $this->db->commit();
                error_log("Profile updated successfully");
                return true;
            } else {
                $this->db->rollback();
                error_log("No rows were updated");
                return false;
            }
            
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error updating user profile: " . $e->getMessage());
            return false;
        }
    }

}
?>