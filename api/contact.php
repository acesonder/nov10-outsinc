<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$name = sanitize($_POST['name'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$subject = sanitize($_POST['subject'] ?? '');
$message = sanitize($_POST['message'] ?? '');

if (empty($name) || empty($email) || empty($subject) || empty($message)) {
    jsonResponse(['success' => false, 'message' => 'All fields are required'], 400);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(['success' => false, 'message' => 'Invalid email address'], 400);
}

// Log the contact form submission
$db = getDB();
$conn = $db->getConnection();

$stmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, details) VALUES (?, 'contact_form_submitted', ?)");
$userId = Auth::isLoggedIn() ? $_SESSION['user_id'] : null;
$details = json_encode(['name' => $name, 'email' => $email, 'subject' => $subject]);
$stmt->bind_param("is", $userId, $details);
$stmt->execute();

// In a real application, you would send this via email
// For now, we'll just return success

jsonResponse(['success' => true, 'message' => 'Thank you for your message. We will get back to you soon!']);
?>
