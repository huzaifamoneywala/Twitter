<?php
header('Content-Type: application/json');
include 'db.php';
$tweet_id = isset($_GET['tweet_id']) ? (int)$_GET['tweet_id'] : 0;
$sql = "SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.tweet_id = $tweet_id ORDER BY c.created_at ASC";
$result = $conn->query($sql);
$comments = [];
while ($row = $result->fetch_assoc()) {
    $comments[] = $row;
}
echo json_encode($comments);
?>
