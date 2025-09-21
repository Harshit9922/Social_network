<?php
session_start();
require_once __DIR__ . '/classes/User.php';
$db=new Database(); $conn=$db->getConnection(); $userModel=new User($conn);
$error=''; if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['login'])){ $email=trim($_POST['email'] ?? ''); $password=trim($_POST['password'] ?? ''); $res=$userModel->getByEmail($email); if($res && password_verify($password,$res['password'])){ $_SESSION['email']=$email; header('Location: user.php'); exit(); } else $error='Invalid credentials'; }
?><!doctype html><html><head><meta charset='utf-8'><title>Login</title><link rel='stylesheet' href='assets/style.css'></head><body>
<div class='form-container'>
  <h2>Social Network Login</h2>
  <?php if($error) echo "<p class='error'>".htmlspecialchars($error)."</p>"; ?>
  <form method='post'>
    <label>Email</label>
    <input type='email' name='email' required>
    <label>Password</label>
    <input type='password' name='password' required>
    <button type='submit' name='login'>Login</button>
  </form>
  <p style='text-align:center;margin-top:8px;'><a href='#' id='forgotLink'>Forgot password?</a></p>
  <p style='text-align:center;'>Don't have account? <a href='signup.php'>Signup</a></p>
</div>

<!-- Forgot password modal -->
<div id="forgotModal" class="modal" style="display:none;align-items:center;justify-content:center;">
  <div class="dialog">
    <h3>Reset Password</h3>
    <p>Enter your account email and new password to reset (demo).</p>
    <form method="post" action="reset_password.php">
      <label>Email</label>
      <input type="email" name="email" required>
      <label>New password</label>
      <input type="password" name="new_password" required>
      <div style="text-align:right;margin-top:8px;">
        <button type="button" class="btn" id="forgotClose">Cancel</button>
        <button type="submit" class="btn">Reset</button>
      </div>
    </form>
  </div>
</div>

<script>
document.getElementById('forgotLink').addEventListener('click', function(e){ e.preventDefault(); document.getElementById('forgotModal').style.display='flex'; });
document.getElementById('forgotClose').addEventListener('click', function(){ document.getElementById('forgotModal').style.display='none'; });
</script>
</body></html>
