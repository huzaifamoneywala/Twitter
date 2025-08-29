<?php
include 'db.php';
session_start();
if (isset($_SESSION['user_id']) && isset($_POST['bio'])) {
    $user_id = $_SESSION['user_id'];
    $bio = $_POST['bio'];
    $sql = "UPDATE users SET bio = '" . $conn->real_escape_string($bio) . "' WHERE id = $user_id";
    $conn->query($sql);
    echo 'Success';
}
?>
