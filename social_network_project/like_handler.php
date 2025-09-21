<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/LikeModel.php';

// Security: Ensure a user is logged in
if (!isset($_SESSION['email'])) {
    echo json_encode(['success' => false, 'error' => 'not_logged_in']);
    exit();
}

$db = new Database();
$conn = $db->getConnection();

// Get the ID of the currently logged-in user
$userModel = new User($conn);
$user = $userModel->getByEmail($_SESSION['email']);
if (!$user) {
    echo json_encode(['success' => false, 'error' => 'user_not_found']);
    exit();
}
$user_id = $user['id'];

// Get and validate the data sent from the webpage
$post_id = intval($_POST['post_id'] ?? 0);
$type = $_POST['type'] ?? ''; // e.g., 'like' or 'dislike'

// Input validation: ensure we have a valid post and a valid action type
if ($post_id <= 0 || !in_array($type, ['like', 'dislike'])) {
    echo json_encode(['success' => false, 'error' => 'invalid_input']);
    exit();
}

// Use the LikeModel to process the action
$likeModel = new LikeModel($conn);
$success = $likeModel->toggle($user_id, $post_id, $type);

// Fetch the new, updated counts for the post
$new_counts = $likeModel->counts($post_id);

// Send the result and the new counts back to the browser as a JSON response
echo json_encode(['success' => $success, 'counts' => $new_counts]);
