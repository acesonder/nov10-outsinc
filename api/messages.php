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
        
        $result = $conn->query("
            SELECT m.*, u.first_name as sender_first_name, u.last_name as sender_last_name
            FROM messages m
            JOIN users u ON m.sender_id = u.id
            WHERE m.recipient_id = $userId
            ORDER BY m.created_at DESC
            LIMIT $limit
        ");
        
        $messages = $result->fetch_all(MYSQLI_ASSOC);
        
        // Add formatted data
        foreach ($messages as &$msg) {
            $msg['sender_name'] = $msg['sender_first_name'] . ' ' . $msg['sender_last_name'];
            $msg['time_ago'] = timeAgo($msg['created_at']);
        }
        
        jsonResponse(['success' => true, 'messages' => $messages]);
        break;
        
    case 'send':
        $recipientId = intval($_POST['recipient_id'] ?? 0);
        $message = sanitize($_POST['message'] ?? '');
        
        if (empty($message) || $recipientId === 0) {
            jsonResponse(['success' => false, 'message' => 'Invalid message data'], 400);
        }
        
        $db = getDB();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, recipient_id, message) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $userId, $recipientId, $message);
        
        if ($stmt->execute()) {
            // Create notification for recipient
            createNotification($recipientId, 'message', 'New Message', 'You have a new message', '/public/messages.php');
            
            Auth::logAction($userId, 'message_sent', 'messages', $db->getLastInsertId());
            
            jsonResponse(['success' => true, 'message' => 'Message sent']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Failed to send message'], 500);
        }
        break;
        
    case 'mark_read':
        $messageId = intval($_POST['id'] ?? 0);
        
        $db = getDB();
        $conn = $db->getConnection();
        
        $conn->query("UPDATE messages SET is_read = 1 WHERE id = $messageId AND recipient_id = $userId");
        
        jsonResponse(['success' => true, 'message' => 'Message marked as read']);
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}
?>
