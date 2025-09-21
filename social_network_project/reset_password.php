<?php
session_start();
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/User.php';

// Only allow this script to be accessed via a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

$email = trim($_POST['email'] ?? '');
$newPassword = trim($_POST['new_password'] ?? '');

// --- Server-side Validation ---

// Check if fields are empty
if (empty($email) || empty($newPassword)) {
    // Set an error message in the session
    $_SESSION['reset_error'] = "Please fill in all the required fields.";
    // Redirect back to the login page
    header('Location: login.php');
    exit();
}

// Check for minimum password length
if (strlen($newPassword) < 6) {
    $_SESSION['reset_error'] = "The new password must be at least 6 characters long.";
    header('Location: login.php');
    exit();
}

// --- Attempt to Reset the Password ---
$db = new Database();
$conn = $db->getConnection();
$userModel = new User($conn);

$result = $userModel->resetPassword($email, $newPassword);

if ($result['success']) {
    // If successful, set a success message
    $_SESSION['reset_success'] = "Password reset successfully! You can now log in with your new password.";
} else {
    // If it fails, set the specific error message from the User class
    $_SESSION['reset_error'] = $result['error'] ?? 'An unknown error occurred during password reset.';
}

// CRITICAL: Redirect the user back to the login page to see the message
header('Location: login.php');
exit();
