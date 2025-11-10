<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

Auth::requireLogin();

$action = $_GET['action'] ?? '';
$userId = $_SESSION['user_id'];

switch ($action) {
    case 'toggle':
        $resourceId = intval($_POST['resource_id'] ?? 0);
        
        if ($resourceId === 0) {
            jsonResponse(['success' => false, 'message' => 'Invalid resource'], 400);
        }
        
        $db = getDB();
        $conn = $db->getConnection();
        
        // Check if already favorited
        $result = $conn->query("SELECT id FROM favorites WHERE user_id = $userId AND resource_id = $resourceId");
        
        if ($result->num_rows > 0) {
            // Remove favorite
            $conn->query("DELETE FROM favorites WHERE user_id = $userId AND resource_id = $resourceId");
            $message = 'Removed from favorites';
        } else {
            // Add favorite
            $stmt = $conn->prepare("INSERT INTO favorites (user_id, resource_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $userId, $resourceId);
            $stmt->execute();
            $message = 'Added to favorites';
            
            // Check for badge (10 favorites)
            $count = $conn->query("SELECT COUNT(*) as count FROM favorites WHERE user_id = $userId")->fetch_assoc()['count'];
            if ($count === 10) {
                Auth::awardBadge($userId, 4); // Resource Finder badge
                createNotification($userId, 'badge', 'New Badge Earned!', 'You earned the Resource Finder badge!', '/public/profile.php');
            }
        }
        
        Auth::logAction($userId, 'favorite_toggled', 'favorites', $resourceId);
        
        jsonResponse(['success' => true, 'message' => $message]);
        break;
        
    case 'list':
        $db = getDB();
        $conn = $db->getConnection();
        
        $result = $conn->query("
            SELECT r.* 
            FROM resources r
            JOIN favorites f ON r.id = f.resource_id
            WHERE f.user_id = $userId
            ORDER BY f.created_at DESC
        ");
        
        $favorites = $result->fetch_all(MYSQLI_ASSOC);
        
        jsonResponse(['success' => true, 'favorites' => $favorites]);
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}
?>
