<?php
include "logindb.php"; 

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = $_POST["email"] ?? "";
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {
        $error = "Please enter email and password.";
    } else {
        
        $sql = "SELECT desi, status FROM users WHERE email='$email' AND pass='$password' LIMIT 1";
        $result = $conn->query($sql);

        if ($result && $result->num_rows === 1) {
            $row = $result->fetch_assoc();
            $role   = $row["desi"];
            $status = $row["status"];

            if ($status === "Inactive") {
                $error = "Sorry, your account is currently inactive. Please contact with Admin.";
            } else {
                if ($role === "Admin") {
                    header("Location: ../Admin/dash.php");
                    exit();
                } elseif ($role === "Manager") {
                    header("Location: ../Manager/dash.php");
                    exit();
                } else {
                    $error = "Access allowed only for Admin or Manager.";
                }
            }
        } else {
            $error = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In</title>
    <link rel="stylesheet" href="../LogIn/loginstyle.css" type="text/css">
</head>

<body>
    <div class="login-box">
        <h2>Log In To Your Account</h2>

        <?php if ($error): ?>
            <p style="color:#ffb3b3; font-weight:bold; margin-bottom:10px;">
                <?php echo htmlspecialchars($error); ?>
            </p>
        <?php endif; ?>

        <form method="post" action="">
            <div class="input-box">
                <span class="icon">
                    <ion-icon name="mail-unread"></ion-icon>
                </span>
                <input type="email" class="text" name="email" required>
                <label>Email</label>
            </div>
            <div class="input-box">
                <span class="icon">
                    <ion-icon name="lock-closed"></ion-icon>
                </span>
                <input type="password" class="text" name="password" required>
                <label>Password</label>
            </div>
            <div class="remember-forgate">
                <label><input type="checkbox">Remember me</label>
                <a href="#">Forgot Password?</a>
            </div>
            <div>
                <button type="submit" class="log-btn">Log In</button>
            </div>
        </form>

        <div class="register">
            <p>Don't have an account?
                <a href="../SignUp/signup.php">Create an account</a>
            </p>
        </div>
    </div>

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>
