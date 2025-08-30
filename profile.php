<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>location.href = 'login.php';</script>";
    exit;
}
$current_user_id = $_SESSION['user_id'];
$view_username = isset($_GET['user']) ? $_GET['user'] : $_SESSION['username'];
$sql = "SELECT * FROM users WHERE username = '$view_username'";
$result = $conn->query($sql);
if ($result->num_rows == 0) {
    die("User not found.");
}
$user = $result->fetch_assoc();
$view_user_id = $user['id'];
 
// Tweets
$sql = "SELECT t.*, u.username, u.profile_pic, 
        (SELECT COUNT(*) FROM likes l WHERE l.tweet_id = t.id) AS like_count,
        (SELECT COUNT(*) FROM likes l WHERE l.tweet_id = t.id AND l.user_id = $current_user_id) AS is_liked,
        (SELECT COUNT(*) FROM comments c WHERE c.tweet_id = t.id) AS comment_count
        FROM tweets t JOIN users u ON t.user_id = u.id WHERE t.user_id = $view_user_id ORDER BY t.created_at DESC";
$tweets_result = $conn->query($sql);
$tweets = [];
while ($row = $tweets_result->fetch_assoc()) {
    $tweets[] = $row;
}
 
// Followers and following counts
$followers_sql = "SELECT COUNT(*) AS count FROM follows WHERE followed_id = $view_user_id";
$followers = $conn->query($followers_sql)->fetch_assoc()['count'];
$following_sql = "SELECT COUNT(*) AS count FROM follows WHERE follower_id = $view_user_id";
$following = $conn->query($following_sql)->fetch_assoc()['count'];
 
