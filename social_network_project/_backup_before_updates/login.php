<?php
session_start();
require_once __DIR__ . '/classes/User.php';
$db=new Database(); $conn=$db->getConnection(); $userModel=new User($conn);
$error=''; if($_SERVER['REQUEST_METHOD']=='POST'){ $email=trim($_POST['email']); $password=$_POST['password']; $res=$userModel->login($email,$password); if($res['success']){ $_SESSION['email']=$email; header('Location: user.php'); exit(); } else $error=$res['error']; }
?><!doctype html><html><head><meta charset='utf-8'><title>Login</title><link rel='stylesheet' href='assets/style.css'></head><body><div class='form-container'><h2>Login</h2><?php if($error) echo "<p class='error'>".htmlspecialchars($error)."</p>"; ?><form method='post'><input name='email' type='email' placeholder='Email' required><input name='password' type='password' placeholder='Password' required><button type='submit'>Login</button></form><p>No account? <a href='signup.php'>Signup</a></p></div></body></html>