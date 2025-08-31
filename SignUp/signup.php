<?php

include "signupdb.php"; // PHP extarnal file connetion

$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = $_POST["firstname"];
    $lastname = $_POST["lastname"];
    $email = $_POST["email"];
    $phonenumber = $_POST["phonenumber"];
    $password = $_POST["password"];
    $confirmpassword = $_POST["confirmpassword"];
    $designation = $_POST["designation"];
    $department = $_POST["department"];

    if (empty($firstname) || empty($lastname) || empty($email) || empty($phonenumber) || empty($password) || empty($confirmpassword) || empty($designation) || empty($department)) {

        $error = "All section should be filled";
    } else {

        $sql = "INSERT INTO register (username , password , email) VALUES ('$username','$hash_pass','$email')";



        if ($conn->query($sql) === TRUE) {
            $success = "Registartion Complete, U can do login ";
        } else {
            $error = "Error" . $conn->error;
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
    <div class="signup-box">
        <h2>Sign Up With Your Informations</h2>
        <div class="left">
            <div class="input-box">
                <span class="icon">
                    <ion-icon name="mail-unread"></ion-icon>
                </span>
                <input type="text" class="text" name="firstname" required>
                <label for="">First Name</label>
            </div>
            <div class="input-box">
                <span class="icon">
                    <ion-icon name="mail-unread"></ion-icon>
                </span>
                <input type="text" class="text" name="lastname" required>
                <label for="">Last Name</label>
            </div>
            <div class="input-box">
                <span class="icon">
                    <ion-icon name="mail-unread"></ion-icon>
                </span>
                <input type="email" class="text" name="email" required>
                <label for="">Email</label>
            </div>
            <div class="input-box">
                <span class="icon">
                    <ion-icon name="mail-unread"></ion-icon>
                </span>
                <input type="text" class="text" name="phonenumber" required>
                <label for="">Phone Number</label>
            </div>
        </div>


        <div class="right">
            <div class="input-box">
                <span class="icon">
                    <ion-icon name="mail-unread"></ion-icon>
                </span>
                <input type="password" class="text" name="password" required>
                <label for="">Password</label>
            </div>
            <div class="input-box">
                <span class="icon">
                    <ion-icon name="mail-unread"></ion-icon>
                </span>
                <input type="password" class="text" name="confirmpassword" required>
                <label for="">Connfirm Password</label>
            </div>
            <div class="input-box">
                <span class="icon">
                    <ion-icon name="mail-unread"></ion-icon>
                </span>
                <select name="designation" class="text" id="">
                    <option value="" disabled selected hidden> Designation</option>
                    <option value="">Manager</option>
                    <option value="">Employee</option>
                    <option value="">Customer</option>
                    <option value="">Admin</option>
                </select>
            </div>
            <div class="input-box">
                <span class="icon">
                    <ion-icon name="mail-unread"></ion-icon>
                </span>
                <select name="department" class="text" id="">
                    <option value="" disabled selected hidden> Department</option>
                    <option value="">Manager</option>
                    <option value="">Employee</option>
                    <option value="">Customer</option>
                    <option value="">Admin</option>
                </select>
            </div>

        </div class="checkbox">
        <p><input type="checkbox">I confirm that my information is accurate and agree to the Terms & Conditions and Privacy Policy.</input></p>
        <div>
            <button type="submit" class="signup-btn">Sign Up</button>
        </div>
    </div>
</body>