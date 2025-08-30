<?php
header('Content-Type: application/json');
include 'db.php';
session_start();
if (isset($_SESSION['user_id']) && isset($_POST['tweet_id'])) {
    $user_id = $_SESSION['user_id'];
    $tweet_id = $_POST['tweet_id'];
    $check_sql = "SELECT * FROM likes WHERE user_id = $user_id AND tweet_id = $tweet_id";
    $result = $conn->query($check_sql);
    if ($result->num_rows > 0) {
        $sql = "DELETE FROM likes WHERE user_id = $user_id AND tweet_id = $tweet_id";
        $is_liked = false;
    } else {
        $sql = "INSERT INTO likes (user_id, tweet_id) VALUES ($user_id, $tweet_id)";
        $is_liked = true;
    }
    $conn->query($sql);
    $count_sql = "SELECT COUNT(*) AS count FROM likes WHERE tweet_id = $tweet_id";
    $like_count = $conn->query($count_sql)->fetch_assoc()['count'];
    echo json_encode(['like_count' => $like_count, 'is_liked' => $is_liked]);
}
?>
