<?php
include 'db.php';
session_start();
if (isset($_SESSION['user_id']) && isset($_POST['tweet_id'])) {
    $user_id = $_SESSION['user_id'];
    $tweet_id = $_POST['tweet_id'];
    $sql = "DELETE FROM tweets WHERE id = $tweet_id AND user_id = $user_id";
    $conn->query($sql);
    echo 'Success';
}
?>
