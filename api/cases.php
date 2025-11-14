<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'create':
        $title = sanitize($_POST['title'] ?? '');
        $type = sanitize($_POST['type'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $priority = sanitize($_POST['priority'] ?? 'medium');
        $isAnonymous = isset($_POST['is_anonymous']) ? 1 : 0;
        
        $recipientId = null;
        if (Auth::isLoggedIn() && !$isAnonymous) {
            $recipientId = $_SESSION['user_id'];
        }
        
        $db = getDB();
        $conn = $db->getConnection();
        
        $stmt = $conn->prepare("INSERT INTO cases (recipient_id, title, description, type, priority, is_anonymous) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssi", $recipientId, $title, $description, $type, $priority, $isAnonymous);
        
        if ($stmt->execute()) {
            $caseId = $db->getLastInsertId();
            
            if ($recipientId) {
                Auth::logAction($recipientId, 'case_created', 'cases', $caseId);
                createNotification($recipientId, 'case', 'Help Request Submitted', 'Your request has been submitted and will be reviewed soon.', '/public/case.php?id=' . $caseId);
            }
            
            jsonResponse(['success' => true, 'message' => 'Help request submitted successfully', 'case_id' => $caseId]);
        } else {
            jsonResponse(['success' => false, 'message' => 'Failed to submit request'], 500);
        }
        break;
        
    case 'update_status':
        Auth::requireRole(['staff', 'admin', 'volunteer']);
        
        $caseId = intval($_POST['case_id'] ?? 0);
        $status = sanitize($_POST['status'] ?? '');
        $note = sanitize($_POST['note'] ?? '');
        
        $db = getDB();
        $conn = $db->getConnection();
        
        // Get case details
        $result = $conn->query("SELECT recipient_id FROM cases WHERE id = $caseId");
        if ($result->num_rows === 0) {
            jsonResponse(['success' => false, 'message' => 'Case not found'], 404);
        }
        
        $case = $result->fetch_assoc();
        
        // Update case status
        $closedAt = $status === 'closed' ? 'NOW()' : 'NULL';
        $conn->query("UPDATE cases SET status = '$status', updated_at = NOW(), closed_at = $closedAt WHERE id = $caseId");
        
        // Add note if provided
        if (!empty($note)) {
            $userId = $_SESSION['user_id'];
            $stmt = $conn->prepare("INSERT INTO case_notes (case_id, user_id, note) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $caseId, $userId, $note);
            $stmt->execute();
        }
        
        // Notify recipient
        if ($case['recipient_id']) {
            $message = "Your case status has been updated to: " . ucfirst(str_replace('_', ' ', $status));
            createNotification($case['recipient_id'], 'case_update', 'Case Updated', $message, '/public/case.php?id=' . $caseId);
        }
        
        Auth::logAction($_SESSION['user_id'], 'case_updated', 'cases', $caseId);
        
        jsonResponse(['success' => true, 'message' => 'Case updated successfully']);
        break;
        
    case 'list':
        Auth::requireLogin();
        
        $db = getDB();
        $conn = $db->getConnection();
        
        $userId = $_SESSION['user_id'];
        $role = $_SESSION['role'];
        
        // Build query based on role
        if ($role === 'recipient') {
            $query = "SELECT * FROM cases WHERE recipient_id = $userId ORDER BY created_at DESC";
        } else {
            $query = "SELECT c.*, u.first_name, u.last_name FROM cases c 
                      LEFT JOIN users u ON c.recipient_id = u.id 
                      ORDER BY c.created_at DESC";
        }
        
        $result = $conn->query($query);
        $cases = $result->fetch_all(MYSQLI_ASSOC);
        
        jsonResponse(['success' => true, 'cases' => $cases]);
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}
?>
