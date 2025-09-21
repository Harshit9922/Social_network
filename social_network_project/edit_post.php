<?php
session_start();
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Post.php';
require_once __DIR__ . '/classes/User.php';
if(!isset($_SESSION['email'])){ echo json_encode(['success'=>false]); exit; }
$db=new Database(); $conn=$db->getConnection();
$userM = new User($conn); $user = $userM->getByEmail($_SESSION['email']);
$postModel = new PostModel($conn);
$post_id = intval($_POST['post_id'] ?? 0);
$content = trim($_POST['content'] ?? '');
if($post_id && strlen($content)){
    $stmt = $conn->prepare("UPDATE posts SET content=? WHERE id=? AND user_id=?");
    $stmt->bind_param('sii',$content, $post_id, $user['id']);
    if($stmt->execute()) echo json_encode(['success'=>true]); else echo json_encode(['success'=>false]);
}else echo json_encode(['success'=>false]);
?>