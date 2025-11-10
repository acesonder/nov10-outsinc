<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

Auth::requireLogin();

$action = $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];

switch ($action) {
    case 'rsvp':
        $eventId = intval($_POST['event_id'] ?? 0);
        $status = sanitize($_POST['status'] ?? 'attending');
        
        if ($eventId === 0) {
            jsonResponse(['success' => false, 'message' => 'Invalid event'], 400);
        }
        
        $db = getDB();
        $conn = $db->getConnection();
        
        // Check if event exists and has space
        $event = $conn->query("SELECT * FROM events WHERE id = $eventId")->fetch_assoc();
        if (!$event) {
            jsonResponse(['success' => false, 'message' => 'Event not found'], 404);
        }
        
        if ($status === 'attending' && $event['max_participants']) {
            $attendees = $conn->query("SELECT COUNT(*) as count FROM event_rsvps WHERE event_id = $eventId AND status = 'attending'")->fetch_assoc()['count'];
            if ($attendees >= $event['max_participants']) {
                jsonResponse(['success' => false, 'message' => 'Event is full'], 400);
            }
        }
        
        // Insert or update RSVP
        $stmt = $conn->prepare("INSERT INTO event_rsvps (event_id, user_id, status) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE status = ?");
        $stmt->bind_param("iiss", $eventId, $userId, $status, $status);
        
        if ($stmt->execute()) {
            $message = $status === 'attending' ? 'RSVP confirmed!' : 'RSVP cancelled';
            
            if ($status === 'attending') {
                createNotification($userId, 'event', 'Event RSVP', 'You are registered for: ' . $event['title'], '/public/events.php');
                
                // Check for badge (10 events)
                $count = $conn->query("SELECT COUNT(*) as count FROM event_rsvps WHERE user_id = $userId AND status = 'attending'")->fetch_assoc()['count'];
                if ($count === 10) {
                    Auth::awardBadge($userId, 3); // Community Champion badge
                    createNotification($userId, 'badge', 'New Badge Earned!', 'You earned the Community Champion badge!', '/public/profile.php');
                }
            }
            
            Auth::logAction($userId, 'event_rsvp_' . $status, 'events', $eventId);
            
            jsonResponse(['success' => true, 'message' => $message]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Failed to update RSVP'], 500);
        }
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}
?>
