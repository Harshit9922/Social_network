<?php
session_start();
header('Content-Type: application/json'); // It's good practice to set the content type

// Require all necessary classes at the top
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Post.php';
require_once __DIR__ . '/classes/User.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit;
}

// Establish database connection
$db = new Database();
$conn = $db->getConnection();

// Get the logged-in user's ID
$userModel = new User($conn);
$user = $userModel->getByEmail($_SESSION['email']);
if (!$user) {
    echo json_encode(['success' => false, 'error' => 'User not found']);
    exit;
}
$user_id = $user['id'];

// Get the post ID from the POST request
$post_id = intval($_POST['post_id'] ?? 0);

// If post ID is valid, attempt to delete
if ($post_id > 0) {
    $postModel = new PostModel($conn);
    if ($postModel->delete($post_id, $user_id)) {
        // If deletion is successful
        echo json_encode(['success' => true]);
    } else {
        // If deletion fails (e.g., user doesn't own the post)
        echo json_encode(['success' => false, 'error' => 'Could not delete post']);
    }
} else {
    // If post_id is invalid
    echo json_encode(['success' => false, 'error' => 'Invalid post ID']);
}