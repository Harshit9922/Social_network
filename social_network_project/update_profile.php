<?php
session_start();
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/User.php';

if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Redirect if not a POST request
    header('Location: user.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();
$userModel = new User($conn);

$user = $userModel->getByEmail($_SESSION['email']);
if (!$user) {
    header('Location: login.php');
    exit();
}
$user_id = $user['id'];

// --- Handle Text Fields Update ---
$fullname = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$intermediate = trim($_POST['intermediate'] ?? '');

$userModel->updateProfile($user_id, $fullname, $email, $intermediate);

// --- Handle Profile Picture Upload ---
if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === 0) {
    $allowed_extensions = ['png', 'jpg', 'jpeg', 'gif', 'webp'];
    $file_extension = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));

    if (in_array($file_extension, $allowed_extensions)) {
        $safe_filename = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '', basename($_FILES['profile_pic']['name']));
        $target_path = 'uploads/' . $safe_filename;

        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_path)) {
            $userModel->updateProfilePic($user_id, $target_path);
        }
    }
}

// Redirect back to the user page. The JS will handle UI updates,
// but this is a fallback for non-JS submissions or picture uploads.
header('Location: user.php');
exit();
