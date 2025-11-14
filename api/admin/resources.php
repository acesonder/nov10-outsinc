<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';

Auth::requireRole('admin');

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        $name = sanitize($_POST['name'] ?? '');
        $type = sanitize($_POST['type'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        $city = sanitize($_POST['city'] ?? '');
        $state = sanitize($_POST['state'] ?? '');
        $zip = sanitize($_POST['zip'] ?? '');
        $phone = sanitize($_POST['phone'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $website = sanitize($_POST['website'] ?? '');
        $hours = sanitize($_POST['hours'] ?? '');
        $accessibilityFeatures = sanitize($_POST['accessibility_features'] ?? '');
        
        if (empty($name) || empty($type)) {
            jsonResponse(['success' => false, 'message' => 'Name and type are required'], 400);
        }
        
        $db = getDB();
        $conn = $db->getConnection();
        $userId = $_SESSION['user_id'];
        
        $stmt = $conn->prepare("INSERT INTO resources (name, type, description, address, city, state, zip, phone, email, website, hours, accessibility_features, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssssssi", $name, $type, $description, $address, $city, $state, $zip, $phone, $email, $website, $hours, $accessibilityFeatures, $userId);
        
        if ($stmt->execute()) {
            $resourceId = $db->getLastInsertId();
            Auth::logAction($userId, 'resource_created', 'resources', $resourceId);
            jsonResponse(['success' => true, 'message' => 'Resource created successfully']);
        } else {
            jsonResponse(['success' => false, 'message' => 'Failed to create resource'], 500);
        }
        break;
        
    case 'delete':
        $id = intval($_POST['id'] ?? 0);
        
        if ($id === 0) {
            jsonResponse(['success' => false, 'message' => 'Invalid resource ID'], 400);
        }
        
        $db = getDB();
        $conn = $db->getConnection();
        $userId = $_SESSION['user_id'];
        
        // Soft delete by setting is_active to 0
        $conn->query("UPDATE resources SET is_active = 0 WHERE id = $id");
        
        Auth::logAction($userId, 'resource_deleted', 'resources', $id);
        
        jsonResponse(['success' => true, 'message' => 'Resource deleted successfully']);
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}
?>
