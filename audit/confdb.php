<?php
$host = 'localhost';
$db   = 'webtech';  // replace with your DB name
$user = 'root';     // default for XAMPP
$pass = '';         // default empty password

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
