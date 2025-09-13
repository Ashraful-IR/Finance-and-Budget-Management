<?php
// Include DB connection
include "config.php";
if (!isset($conn)) die("Database connection not found.");

// Build SQL based on GET filters
$sql = "SELECT id, fname, lname, email, phone, desi, dept, status FROM users WHERE 1=1";

if (!empty($_GET['id'])) $sql .= " AND id=".(int)$_GET['id'];
if (!empty($_GET['category'])) $sql .= " AND desi='".$conn->real_escape_string($_GET['category'])."'";
if (!empty($_GET['status'])) $sql .= " AND status='".$conn->real_escape_string($_GET['status'])."'";

$result = $conn->query($sql);
if(!$result) die("Query failed: ".$conn->error);

// Send headers to force download as CSV
header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=users.csv");

// Open output stream
$output = fopen('php://output', 'w');

// Add column headers
fputcsv($output, ['ID','First Name','Last Name','Email','Phone','Designation','Department','Status']);

// Output data rows
while($row = $result->fetch_assoc()){
    fputcsv($output, [
        $row['id'],
        $row['fname'],
        $row['lname'],
        $row['email'],
        $row['phone'],
        $row['desi'],
        $row['dept'],
        $row['status']
    ]);
}

fclose($output);
$conn->close();
exit;
?>
