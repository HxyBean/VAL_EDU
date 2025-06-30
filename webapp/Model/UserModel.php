<?php
require_once(__DIR__ . '/../Base/BaseModel.php');

class UserModel extends BaseModel {
    
    public function getAll() {
        $sql = "SELECT id, username, email, full_name, role, phone, is_active, created_at FROM users WHERE is_active = 1 ORDER BY role, full_name";
        return $this->query($sql);
    }

    public function getById($id) {
        $sql = "SELECT id, username, email, full_name, role, phone, address, birthdate, notes, created_at FROM users WHERE id = ? AND is_active = 1";
        return $this->queryPrepared($sql, ['i', $id]);
    }

    public function authenticate($username, $password) {
        // First check if user exists
        $sql = "SELECT id, username, email, password, full_name, role, is_active FROM users WHERE (username = ? OR email = ?)";
        $result = $this->queryPrepared($sql, ['ss', $username, $username]);
        
        if ($result['code'] !== 0) {
            return array('code' => 1, 'error' => 'Database error occurred');
        }
        
        if (empty($result['data'])) {
            return array('code' => 1, 'error' => 'Account not found. Please check your username or email.');
        }
        
        $user = $result['data'][0];
        
        // Check if account is active
        if (!$user['is_active']) {
            return array('code' => 1, 'error' => 'Your account has been deactivated. Please contact support.');
        }
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Remove password from returned data for security
            unset($user['password']);
            return array('code' => 0, 'data' => $user);
        } else {
            return array('code' => 1, 'error' => 'Incorrect password. Please try again.');
        }
    }

    public function createUser($userData) {
        // Check if username already exists
        $checkUsernameSql = "SELECT id FROM users WHERE username = ?";
        $usernameResult = $this->queryPrepared($checkUsernameSql, ['s', $userData['username']]);
        
        if ($usernameResult['code'] === 0 && !empty($usernameResult['data'])) {
            return array('code' => 1, 'error' => 'Username already exists. Please choose a different username.');
        }
        
        // Check if email already exists
        $checkEmailSql = "SELECT id FROM users WHERE email = ?";
        $emailResult = $this->queryPrepared($checkEmailSql, ['s', $userData['email']]);
        
        if ($emailResult['code'] === 0 && !empty($emailResult['data'])) {
            return array('code' => 1, 'error' => 'Email already exists. Please use a different email address.');
        }

        // Hash password
        $hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        // Prepare data for insertion
        $insertData = [
            'username' => $userData['username'],
            'email' => $userData['email'],
            'password' => $hashedPassword,
            'full_name' => $userData['full_name'],
            'role' => $userData['role'],
            'phone' => $userData['phone'] ?? null,
            'birthdate' => $userData['birthdate'] ?? null
        ];

        // Insert user
        $result = $this->insert('users', $insertData);
        
        if ($result['code'] === 0) {
            // Return user data without password
            return $this->getById($result['insert_id']);
        }
        
        return array('code' => 1, 'error' => 'Failed to create account. Please try again.');
    }

    public function updateLastLogin($userId) {
        $sql = "UPDATE users SET updated_at = NOW() WHERE id = ?";
        return $this->queryPrepared($sql, ['i', $userId]);
    }

    public function getUserByEmail($email) {
        $sql = "SELECT id, username, email, full_name, role FROM users WHERE email = ? AND is_active = 1";
        return $this->queryPrepared($sql, ['s', $email]);
    }

    public function getUsersByRole($role) {
        $sql = "SELECT id, username, email, full_name, phone, created_at FROM users WHERE role = ? AND is_active = 1 ORDER BY full_name";
        return $this->queryPrepared($sql, ['s', $role]);
    }
}
?>