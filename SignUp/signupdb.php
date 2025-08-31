<?php
$host="localhost";
$user="root";
$pass="";
$dbname="f&b_management";

$conn = new mysqli($host, $user, $pass, $dbname);
 if($conn-> connect_error)
 {
 
    die ("Connection Fail". $conn-> connect_error) ;
 
 }
 
?>