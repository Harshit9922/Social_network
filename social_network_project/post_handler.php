<?php
session_start();
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Post.php';
require_once __DIR__ . '/classes/User.php';

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: user.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Get the logged-in user's ID
$userModel = new User($conn);
$user = $userModel->getByEmail($_SESSION['email']);
if (!$user) {
    header('Location: login.php');
    exit();
}
$user_id = $user['id'];

// Get the post content
$content = trim($_POST['content'] ?? '');

// Content is required, even if there's an image
if (empty($content)) {
    // You can add an error message here if you want
    header('Location: user.php');
    exit();
}

$image_path = null;

// --- Handle Image Upload ---
if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
    $upload_dir = __DIR__ . '/uploads/';
    
    // Create the uploads directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $file_name = $_FILES['image']['name'];
    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    if (in_array($file_extension, $allowed_extensions)) {
        // Create a unique, safe filename
        $safe_filename = time() . '_' . preg_replace('/[^A-Za-z0-9._-]/', '', $file_name);
        $target_file = $upload_dir . $safe_filename;

        // Move the file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            // Use a relative path to store in the database
            $image_path = 'uploads/' . $safe_filename;
        }
    }
}

// Create the post in the database
$postModel = new PostModel($conn);
$postModel->create($user_id, $content, $image_path);

// Redirect back to the user page to see the new post
header('Location: user.php');
exit();
