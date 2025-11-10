<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

Auth::requireLogin();

$action = $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];

switch ($action) {
    case 'list':
        $limit = intval($_GET['limit'] ?? 10);
        
        $db = getDB();
        $conn = $db->getConnection();
        
        $result = $conn->query("SELECT * FROM notifications WHERE user_id = $userId ORDER BY created_at DESC LIMIT $limit");
        $notifications = $result->fetch_all(MYSQLI_ASSOC);
        
        // Add time_ago to each notification
        foreach ($notifications as &$notif) {
            $notif['time_ago'] = timeAgo($notif['created_at']);
        }
        
        jsonResponse(['success' => true, 'notifications' => $notifications]);
        break;
        
    case 'mark_read':
        $notifId = intval($_POST['id'] ?? 0);
        
        $db = getDB();
        $conn = $db->getConnection();
        
        $conn->query("UPDATE notifications SET is_read = 1 WHERE id = $notifId AND user_id = $userId");
        
        jsonResponse(['success' => true, 'message' => 'Notification marked as read']);
        break;
        
    case 'mark_all_read':
        $db = getDB();
        $conn = $db->getConnection();
        
        $conn->query("UPDATE notifications SET is_read = 1 WHERE user_id = $userId");
        
        jsonResponse(['success' => true, 'message' => 'All notifications marked as read']);
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}
?>
