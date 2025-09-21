<?php
session_start();
require_once __DIR__ . '/classes/Post.php';
$db=new Database(); $conn=$db->getConnection();
if(!isset($_SESSION['email'])){ header('Location: login.php'); exit(); }
$userStmt=$conn->prepare('SELECT id FROM users WHERE email=? LIMIT 1'); $userStmt->bind_param('s',$_SESSION['email']); $userStmt->execute(); $uRes=$userStmt->get_result()->fetch_assoc(); $user_id=$uRes['id'];
$content=trim($_POST['content'] ?? ''); if($content===''){ header('Location: user.php'); exit(); }
$image=null; if(!empty($_FILES['image']['name'])){ $allowed=['image/jpeg','image/png','image/webp']; if($_FILES['image']['error']===0 && in_array($_FILES['image']['type'],$allowed)){ $safe=time().'_'.preg_replace('/[^A-Za-z0-9._-]/','_',basename($_FILES['image']['name'])); $target='uploads/'.$safe; if(move_uploaded_file($_FILES['image']['tmp_name'],$target)) $image=$target; } }
$postModel=new PostModel($conn); $postModel->create($user_id,$content,$image); header('Location: user.php'); exit(); ?>