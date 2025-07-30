<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

class User {
    private $db;
    
    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }
    
    public function register($username, $email, $password, $fullName) {
        $hashedPassword = hashPassword($password);
        
        $stmt = $this->db->prepare("INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)");
        
        try {
            $stmt->execute([$username, $email, $hashedPassword, $fullName]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Constraint violation
                return false;
            }
            throw $e;
        }
    }
    
    public function login($username, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && verifyPassword($password, $user['password'])) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['is_admin'] = $user['is_admin'];
            $_SESSION['user'] = $user;
            return true;
        }
        
        return false;
    }
    
    public function logout() {
        // Unset all session variables
        session_unset();

        // Destroy the session data on the server
        session_destroy();

        // Expire the session cookie
        if (session_status() === PHP_SESSION_ACTIVE) {
            setcookie(session_name(), '', 0, '/');
        }
    }
    
    public function getById($id) {
        return $this->getUserById($id);
    }
    
    public function getUserById($id) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getAllUsers() {
        $stmt = $this->db->prepare("SELECT id, username, email, full_name, is_admin, created_at, updated_at FROM users ORDER BY full_name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function updateUser($id, $username, $email, $fullName) {
        $stmt = $this->db->prepare("UPDATE users SET username = ?, email = ?, full_name = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        
        try {
            $stmt->execute([$username, $email, $fullName, $id]);
            return true;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return false;
            }
            throw $e;
        }
    }
    
    public function updateProfile($id, $fullName, $email) {
        $stmt = $this->db->prepare("UPDATE users SET full_name = ?, email = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        
        try {
            $stmt->execute([$fullName, $email, $id]);
            return true;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return false;
            }
            throw $e;
        }
    }
    
    public function changePassword($id, $currentPassword, $newPassword) {
        $user = $this->getUserById($id);
        
        if (!$user || !verifyPassword($currentPassword, $user['password'])) {
            return false;
        }
        
        $hashedPassword = hashPassword($newPassword);
        $stmt = $this->db->prepare("UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$hashedPassword, $id]);
        
        return true;
    }
    
    public function deleteUser($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }
    
    public function setAdminStatus($id, $isAdmin) {
        $stmt = $this->db->prepare("UPDATE users SET is_admin = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$isAdmin ? 1 : 0, $id]);
        return $stmt->rowCount() > 0;
    }
    
    public function getByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function update($id, $data) {
        $fields = [];
        $values = [];
        
        foreach ($data as $field => $value) {
            $fields[] = "$field = ?";
            $values[] = $value;
        }
        
        $values[] = $id;
        
        $sql = "UPDATE users SET " . implode(', ', $fields) . ", updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        try {
            $stmt->execute($values);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return false;
            }
            throw $e;
        }
    }
}
?>
