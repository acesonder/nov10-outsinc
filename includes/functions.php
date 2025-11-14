<?php
require_once 'db.php';

// Sanitize input
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// Format date
function formatDate($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

// Format datetime
function formatDateTime($datetime, $format = 'M d, Y g:i A') {
    return date($format, strtotime($datetime));
}

// Time ago
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . ' minutes ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return formatDateTime($datetime);
}

// Get user badge count
function getUserBadgeCount($userId) {
    $db = getDB();
    $conn = $db->getConnection();
    $result = $conn->query("SELECT COUNT(*) as count FROM user_badges WHERE user_id = $userId");
    $row = $result->fetch_assoc();
    return $row['count'];
}

// Get user badges
function getUserBadges($userId) {
    $db = getDB();
    $conn = $db->getConnection();
    $result = $conn->query("
        SELECT b.*, ub.earned_at 
        FROM badges b 
        JOIN user_badges ub ON b.id = ub.badge_id 
        WHERE ub.user_id = $userId 
        ORDER BY ub.earned_at DESC
    ");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Get unread notification count
function getUnreadNotificationCount($userId) {
    $db = getDB();
    $conn = $db->getConnection();
    $result = $conn->query("SELECT COUNT(*) as count FROM notifications WHERE user_id = $userId AND is_read = 0");
    $row = $result->fetch_assoc();
    return $row['count'];
}

// Get unread message count
function getUnreadMessageCount($userId) {
    $db = getDB();
    $conn = $db->getConnection();
    $result = $conn->query("SELECT COUNT(*) as count FROM messages WHERE recipient_id = $userId AND is_read = 0");
    $row = $result->fetch_assoc();
    return $row['count'];
}

// Get people helped count (closed cases)
function getPeopleHelpedCount() {
    $db = getDB();
    $conn = $db->getConnection();
    $result = $conn->query("SELECT COUNT(DISTINCT recipient_id) as count FROM cases WHERE status = 'closed'");
    $row = $result->fetch_assoc();
    return $row['count'];
}

// Create notification
function createNotification($userId, $type, $title, $message, $link = null) {
    $db = getDB();
    $conn = $db->getConnection();
    $stmt = $conn->prepare("INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $userId, $type, $title, $message, $link);
    return $stmt->execute();
}

// Upload file
function uploadFile($file, $directory = 'general') {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return ['success' => false, 'message' => 'No file uploaded'];
    }
    
    $uploadDir = UPLOAD_PATH . $directory . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $targetPath = $uploadDir . $filename;
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File too large'];
    }
    
    // Check file type
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'mp4', 'mov'];
    if (!in_array(strtolower($extension), $allowed)) {
        return ['success' => false, 'message' => 'File type not allowed'];
    }
    
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'filename' => $filename, 'path' => '/uploads/' . $directory . '/' . $filename];
    }
    
    return ['success' => false, 'message' => 'Upload failed'];
}

// Generate avatar
function getAvatarUrl($name, $size = 100) {
    $initials = '';
    $nameParts = explode(' ', $name);
    foreach ($nameParts as $part) {
        if (!empty($part)) {
            $initials .= strtoupper($part[0]);
        }
    }
    $initials = substr($initials, 0, 2);
    
    $colors = ['3498db', 'e74c3c', '2ecc71', 'f39c12', '9b59b6', '1abc9c', 'e67e22', '34495e'];
    $color = $colors[array_sum(array_map('ord', str_split($name))) % count($colors)];
    
    return "https://ui-avatars.com/api/?name=" . urlencode($initials) . "&size=$size&background=$color&color=fff&bold=true";
}

// Get role badge class
function getRoleBadgeClass($role) {
    $classes = [
        'admin' => 'badge-danger',
        'staff' => 'badge-warning',
        'volunteer' => 'badge-info',
        'recipient' => 'badge-secondary'
    ];
    return $classes[$role] ?? 'badge-secondary';
}

// Get case status badge class
function getCaseStatusBadgeClass($status) {
    $classes = [
        'open' => 'badge-primary',
        'in_progress' => 'badge-warning',
        'closed' => 'badge-success',
        'follow_up' => 'badge-info'
    ];
    return $classes[$status] ?? 'badge-secondary';
}

// Get priority badge class
function getPriorityBadgeClass($priority) {
    $classes = [
        'urgent' => 'badge-danger',
        'high' => 'badge-warning',
        'medium' => 'badge-info',
        'low' => 'badge-secondary'
    ];
    return $classes[$priority] ?? 'badge-secondary';
}

// JSON response
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>
