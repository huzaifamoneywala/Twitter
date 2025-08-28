<?php
include 'db.php';
session_start();
if (isset($_SESSION['user_id']) && isset($_POST['tweet_id']) && isset($_POST['content'])) {
    $user_id = $_SESSION['user_id'];
    $tweet_id = $_POST['tweet_id'];
    $content = $_POST['content'];
    $sql = "INSERT INTO comments (user_id, tweet_id, content) VALUES ($user_id, $tweet_id, '" . $conn->real_escape_string($content) . "')";
    $conn->query($sql);
    echo 'Success';
}
?>
