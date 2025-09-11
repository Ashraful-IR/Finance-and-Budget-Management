<?php
include "signupdb.php"; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname       = $_POST["firstname"];
    $lastname        = $_POST["lastname"];
    $email           = $_POST["email"];
    $phonenumber     = $_POST["phonenumber"];
    $password        = $_POST["password"];
    $confirmpassword = $_POST["confirmpassword"];
    $designation     = $_POST["designation"];
    $department      = $_POST["department"];
    $status          = "Active";
    $agree           = isset($_POST["agree"]) ? 1 : 0;

    if ($agree === 0) {
        echo "<script>alert('You must agree to the Terms & Conditions before signing up');</script>";
    } elseif (
        empty($firstname) || empty($lastname) || empty($email) ||
        empty($phonenumber) || empty($password) || empty($confirmpassword) ||
        empty($designation) || empty($department)
    ) {
        echo "<script>alert('All fields are required');</script>";
    } elseif ($password !== $confirmpassword) {
        echo "<script>alert('Passwords do not match');</script>";
    } else {
       
        $check = $conn->query("SELECT * FROM users WHERE email='$email'");
        if ($check->num_rows > 0) {
            echo "<script>alert('Email already exists, please use another');</script>";
        } else {
           
            $sql = "INSERT INTO users 
                    (fname, lname, email, phone, pass, cpass, desi, dept, status) 
                    VALUES 
                    ('$firstname', '$lastname', '$email', '$phonenumber', '$password', '$confirmpassword', '$designation', '$department', '$status')";

            if ($conn->query($sql) === TRUE) {
                echo "<script>
                        alert('Successfully registered!');
                        window.location.href = '../Login/login.php';
                      </script>";
                exit();
            } else {
                echo "<script>alert('Error: ".$conn->error."');</script>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="../SignUp/signupstyle.css" type="text/css">
   
</head>

<body>
    <form method="post" action="">
        <div class="signup-box">
            <h2>Sign Up With Your Informations</h2>

            <?php if (!empty($error)): ?>
                <div class="msg"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="left">
                <div class="input-box">
                    <span class="icon"><ion-icon name="mail-unread"></ion-icon></span>
                    <input type="text" class="text" name="firstname" required>
                    <label>First Name</label>
                </div>
                <div class="input-box">
                    <span class="icon"><ion-icon name="mail-unread"></ion-icon></span>
                    <input type="text" class="text" name="lastname" required>
                    <label>Last Name</label>
                </div>
                <div class="input-box">
                    <span class="icon"><ion-icon name="mail-unread"></ion-icon></span>
                    <input type="email" class="text" name="email" required>
                    <label>Email</label>
                </div>
                <div class="input-box">
                    <span class="icon"><ion-icon name="mail-unread"></ion-icon></span>
                    <input type="text" class="text" name="phonenumber" required>
                    <label>Phone Number</label>
                </div>
            </div>

            <div class="right">
                <div class="input-box">
                    <span class="icon"><ion-icon name="mail-unread"></ion-icon></span>
                    <input type="password" class="text" name="password" required>
                    <label>Password</label>
                </div>
                <div class="input-box">
                    <span class="icon"><ion-icon name="mail-unread"></ion-icon></span>
                    <input type="password" class="text" name="confirmpassword" required>
                    <label>Connfirm Password</label>
                </div>
                <div class="input-box">
                    <span class="icon"><ion-icon name="mail-unread"></ion-icon></span>
                    <select name="designation" class="text" required>
                        <option value="" disabled selected hidden>Designation</option>
                        <option value="Manager">Manager</option>
                        <option value="Employee">Employee</option>
                        <option value="Customer">Customer</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
                <div class="input-box">
                    <span class="icon"><ion-icon name="mail-unread"></ion-icon></span>
                    <select name="department" class="text" required>
                        <option value="" disabled selected hidden>Department</option>
                        <option value="Sales">Sales</option>
                        <option value="HR">HR</option>
                        <option value="IT">IT</option>
                        <option value="Finance">Finance</option>
                    </select>
                </div>
            </div>

            <div class="checkbox">
                <p>
                    <!-- ✅ added name="agree" -->
                    <input type="checkbox" name="agree">
                    I confirm that my information is accurate and agree to the Terms &amp; Conditions and Privacy Policy.
                </p>
            </div>

            <div>
                <button type="submit" class="signup-btn">Sign Up</button>
            </div>
        </div>
    </form>

    <script>
    document.querySelector("form").addEventListener("submit", function(e) {
        const checkbox = document.querySelector("input[name='agree']");
        if (!checkbox.checked) {
            e.preventDefault(); 
            alert("You must agree to the Terms & Conditions before signing up.");
        }
    });
    </script>
</body>
</html>
