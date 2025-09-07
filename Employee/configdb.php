<?php
// -----------------------------------------------------------------------------
// configdb.php  — central DB connection for employee-dashboard.php
// -----------------------------------------------------------------------------

$DB_HOST = 'localhost';   // change if needed
$DB_USER = 'root';        // change if needed
$DB_PASS = '';            // change if needed
$DB_NAME = 'fnb';         // your database name

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_errno) {
    // Keep the message simple if this file is ever included during HTML render
    // (the SPA calls the API endpoints via fetch and will show errors there).
    die('Database connection failed: ' . $conn->connect_error);
}

// Always use UTF-8
if (!$conn->set_charset('utf8mb4')) {
    die('Failed to set charset: ' . $conn->error);
}
