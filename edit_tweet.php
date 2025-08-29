<?php
include 'db.php';
session_start();
if (isset($_SESSION['user_id']) && isset($_POST['tweet_id']) && isset($_POST['content'])) {
    $user_id = $_SESSION['user_id'];
    $tweet_id = $_POST['tweet_id'];
    $content = $_POST['content'];
    $sql = "UPDATE tweets SET content = '" . $conn->real_escape_string($content) . "' WHERE id = $tweet_id AND user_id = $user_id";
    $conn->query($sql);
    echo 'Success';
}
?>
