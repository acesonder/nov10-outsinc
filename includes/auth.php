<?php
require_once 'db.php';

class Auth {
    
    public static function register($email, $password, $firstName, $lastName, $securityQuestion, $securityAnswer, $role = 'recipient') {
        $db = getDB();
        $conn = $db->getConnection();
        
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }
        
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            return ['success' => false, 'message' => 'Email already registered'];
        }
        
        // Hash password and security answer
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $answerHash = password_hash(strtolower(trim($securityAnswer)), PASSWORD_BCRYPT);
        
        // Insert user
        $stmt = $conn->prepare("INSERT INTO users (email, password_hash, first_name, last_name, role, security_question, security_answer_hash) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $email, $passwordHash, $firstName, $lastName, $role, $securityQuestion, $answerHash);
        
        if ($stmt->execute()) {
            $userId = $db->getLastInsertId();
            
            // Award "First Step" badge
            self::awardBadge($userId, 1);
            
            // Log action
            self::logAction($userId, 'user_registered', 'users', $userId);
            
            return ['success' => true, 'message' => 'Registration successful', 'user_id' => $userId];
        }
        
        return ['success' => false, 'message' => 'Registration failed'];
    }
    
    public static function login($email, $password) {
        $db = getDB();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("SELECT id, email, password_hash, role, first_name, last_name, is_active FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
        
        $user = $result->fetch_assoc();
        
        if (!$user['is_active']) {
            return ['success' => false, 'message' => 'Account is inactive'];
        }
        
        if (password_verify($password, $user['password_hash'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['last_name'] = $user['last_name'];
            
            // Update last login
            $conn->query("UPDATE users SET last_login = NOW() WHERE id = " . $user['id']);
            
            // Log action
            self::logAction($user['id'], 'user_login', 'users', $user['id']);
            
            return ['success' => true, 'message' => 'Login successful', 'user' => $user];
        }
        
        return ['success' => false, 'message' => 'Invalid email or password'];
    }
    
    public static function logout() {
        if (isset($_SESSION['user_id'])) {
            self::logAction($_SESSION['user_id'], 'user_logout', 'users', $_SESSION['user_id']);
        }
        session_destroy();
        return ['success' => true, 'message' => 'Logged out successfully'];
    }
    
    public static function resetPassword($email, $securityAnswer, $newPassword) {
        $db = getDB();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("SELECT id, security_answer_hash FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'Email not found'];
        }
        
        $user = $result->fetch_assoc();
        
        if (password_verify(strtolower(trim($securityAnswer)), $user['security_answer_hash'])) {
            $passwordHash = password_hash($newPassword, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->bind_param("si", $passwordHash, $user['id']);
            
            if ($stmt->execute()) {
                self::logAction($user['id'], 'password_reset', 'users', $user['id']);
                return ['success' => true, 'message' => 'Password reset successful'];
            }
        }
        
        return ['success' => false, 'message' => 'Security answer incorrect'];
    }
    
    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header('Location: /index.php');
            exit;
        }
    }
    
    public static function requireRole($roles) {
        self::requireLogin();
        if (!in_array($_SESSION['role'], (array)$roles)) {
            header('Location: /public/dashboard.php');
            exit;
        }
    }
    
    public static function getCurrentUser() {
        if (!self::isLoggedIn()) {
            return null;
        }
        
        $db = getDB();
        $conn = $db->getConnection();
        $userId = $_SESSION['user_id'];
        
        $result = $conn->query("SELECT * FROM users WHERE id = $userId");
        return $result->fetch_assoc();
    }
    
    public static function logAction($userId, $action, $entityType = null, $entityId = null, $details = null) {
        $db = getDB();
        $conn = $db->getConnection();
        
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        
        $stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, entity_type, entity_id, details, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $userId, $action, $entityType, $entityId, $details, $ipAddress, $userAgent);
        $stmt->execute();
    }
    
    public static function awardBadge($userId, $badgeId) {
        $db = getDB();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("INSERT IGNORE INTO user_badges (user_id, badge_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $userId, $badgeId);
        return $stmt->execute();
    }
}
?>
