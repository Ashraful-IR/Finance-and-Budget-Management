<?php
// Include database connection
include "config.php";
if (!isset($conn)) die("Database connection not found.");

// Build SQL based on GET filters (same as dashboard)
$sql = "SELECT id, fname, lname, email, phone, desi, dept, status FROM users WHERE 1=1";

if (!empty($_GET['id'])) {
    $sql .= " AND id=".(int)$_GET['id'];
}
if (!empty($_GET['category'])) {
    $sql .= " AND desi='".$conn->real_escape_string($_GET['category'])."'";
}
if (!empty($_GET['status'])) {
    $sql .= " AND status='".$conn->real_escape_string($_GET['status'])."'";
}

$result = $conn->query($sql);

// Send headers for Excel file
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=users.xlsx");

// Output column headers
echo "ID\tFirst Name\tLast Name\tEmail\tPhone\tDesignation\tDepartment\tStatus\n";

// Output data
while ($row = $result->fetch_assoc()) {
    echo $row['id']."\t"
        .$row['fname']."\t"
        .$row['lname']."\t"
        .$row['email']."\t"
        .$row['phone']."\t"
        .$row['desi']."\t"
        .$row['dept']."\t"
        .$row['status']."\n";
}

$conn->close();
exit;
?>
