<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        $result = Auth::login($email, $password);
        jsonResponse($result);
        break;
        
    case 'register':
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $firstName = $_POST['first_name'] ?? '';
        $lastName = $_POST['last_name'] ?? '';
        $securityQuestion = $_POST['security_question'] ?? '';
        $securityAnswer = $_POST['security_answer'] ?? '';
        $role = $_POST['role'] ?? 'recipient';
        
        $result = Auth::register($email, $password, $firstName, $lastName, $securityQuestion, $securityAnswer, $role);
        jsonResponse($result);
        break;
        
    case 'reset_password':
        $email = $_POST['email'] ?? '';
        $securityAnswer = $_POST['security_answer'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        
        $result = Auth::resetPassword($email, $securityAnswer, $newPassword);
        jsonResponse($result);
        break;
        
    case 'logout':
        Auth::logout();
        header('Location: /public/index.php');
        exit;
        break;
        
    default:
        jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}
?>
