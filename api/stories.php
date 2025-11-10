<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'submit':
        Auth::requireLogin();
        
        $title = sanitize($_POST['title'] ?? '');
        $story = sanitize($_POST['story'] ?? '');
        $anonymous = isset($_POST['anonymous']) ? 1 : 0;
        
        if (empty($title) || empty($story)) {
            jsonResponse(['success' => false, 'message' => 'Title and story are required'], 400);
        }
        
        $authorId = $anonymous ? null : $_SESSION['user_id'];
        $mediaType = 'none';
        $mediaUrl = null;
        
        // Handle file upload
        if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
            $upload = uploadFile($_FILES['media'], 'stories');
            if ($upload['success']) {
                $mediaUrl = $upload['path'];
                $extension = strtolower(pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION));
                $mediaType = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']) ? 'image' : 'video';
            }
        }
        
        $db = getDB();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("INSERT INTO success_stories (title, story, author_id, media_type, media_url, is_published) VALUES (?, ?, ?, ?, ?, 0)");
        $stmt->bind_param("ssiss", $title, $story, $authorId, $mediaType, $mediaUrl);
        
        if ($stmt->execute()) {
            $storyId = $db->getLastInsertId();
            
            if ($authorId) {
                Auth::logAction($authorId, 'story_submitted', 'success_stories', $storyId);
                createNotification($authorId, 'story', 'Story Submitted', 'Your story has been submitted for review.', '/public/stories.php');
                
                // Award "Story Teller" badge
                Auth::awardBadge($authorId, 5);
            }
            
            jsonResponse(['success' => true, 'message' => 'Thank you for sharing your story! It will be reviewed before publishing.']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Failed to submit story'], 500);
        }
        break;
        
    case 'view':
        $storyId = intval($_GET['id'] ?? 0);
        
        if ($storyId === 0) {
            jsonResponse(['success' => false, 'message' => 'Invalid story ID'], 400);
        }
        
        $db = getDB();
        $conn = $db->getConnection();
        
        // Increment view count
        $conn->query("UPDATE success_stories SET views = views + 1 WHERE id = $storyId");
        
        // Get story
        $result = $conn->query("
            SELECT s.*, u.first_name, u.last_name 
            FROM success_stories s 
            LEFT JOIN users u ON s.author_id = u.id 
            WHERE s.id = $storyId AND s.is_published = 1
        ");
        
        if ($result->num_rows === 0) {
            jsonResponse(['success' => false, 'message' => 'Story not found'], 404);
        }
        
        $story = $result->fetch_assoc();
        jsonResponse(['success' => true, 'story' => $story]);
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}
?>
