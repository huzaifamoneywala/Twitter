<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>location.href = 'login.php';</script>";
    exit;
}
$user_id = $_SESSION['user_id'];
 
// Fetch initial feed
$sql = "SELECT t.*, u.username, u.profile_pic, 
        (SELECT COUNT(*) FROM likes l WHERE l.tweet_id = t.id) AS like_count,
        (SELECT COUNT(*) FROM likes l WHERE l.tweet_id = t.id AND l.user_id = $user_id) AS is_liked,
        (SELECT COUNT(*) FROM comments c WHERE c.tweet_id = t.id) AS comment_count
        FROM tweets t
        JOIN users u ON t.user_id = u.id
        WHERE t.user_id = $user_id OR t.user_id IN (SELECT followed_id FROM follows WHERE follower_id = $user_id)
        ORDER BY t.created_at DESC LIMIT 50";
$result = $conn->query($sql);
$tweets = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tweets[] = $row;
    }
}
$last_tweet_id = !empty($tweets) ? $tweets[0]['id'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Home - Twitter Clone</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background: #fff; color: #14171a; margin: 0; }
        .header { background: #fff; border-bottom: 1px solid #e6ecf0; padding: 10px 20px; font-weight: bold; font-size: 19px; text-align: center; color: #1da1f2; }
        .tweet-box { max-width: 600px; margin: 0 auto; background: #fff; border-bottom: 1px solid #e6ecf0; padding: 10px 20px; display: flex; flex-direction: column; }
        .tweet-box textarea { border: none; resize: none; font-size: 20px; outline: none; min-height: 50px; color: #14171a; }
        .tweet-box button { align-self: flex-end; background: #1da1f2; color: #fff; border: none; padding: 8px 20px; border-radius: 9999px; cursor: pointer; font-weight: bold; transition: background 0.2s; }
        .tweet-box button:hover { background: #0c85d0; }
        .feed { max-width: 600px; margin: 0 auto; }
        .tweet { border-bottom: 1px solid #e6ecf0; padding: 10px 20px; display: flex; }
        .avatar { width: 48px; height: 48px; border-radius: 50%; background: #ccd6dd; flex-shrink: 0; } /* Default gray, replace with img if pic */
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
        @media (max-width: 600px) { .tweet-box, .feed { padding: 10px; } .tweet { padding: 10px; } }
    </style>
</head>
<body>
    <div class="header">Home</div>
    <div class="tweet-box">
        <textarea id="tweet-input" placeholder="What's happening?"></textarea>
        <button onclick="postTweet()">Tweet</button>
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
                    <?php if ($tweet['user_id'] == $user_id): ?>
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
        let lastTweetId = <?php echo $last_tweet_id; ?>;
 
        function postTweet() {
            const content = document.getElementById('tweet-input').value.trim();
            if (!content) return;
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'post_tweet.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.getElementById('tweet-input').value = '';
                    fetchNewTweets(); // Refresh feed
                }
            };
            xhr.send(`content=${encodeURIComponent(content)}`);
        }
 
        function likeTweet(tweetId) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'like.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    const btn = document.getElementById(`like-btn-${tweetId}`);
                    btn.innerText = `Like (${response.like_count})`;
                    if (response.is_liked) {
                        btn.classList.add('liked');
                    } else {
                        btn.classList.remove('liked');
                    }
                }
            };
            xhr.send(`tweet_id=${tweetId}`);
        }
 
        function toggleComments(tweetId) {
            const div = document.getElementById(`comments-${tweetId}`);
            if (div.style.display === 'block') {
                div.style.display = 'none';
            } else {
                div.style.display = 'block';
                fetchComments(tweetId);
            }
        }
 
        function fetchComments(tweetId) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `get_comments.php?tweet_id=${tweetId}`, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const comments = JSON.parse(xhr.responseText);
                    const div = document.getElementById(`comments-${tweetId}`);
                    div.innerHTML = '';
                    comments.forEach(comm => {
                        const cdiv = document.createElement('div');
                        cdiv.className = 'comment';
                        cdiv.innerHTML = `<strong>${comm.username}</strong>: ${comm.content} <span>· ${comm.created_at}</span>`;
                        div.appendChild(cdiv);
                    });
                    const newComment = document.createElement('div');
                    newComment.className = 'new-comment';
                    newComment.innerHTML = `
                        <input type="text" placeholder="Add a comment..." id="comment-input-${tweetId}">
                        <button onclick="postComment(${tweetId})">Post</button>
                    `;
                    div.appendChild(newComment);
                }
            };
            xhr.send();
        }
 
        function postComment(tweetId) {
            const input = document.getElementById(`comment-input-${tweetId}`);
            const content = input.value.trim();
            if (!content) return;
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'comment.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    input.value = '';
                    fetchComments(tweetId); // Refresh comments
                    // Update comment count
                    const btn = document.getElementById(`comment-btn-${tweetId}`);
                    const count = parseInt(btn.innerText.match(/\d+/)[0]) + 1;
                    btn.innerText = `Comment (${count})`;
                }
            };
            xhr.send(`tweet_id=${tweetId}&content=${encodeURIComponent(content)}`);
        }
 
        function deleteTweet(tweetId) {
            if (!confirm('Are you sure you want to delete this tweet?')) return;
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'delete_tweet.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.getElementById(`tweet-${tweetId}`).remove();
                }
            };
            xhr.send(`tweet_id=${tweetId}`);
        }
 
        function editTweet(tweetId) {
            const newContent = prompt('Edit your tweet:', document.querySelector(`#tweet-${tweetId} .tweet-text`).innerText);
            if (!newContent || newContent.trim() === '') return;
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'edit_tweet.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.querySelector(`#tweet-${tweetId} .tweet-text`).innerText = newContent;
                }
            };
            xhr.send(`tweet_id=${tweetId}&content=${encodeURIComponent(newContent)}`);
        }
 
        function fetchNewTweets() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', `get_feed.php?since=${lastTweetId}`, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const newTweets = JSON.parse(xhr.responseText);
                    if (newTweets.length > 0) {
                        newTweets.forEach(tweet => prependTweet(tweet));
                        lastTweetId = newTweets[0].id; // Update to the latest
                    }
                }
            };
            xhr.send();
        }
 
        function prependTweet(tweet) {
            const div = document.createElement('div');
            div.className = 'tweet';
            div.id = `tweet-${tweet.id}`;
            div.innerHTML = `
                <div class="avatar"></div>
                <div class="tweet-content">
                    <div class="tweet-header">
                        <strong>${tweet.username}</strong>
                        <span>· ${tweet.created_at}</span>
                    </div>
                    <div class="tweet-text">${tweet.content}</div>
                    <div class="tweet-actions">
                        <button onclick="toggleComments(${tweet.id})" id="comment-btn-${tweet.id}">Comment (${tweet.comment_count})</button>
                        <button onclick="likeTweet(${tweet.id})" id="like-btn-${tweet.id}" class="${tweet.is_liked ? 'liked' : ''}">Like (${tweet.like_count})</button>
                        ${tweet.user_id === <?php echo $user_id; ?> ? '<button onclick="editTweet(' + tweet.id + ')">Edit</button><button onclick="deleteTweet(' + tweet.id + ')">Delete</button>' : ''}
                    </div>
                    <div class="comments-div" id="comments-${tweet.id}"></div>
                </div>
            `;
            document.querySelector('.feed').prepend(div);
        }
 
        setInterval(fetchNewTweets, 10000); // Poll every 10 seconds for real-time updates
    </script>
</body>
</html>
