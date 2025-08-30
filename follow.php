<?php
include 'db.php';
session_start();
if (isset($_SESSION['user_id']) && isset($_POST['user_id'])) {
    $follower_id = $_SESSION['user_id'];
    $followed_id = $_POST['user_id'];
    if ($follower_id == $followed_id) exit; // No self-follow
    $check_sql = "SELECT * FROM follows WHERE follower_id = $follower_id AND followed_id = $followed_id";
    $result = $conn->query($check_sql);
    if ($result->num_rows > 0) {
        $sql = "DELETE FROM follows WHERE follower_id = $follower_id AND followed_id = $followed_id";
    } else {
        $sql = "INSERT INTO follows (follower_id, followed_id) VALUES ($follower_id, $followed_id)";
    }
    $conn->query($sql);
    echo 'Success';
}
?>
