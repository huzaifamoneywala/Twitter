<?php
header('Content-Type: application/json');
include 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) exit;
$user_id = $_SESSION['user_id'];
$since = isset($_GET['since']) ? (int)$_GET['since'] : 0;
$sql = "SELECT t.*, u.username, u.profile_pic, 
        (SELECT COUNT(*) FROM likes l WHERE l.tweet_id = t.id) AS like_count,
        (SELECT COUNT(*) FROM likes l WHERE l.tweet_id = t.id AND l.user_id = $user_id) AS is_liked,
        (SELECT COUNT(*) FROM comments c WHERE c.tweet_id = t.id) AS comment_count
        FROM tweets t
        JOIN users u ON t.user_id = u.id
        WHERE (t.user_id = $user_id OR t.user_id IN (SELECT followed_id FROM follows WHERE follower_id = $user_id))
        AND t.id > $since
        ORDER BY t.created_at DESC";
$result = $conn->query($sql);
$tweets = [];
while ($row = $result->fetch_assoc()) {
    $tweets[] = $row;
}
echo json_encode($tweets);
?>
