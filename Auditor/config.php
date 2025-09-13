
<?php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'fnb';

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_errno) {
    die('Database connection failed: ' . $conn->connect_error);
}

if (!$conn->set_charset('utf8mb4')) {
    die('Failed to set charset: ' . $conn->error);
}
