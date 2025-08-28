<?php
$servername = "localhost";
$username = "uyneengacpnz5";
$password = "yq8amjwdkzqf";
$dbname = "dbb0im8i9gt4fr";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
