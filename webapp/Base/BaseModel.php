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
}
?>