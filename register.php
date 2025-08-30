<?php
include 'db.php';
 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password) VALUES ('$username', '$password')";
    if ($conn->query($sql) === TRUE) {
        echo "<script>location.href = 'login.php';</script>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Register - Twitter Clone</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background: linear-gradient(to bottom, #e6f3ff, #ffffff); color: #14171a; margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .container { background: #fff; padding: 40px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); width: 400px; }
        h1 { text-align: center; color: #1da1f2; font-size: 24px; margin-bottom: 20px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #e6ecf0; border-radius: 4px; font-size: 16px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #1da1f2; color: #fff; border: none; border-radius: 9999px; font-size: 16px; cursor: pointer; transition: background 0.2s; }
        button:hover { background: #0c85d0; }
        p { text-align: center; margin-top: 20px; }
        a { color: #1da1f2; text-decoration: none; }
        @media (max-width: 500px) { .container { width: 90%; padding: 20px; } }
    </style>
</head>
<body>
    <div class="container">
        <h1>Register</h1>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
</body>
</html>
