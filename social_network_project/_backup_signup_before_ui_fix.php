<?php
session_start();
require_once __DIR__ . '/classes/User.php';
$db=new Database(); $conn=$db->getConnection(); $userModel=new User($conn);
$errors=[]; $success='';
if($_SERVER['REQUEST_METHOD']=='POST'){
    $fullname=trim($_POST['fullname'] ?? '');
    $dob=trim($_POST['dob'] ?? '');
    $email=trim($_POST['email'] ?? '');
    $password=trim($_POST['password'] ?? '');
    $profile_pic = '';
    if(!empty($_FILES['profile_pic']['name'])){
        $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
        $allowed = ['png','jpg','jpeg','gif','webp'];
        if(in_array(strtolower($ext), $allowed)){
            $profile_pic = 'uploads/' . time() . '_' . basename($_FILES['profile_pic']['name']);
            move_uploaded_file($_FILES['profile_pic']['tmp_name'], $profile_pic);
        }
    }
    $res = $userModel->register($fullname, $dob, $email, $password, $profile_pic);
    if($res['success']){ $success='Account created. <a href="login.php">Login</a>'; } else $errors[]=$res['error'] ?? 'Error';
}
?><!doctype html>
<html><head><meta charset='utf-8'><title>Signup</title>
<link rel='stylesheet' href='assets/style.css'>
<style>
.modal { position: fixed; left:0; right:0; top:0; bottom:0; background: rgba(0,0,0,0.4); display:none; align-items:center; justify-content:center; }
.modal .dialog { background:#fff; padding:18px; border-radius:8px; width:320px; max-width:90%; box-shadow:0 8px 30px rgba(0,0,0,0.15); }
.avatar-circle { width:120px; height:120px; border-radius:50%; background:#e9e9e9; display:block; margin:0 auto 10px; object-fit:cover; }
.upload-btn { display:block; margin:6px auto; padding:8px 14px; border-radius:50px; cursor:pointer; }
</style>
</head><body>
<div class='form-container'>
  <h2>Create account</h2>
  <?php if($errors) foreach($errors as $e) echo "<p class='error'>".htmlspecialchars($e)."</p>"; ?>
  <?php if($success) echo "<p class='success'>$success</p>"; ?>
  <form method='post' enctype='multipart/form-data'>
    <label>Full name</label>
    <input type='text' name='fullname' required>
    <label>Date of birth</label>
    <!-- Button to open modal -->
    <div style="display:flex;gap:8px;">
      <input type="text" id="dob_display" name="dob" placeholder="Click 'Edit' to enter date" required readonly style="flex:1;">
      <button type="button" id="openDob" class="btn">Edit</button>
    </div>
    <label>Email</label>
    <input type='email' name='email' required>
    <label>Password</label>
    <input type='password' name='password' required>
    <label>Profile picture</label>
    <div style="text-align:center;">
      <img id="preview" class="avatar-circle" src="uploads/default.png" alt="avatar">
      <input type='file' id='profile_pic' name='profile_pic' accept='image/*' style="display:none;">
      <button type="button" class="upload-btn btn" id="choosePic">Choose File</button>
    </div>
    <button type='submit'>Signup</button>
  </form>
  <p>Already have an account? <a href='login.php'>Login</a></p>
</div>

<!-- Modal for DOB -->
<div class="modal" id="dobModal">
  <div class="dialog">
    <h3>Enter Date of Birth</h3>
    <p>You can type a date manually or use the date picker below.</p>
    <input type="text" id="dob_manual" placeholder="e.g. 12 Aug 2000 or 2000-08-12" style="width:100%;padding:8px;margin-bottom:8px;">
    <input type="date" id="dob_picker" style="width:100%;padding:8px;margin-bottom:8px;">
    <div style="text-align:right;">
      <button id="dobCancel" class="btn">Cancel</button>
      <button id="dobSave" class="btn">Save</button>
    </div>
  </div>
</div>

<script>
document.getElementById('choosePic').addEventListener('click', function(){ document.getElementById('profile_pic').click(); });
document.getElementById('profile_pic').addEventListener('change', function(e){
  const file = this.files[0];
  if(!file) return;
  const reader = new FileReader();
  reader.onload = function(){ document.getElementById('preview').src = reader.result; }
  reader.readAsDataURL(file);
});

// DOB modal logic
var dobModal = document.getElementById('dobModal');
document.getElementById('openDob').addEventListener('click', function(){ dobModal.style.display='flex'; });
document.getElementById('dobCancel').addEventListener('click', function(){ dobModal.style.display='none'; });
document.getElementById('dobSave').addEventListener('click', function(){
  var manual = document.getElementById('dob_manual').value.trim();
  var picker = document.getElementById('dob_picker').value;
  var out = manual || picker;
  if(!out){ alert('Enter a date or pick one'); return; }
  document.getElementById('dob_display').value = out;
  dobModal.style.display='none';
});
</script>
</body></html>
