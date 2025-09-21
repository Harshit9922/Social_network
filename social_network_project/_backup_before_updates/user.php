<?php

session_start();
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/Post.php';
require_once __DIR__ . '/classes/User.php';
require_once __DIR__ . '/classes/LikeModel.php';
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}


$db = new Database();
$conn = $db->getConnection();
$userModel = new User($conn);
$current = $userModel->getByEmail($_SESSION['email']);
$postModel = new PostModel($conn);
$posts = $postModel->getAll();
?>
<!doctype html>
<html>

<head>
    <meta charset='utf-8'>
    <title>User Page</title>
    <link rel='stylesheet' href='assets/style.css'>
    <script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
</head>

<body>
    <div class='container'>
        <div class='profile-card'><img src='<?php echo htmlspecialchars($current['profile_pic']); ?>' class='avatar' onerror="this.src='uploads/default.png'">
            <h2><?php echo htmlspecialchars($current['fullname']); ?></h2>
            <p class='email'><?php echo htmlspecialchars($current['email']); ?></p>
            <p class='level'>Intermediate</p><a class='share-btn' href='#'>Share Profile</a><a class='logout-btn' href='logout.php'>Logout</a>
            <a href="edit_profile.php">Edit Profile</a>

        </div>
        <div class='main-content'>
            <div class='card add-post'>
                <form method='post' action='post_handler.php' enctype='multipart/form-data'><textarea name='content' placeholder="What's on your mind?" required></textarea>
                    <div class='add-post-actions'><input type='file' name='image' accept='image/*'><button class='btn' type='submit'>Post</button></div>
                </form>
            </div><?php while ($post = $posts->fetch_assoc()): $pp = !empty($post['profile_pic']) ? $post['profile_pic'] : 'uploads/default.png';
                        $post_id = (int)$post['id'];
                        $db2 = new Database();
                        $likeModel = new LikeModel($db2->getConnection());
                        $counts = $likeModel->counts($post_id); ?><div class='card post' data-id='<?php echo $post_id; ?>'>
                    <div class='post-header'><img src='<?php echo $pp; ?>' class='post-avatar'>
                        <div>
                            <h4><?php echo htmlspecialchars($post['fullname']); ?></h4><span class='timestamp'><?php echo $post['created_at']; ?></span>
                        </div>
                    </div>
                    <p class='post-text'><?php echo nl2br(htmlspecialchars($post['content'])); ?></p><?php if (!empty($post['image'])): ?><img src='<?php echo $post['image']; ?>' class='post-image'><?php endif; ?><div class='post-actions'><button class='like-btn' data-type='like'>👍 Like <span><?php echo $counts['like']; ?></span></button><button class='dislike-btn' data-type='dislike'>👎 Dislike <span><?php echo $counts['dislike']; ?></span></button></div>
                </div><?php endwhile; ?>
        </div>
    </div>
    <script>
        $(document).on('click', '.like-btn,.dislike-btn', function(e) {
            e.preventDefault();
            var btn = $(this);
            var post = btn.closest('.post');
            var post_id = post.data('id');
            var type = btn.data('type');
            $.post('like_handler.php', {
                post_id: post_id,
                type: type
            }, function(res) {
                if (res.success) {
                    post.find('.like-btn span').text(res.counts.like);
                    post.find('.dislike-btn span').text(res.counts.dislike);
                } else alert('Error updating');
            }, 'json');
        });
    </script>
</body>

</html>