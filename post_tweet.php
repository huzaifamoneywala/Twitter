<?php
include 'db.php';
session_start();
if (isset($_SESSION['user_id']) && isset($_POST['content'])) {
    $user_id = $_SESSION['user_id'];
    $content = $_POST['content'];
    $sql = "INSERT INTO tweets (user_id, content) VALUES ($user_id, '" . $conn->real_escape_string($content) . "')";
    $conn->query($sql);
    echo 'Success';
}
?>