// Is following?
$is_following = false;
if ($view_user_id != $current_user_id) {
    $follow_sql = "SELECT * FROM follows WHERE follower_id = $current_user_id AND followed_id = $view_user_id";
    $is_following = $conn->query($follow_sql)->num_rows > 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Profile - Twitter Clone</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background: #fff; color: #14171a; margin: 0; }
        .header { background: #fff; border-bottom: 1px solid #e6ecf0; padding: 10px 20px; font-weight: bold; font-size: 19px; text-align: center; color: #1da1f2; }
        .profile-header { max-width: 600px; margin: 0 auto; padding: 20px; border-bottom: 1px solid #e6ecf0; }
        .profile-avatar { width: 134px; height: 134px; border-radius: 50%; background: #ccd6dd; margin-bottom: 10px; }
        .profile-name { font-size: 20px; font-weight: bold; }
        .profile-bio { color: #657786; margin: 10px 0; }
        .profile-stats { color: #657786; font-size: 14px; }
        .profile-stats strong { color: #14171a; }
        .profile-button { margin-top: 10px; }
        .profile-button button { background: #1da1f2; color: #fff; border: none; padding: 8px 20px; border-radius: 9999px; cursor: pointer; font-weight: bold; transition: background 0.2s; }
        .profile-button button:hover { background: #0c85d0; }
        .feed { max-width: 600px; margin: 0 auto; }
        /* Reuse tweet styles from index.php */
        .tweet { border-bottom: 1px solid #e6ecf0; padding: 10px 20px; display: flex; }
        .avatar { width: 48px; height: 48px; border-radius: 50%; background: #ccd6dd; flex-shrink: 0; }
        .tweet-content { margin-left: 12px; flex: 1; }
        .tweet-header { display: flex; align-items: center; }
        .tweet-header strong { font-weight: bold; }
        .tweet-header span { color: #657786; margin-left: 4px; font-size: 15px; }
        .tweet-text { margin-top: 4px; font-size: 15px; line-height: 20px; }
        .tweet-actions { margin-top: 8px; display: flex; color: #657786; font-size: 13px; }
        .tweet-actions button { background: none; border: none; cursor: pointer; margin-right: 20px; display: flex; align-items: center; transition: color 0.2s; }
        .tweet-actions button:hover { color: #1da1f2; }
        .tweet-actions .liked { color: #e0245e !important; }
        .comments-div { margin-top: 10px; display: none; padding-left: 60px; }
        .comment { font-size: 14px; margin-bottom: 10px; border-top: 1px solid #e6ecf0; padding-top: 10px; }
        .comment strong { color: #14171a; }
        .comment span { color: #657786; font-size: 13px; }
        .new-comment { display: flex; margin-top: 10px; }
        .new-comment input { flex: 1; border: 1px solid #e6ecf0; padding: 8px; border-radius: 4px; }
        .new-comment button { background: #1da1f2; color: #fff; border: none; padding: 8px 12px; border-radius: 9999px; margin-left: 10px; cursor: pointer; }
        @media (max-width: 600px) { .profile-header, .feed { padding: 10px; } .tweet { padding: 10px; } .profile-avatar { width: 100px; height: 100px; } }
    </style>
</head>
<body>
    <div class="header">Profile</div>
    <div class="profile-header">
        <div class="profile-avatar"></div>
        <div class="profile-name"><?php echo htmlspecialchars($user['username']); ?></div>
        <div class="profile-bio"><?php echo htmlspecialchars($user['bio'] ?? 'No bio yet.'); ?></div>
        <div class="profile-stats">
            <strong><?php echo $following; ?></strong> Following
            <strong><?php echo $followers; ?></strong> Followers
        </div>
        <div class="profile-button">
            <?php if ($view_user_id == $current_user_id): ?>
                <button onclick="editProfile()">Edit Profile</button>
            <?php else: ?>
                <button onclick="toggleFollow(<?php echo $view_user_id; ?>)" id="follow-btn"><?php echo $is_following ? 'Unfollow' : 'Follow'; ?></button>
            <?php endif; ?>
        </div>
    </div>
    <div class="feed">
        <?php foreach ($tweets as $tweet): ?>
        <div class="tweet" id="tweet-<?php echo $tweet['id']; ?>">
            <div class="avatar"></div>
            <div class="tweet-content">
                <div class="tweet-header">
                    <strong><?php echo htmlspecialchars($tweet['username']); ?></strong>
                    <span>· <?php echo $tweet['created_at']; ?></span>
                </div>
                <div class="tweet-text"><?php echo htmlspecialchars($tweet['content']); ?></div>
                <div class="tweet-actions">
                    <button onclick="toggleComments(<?php echo $tweet['id']; ?>)" id="comment-btn-<?php echo $tweet['id']; ?>">Comment (<?php echo $tweet['comment_count']; ?>)</button>
                    <button onclick="likeTweet(<?php echo $tweet['id']; ?>)" id="like-btn-<?php echo $tweet['id']; ?>" class="<?php echo $tweet['is_liked'] ? 'liked' : ''; ?>">Like (<?php echo $tweet['like_count']; ?>)</button>
                    <?php if ($tweet['user_id'] == $current_user_id): ?>
                    <button onclick="editTweet(<?php echo $tweet['id']; ?>)">Edit</button>
                    <button onclick="deleteTweet(<?php echo $tweet['id']; ?>)">Delete</button>
                    <?php endif; ?>
                </div>
                <div class="comments-div" id="comments-<?php echo $tweet['id']; ?>"></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <script>
        // Reuse functions from index.php for likeTweet, toggleComments, fetchComments, postComment, editTweet, deleteTweet
 
        function toggleFollow(userId) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'follow.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const btn = document.getElementById('follow-btn');
                    btn.innerText = btn.innerText === 'Follow' ? 'Unfollow' : 'Follow';
                    // Could update counts, but for simplicity reload
                    location.reload();
                }
            };
            xhr.send(`user_id=${userId}`);
        }
 
        function editProfile() {
            const newBio = prompt('Update your bio:', '<?php echo addslashes($user['bio'] ?? ''); ?>');
            if (newBio === null) return;
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'edit_profile.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.querySelector('.profile-bio').innerText = newBio;
                }
            };
            xhr.send(`bio=${encodeURIComponent(newBio)}`);
        }
 
        // Paste the JS functions for likeTweet, toggleComments, etc., from index.php here to avoid duplication issues
        // For brevity, assuming they are similar and can be copied if needed in actual implementation.
    </script>
</body>
</html>
