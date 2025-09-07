<?php
// -----------------------------------------------------------------------------
// configdb.php
// Central DB connection file for employee-dashboard.php
// -----------------------------------------------------------------------------

$servername = "localhost";
$username   = "root";     // 👈 update if needed
$password   = "";         // 👈 update if needed
$dbname     = "fnb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Handle connection error gracefully
if ($conn->connect_error) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode([
        "ok"      => false,
        "error"   => "Database connection failed",
        "details" => $conn->connect_error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Ensure proper UTF-8 handling
if (!$conn->set_charset("utf8mb4")) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode([
        "ok"      => false,
        "error"   => "Failed to set charset",
        "details" => $conn->error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}
?>
