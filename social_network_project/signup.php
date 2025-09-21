<?php
session_start();
require_once __DIR__ . '/classes/User.php';
$db = new Database();
$conn = $db->getConnection();
$userModel = new User($conn);

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $fullname = trim($_POST['fullname'] ?? '');
  $dob = trim($_POST['dob'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = trim($_POST['password'] ?? '');
  $confirm = trim($_POST['confirm_password'] ?? '');

  if ($password !== $confirm) {
    $errors[] = 'Passwords do not match.';
  }

  // default image
  $profile_pic = 'uploads/default.png';

  // If user uploads custom image
  if (!empty($_FILES['profile_pic']['name'])) {
    $ext = pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION);
    $allowed = ['png', 'jpg', 'jpeg', 'gif', 'webp'];
    if (in_array(strtolower($ext), $allowed)) {
      $profile_pic = 'uploads/' . time() . '_' . basename($_FILES['profile_pic']['name']);
      move_uploaded_file($_FILES['profile_pic']['tmp_name'], $profile_pic);
    }
  }

  $res = $userModel->register($fullname, $dob, $email, $password, $profile_pic);
  if ($res['success']) {
    $success = 'Account created. <a href="login.php">Login</a>';
  } else {
    $errors[] = $res['error'] ?? 'Error creating account.';
  }
}
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>Signup</title>
  <link rel="stylesheet" href="assets/style.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f5f5f5;
    }

    .signup-card {
      max-width: 480px;
      margin: 40px auto;
      padding: 28px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 6px 24px rgba(0, 0, 0, 0.08);
    }

    h2 {
      text-align: center;
      margin-top: 0;
    }

    .top-avatar {
      text-align: center;
      margin-bottom: 15px;
    }

    .avatar-circle {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      object-fit: cover;
      border: 4px solid #eee;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .btn {
      padding: 8px 14px;
      border: none;
      background: #007bff;
      color: #fff;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
    }

    .btn:hover {
      background: #0056b3;
    }

    input[type=text],
    input[type=email],
    input[type=password],
    input[type=date] {
      width: 100%;
      padding: 10px;
      margin: 8px 0 16px 0;
      border: 1px solid #ccc;
      border-radius: 6px;
    }

    .error {
      color: #d9534f;
      font-size: 14px;
    }

    .success {
      color: #28a745;
      font-size: 14px;
    }

    .choose-row {
      display: flex;
      gap: 10px;
      justify-content: center;
      margin-top: 10px;
    }

    small {
      color: #666;
    }
  </style>
</head>

<body>
  <div class="signup-card">
    <h2>Join Social Network</h2>

    <?php if ($errors) foreach ($errors as $e) echo "<p class='error'>" . htmlspecialchars($e) . "</p>"; ?>
    <?php if ($success) echo "<p class='success'>$success</p>"; ?>

    <form id="signupForm" method="post" enctype="multipart/form-data">
      <!-- Profile picture -->
      <div class="top-avatar">
        <img id="preview" class="avatar-circle"
          src="default.png"
          onerror="this.src='default.png'"
          alt="Profile Picture">
        <div class="choose-row">
          <input type="file" id="profile_pic" name="profile_pic" accept="image/*" style="display:none;">
          <button type="button" class="btn" id="choosePic">Upload Profile Pic</button>
          <button type="button" class="btn" id="clearPic">Clear</button>
        </div>
      </div>

      <label>Full Name</label>
      <input type="text" name="fullname" required>

      <label>Date of Birth</label>
      <input type="date" name="dob" id="dob" required>


      <label>Email</label>
      <input type="email" name="email" required>

      <label>Password</label>
      <input type="password" name="password" required>

      <label>Confirm Password</label>
      <input type="password" name="confirm_password" required>

      <button type="submit" class="btn" style="width:100%; margin-top:10px;">Signup</button>
    </form>

    <p style="text-align:center; margin-top:15px;">
      Already have an account? <a href="login.php">Login</a>
    </p>
  </div>

  <script>
    // Profile pic chooser + preview + clear
    document.getElementById('choosePic').addEventListener('click', () => {
      document.getElementById('profile_pic').click();
    });
    document.getElementById('profile_pic').addEventListener('change', function() {
      const file = this.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('preview').src = e.target.result;
        reader.readAsDataURL(file);
      }
    });
    document.getElementById('clearPic').addEventListener('click', () => {
      document.getElementById('preview').src = 'uploads/default.png';
      document.getElementById('profile_pic').value = '';
    });
  </script>
</body>

</html>