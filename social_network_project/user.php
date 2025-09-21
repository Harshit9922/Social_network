<?php
session_start();
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Post.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/LikeModel.php';

// Redirect to login if the user is not authenticated
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}

$db = new Database();
$conn = $db->getConnection();

$userModel = new User($conn);
$postModel = new PostModel($conn);
$likeModel = new LikeModel($conn); 

// Fetch the current user's data
$user = $userModel->getByEmail($_SESSION['email']);
if (!$user) {
    // If user data is not found, log them out for safety
    session_destroy();
    header('Location: login.php');
    exit();
}
// Fetch all posts for the current user
$posts = $postModel->getByUser($user['id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Network</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body >

<div class="social-container">
     
    <!-- Left Sidebar: User Profile -->
    <aside class="profile-sidebar">
        <h1>Social Network</h1>
        <div class="profile-card">
            
            <form id="profilePicForm" action="update_profile.php" method="post" enctype="multipart/form-data" style="position: relative;">
                <img src="<?= htmlspecialchars($user['profile_pic'] ?? 'uploads/default.png') ?>" alt="Profile Picture" class="profile-pic-large" id="profilePreview">
                <label for="profile_pic_upload" class="edit-pic-icon"><i class="fa-solid fa-pencil"></i></label>
                <input type="file" name="profile_pic" id="profile_pic_upload" accept="image/*" style="display: none;">
                <button type="submit" id="uploadProfileBtn" class="btn" style="display:none; margin-top:10px; width:100%;">Save Photo</button>
            </form>
            
            <h3 id="profileFullname"><?= htmlspecialchars($user['fullname'] ?? 'User Name') ?></h3>
            <p id="profileEmail"><?= htmlspecialchars($user['email'] ?? 'user@example.com') ?></p>
            
            <div class="profile-level">
                <span id="profileIntermediate"><?= htmlspecialchars($user['intermediate'] ?? 'Beginner') ?></span>
                <i id="editProfileBtn" class="fa-solid fa-pencil" style="cursor: pointer;"></i>
            </div>
            
            <button id="shareProfileBtn" class="btn-share">Share Profile</button>
            <a href="logout.php" class="logout-link">Logout</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-feed">
        <!-- Add Post Box -->
        <div class="add-post-box">
            <h4>Add Post</h4>
            <form action="post_handler.php" method="post" enctype="multipart/form-data">
                <textarea name="content" placeholder="🚀 What's on your mind?" required></textarea>
                <div id="postImagePreview" class="post-image-preview"></div>
                <div class="add-post-actions">
                    <button type="submit" class="btn-post">Post</button>
                    <label for="imageUpload" class="btn-add-image">
                        <i class="fa-solid fa-image"></i> Add Image
                    </label>
                    <input type="file" id="imageUpload" name="image" accept="image/*" style="display: none;">
                </div>
            </form>
        </div>

        <!-- Feed Section -->
        <section class="feed">
            <?php while ($row = $posts->fetch_assoc()): 
                $counts = $likeModel->counts($row['id']);
            ?>
                <div class="feed-card" data-post-id="<?= $row['id'] ?>">
                    <div class="post-header">
                        <img src="<?= htmlspecialchars($row['profile_pic'] ?? 'uploads/default.png') ?>" alt="user avatar" class="profile-pic-small">
                        <div class="post-user-info">
                            <strong><?= htmlspecialchars($row['fullname']) ?></strong>
                            <span>Posted on - <?= date('d M Y', strtotime($row['created_at'])) ?></span>
                        </div>
                        <?php if (isset($row['email']) && $row['email'] == $_SESSION['email']): ?>
                            <div class="post-owner-actions">
                                <button class="edit-post-btn"><i class="fa-solid fa-pencil"></i></button>
                                <button class="delete-post-btn" data-id="<?= $row['id'] ?>"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="post-content-wrapper">
                        <p class="post-content"><?= nl2br(htmlspecialchars($row['content'])) ?></p>
                    </div>
                    <?php if (!empty($row['image'])): ?>
                        <img src="<?= htmlspecialchars($row['image']) ?>" alt="Post image" class="post-image">
                    <?php endif; ?>
                    <div class="post-actions">
                        <button class="action-btn like-btn" data-type="like">
                            <i class="fa-regular fa-thumbs-up"></i> Like <span class="likes-count"><?= $counts['likes'] ?? 0 ?></span>
                        </button>
                        <button class="action-btn dislike-btn" data-type="dislike">
                            <i class="fa-regular fa-thumbs-down"></i> Dislike <span class="dislikes-count"><?= $counts['dislikes'] ?? 0 ?></span>
                        </button>
                    </div>
                </div>
            <?php endwhile; ?>
        </section>
    </main>
</div>

<!-- Edit Profile Modal -->
<div id="editProfileModal" class="modal" style="display:none;">
  <div class="dialog">
    <h3>Edit Your Profile</h3>
    <form id="editProfileForm">
        <label for="fullname">Full Name</label>
        <input type="text" id="fullname" name="fullname" value="<?= htmlspecialchars($user['fullname']) ?>" required>
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        <label for="intermediate">Level</label>
        <input type="text" id="intermediate" name="intermediate" value="<?= htmlspecialchars($user['intermediate']) ?>">
        <div class="edit-actions" style="margin-top: 15px;">
            <button type="button" id="cancelEditProfile" class="btn-cancel-edit">Cancel</button>
            <button type="submit" class="btn-save-edit">Save Changes</button>
        </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- Profile Picture Preview Logic ---
    const profilePicInput = document.getElementById('profile_pic_upload');
    const profilePreview = document.getElementById('profilePreview');
    const uploadProfileBtn = document.getElementById('uploadProfileBtn');
    profilePicInput.addEventListener('change', function (event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                profilePreview.src = e.target.result;
                uploadProfileBtn.style.display = 'block';
            }
            reader.readAsDataURL(file);
        }
    });

    // --- Post Image Preview Logic ---
    const imageUploadInput = document.getElementById('imageUpload');
    const postImagePreview = document.getElementById('postImagePreview');
    imageUploadInput.addEventListener('change', function(event) {
        const file = event.target.files[0];
        postImagePreview.innerHTML = ''; 
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                postImagePreview.appendChild(img);
            }
            reader.readAsDataURL(file);
        }
    });

    // --- Edit Profile Modal Logic ---
    const editProfileModal = document.getElementById('editProfileModal');
    const editProfileBtn = document.getElementById('editProfileBtn');
    const cancelEditProfileBtn = document.getElementById('cancelEditProfile');
    const editProfileForm = document.getElementById('editProfileForm');
    editProfileBtn.addEventListener('click', function() {
        editProfileModal.style.display = 'flex';
    });
    cancelEditProfileBtn.addEventListener('click', function() {
        editProfileModal.style.display = 'none';
    });
    editProfileForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('update_profile.php', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (response.ok) {
                document.getElementById('profileFullname').textContent = formData.get('fullname');
                document.getElementById('profileEmail').textContent = formData.get('email');
                document.getElementById('profileIntermediate').textContent = formData.get('intermediate');
                editProfileModal.style.display = 'none';
            } else {
                alert('An error occurred while updating the profile.');
            }
        });
    });

    // --- Share Profile Logic ---
    const shareBtn = document.getElementById('shareProfileBtn');
    shareBtn.addEventListener('click', async () => {
        const profileUrl = window.location.href;
        const profileName = document.getElementById('profileFullname').textContent;
        const shareData = {
            title: `Check out ${profileName}'s Profile`,
            text: `Take a look at ${profileName}'s profile on our Social Network!`,
            url: profileUrl,
        };
        if (navigator.share) {
            try {
                await navigator.share(shareData);
            } catch (err) {
                console.error('Error sharing:', err);
            }
        } else {
            try {
                await navigator.clipboard.writeText(profileUrl);
                const originalText = shareBtn.textContent;
                shareBtn.textContent = 'Link Copied!';
                setTimeout(() => { shareBtn.textContent = originalText; }, 2000);
            } catch (err) {
                alert('Sharing is not supported. Please copy the link from the address bar.');
            }
        }
    });

    // --- Post Interactions (Likes, Deletes, Edits) ---
    const mainFeed = document.querySelector('.main-feed');
    mainFeed.addEventListener('click', function(e) {
        const target = e.target;

        // Handle Likes/Dislikes
        const actionBtn = target.closest('.like-btn, .dislike-btn');
        if (actionBtn) {
            const postCard = actionBtn.closest('.feed-card');
            const postId = postCard.dataset.postId;
            const type = actionBtn.dataset.type;
            const formData = new FormData();
            formData.append('post_id', postId);
            formData.append('type', type);
            fetch('like_handler.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    postCard.querySelector('.likes-count').textContent = data.counts.likes;
                    postCard.querySelector('.dislikes-count').textContent = data.counts.dislikes;
                }
            });
        }

        // Handle Deletes
        const deleteBtn = target.closest('.delete-post-btn');
        if (deleteBtn) {
            if (!confirm('Are you sure you want to delete this post?')) return;
            const postCard = deleteBtn.closest('.feed-card');
            const postId = deleteBtn.dataset.id;
            const formData = new FormData();
            formData.append('post_id', postId);
            fetch('delete_post.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    postCard.style.opacity = '0';
                    setTimeout(() => postCard.remove(), 300);
                } else {
                    alert('Error: Could not delete post.');
                }
            });
        }

        // Handle Edit Post Click
        const editBtn = target.closest('.edit-post-btn');
        if (editBtn) {
            const postCard = editBtn.closest('.feed-card');
            const contentWrapper = postCard.querySelector('.post-content-wrapper');
            const postContentP = contentWrapper.querySelector('.post-content');
            if (!postContentP) return; // Already in edit mode
            const currentContent = postContentP.innerHTML.replace(/<br\s*[\/]?>/gi, "\n");
            // Store original content to revert on cancel
            contentWrapper.setAttribute('data-original-content', currentContent);
            contentWrapper.innerHTML = `
                <textarea class="edit-post-textarea">${currentContent}</textarea>
                <div class="edit-actions">
                    <button class="btn-cancel-edit">Cancel</button>
                    <button class="btn-save-edit">Save</button>
                </div>
            `;
        }

        // Handle Save Edit
        const saveBtn = target.closest('.btn-save-edit');
        if (saveBtn) {
            const postCard = saveBtn.closest('.feed-card');
            const postId = postCard.dataset.postId;
            const textarea = postCard.querySelector('.edit-post-textarea');
            const newContent = textarea.value;
            const formData = new FormData();
            formData.append('post_id', postId);
            formData.append('content', newContent);
            fetch('edit_post.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const contentWrapper = postCard.querySelector('.post-content-wrapper');
                    const displayContent = newContent.replace(/\n/g, '<br>');
                    contentWrapper.innerHTML = `<p class="post-content">${displayContent}</p>`;
                } else {
                    alert('Error saving post.');
                }
            });
        }

        // Handle Cancel Edit
        const cancelBtn = target.closest('.btn-cancel-edit');
        if (cancelBtn && !cancelBtn.closest('#editProfileModal')) {
             const postCard = cancelBtn.closest('.feed-card');
             const contentWrapper = postCard.querySelector('.post-content-wrapper');
             const originalContent = contentWrapper.getAttribute('data-original-content');
             const displayContent = originalContent.replace(/\n/g, '<br>');
             contentWrapper.innerHTML = `<p class="post-content">${displayContent}</p>`;
        }
    });
});
</script>

</body>
</html>

