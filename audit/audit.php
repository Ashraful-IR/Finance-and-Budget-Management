<?php
include_once __DIR__ . "/../dbconfig.php";

if (!isset($conn)) die("Database connection not found.");

// Helper: build SQL filter
function buildFilterSQL($conn, $data) {
    $sql = "SELECT * FROM users WHERE 1=1";
    $mapping = [
        'id' => fn($v)=> "id=".(int)$v,
        'category' => fn($v): string=> "desi='".$conn->real_escape_string($v)."'",
        'status' => fn($v): string=> "status='".$conn->real_escape_string($v)."'",
        'date_from' => fn($v): string=> "date>='".$conn->real_escape_string($v)."'",
        'date_to' => fn($v): string=> "date<='".$conn->real_escape_string($v)."'"
    ];
    foreach ($mapping as $key => $func) {
        if (!empty($data[$key])) $sql .= " AND " . $func($data[$key]);
    }
    return $sql;
}

// Handle "Hold" transaction
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['holdTransaction'])) {
    $conn->query("UPDATE users SET status='Held' WHERE id=".(int)$_POST['userId']);
    $queryParams = array_filter([
        'id'=>$_POST['id'] ?? '',
        'category'=>$_POST['category'] ?? '',
        'status'=>$_POST['status'] ?? '',
        'date_from'=>$_POST['date_from'] ?? '',
        'date_to'=>$_POST['date_to'] ?? ''
    ]);
    header("Location: audit.php".($queryParams ? '?'.http_build_query($queryParams) : '')."#dashboard");
    exit();
}

// Determine filters
$filters = array_merge($_GET, $_POST);

// Handle CSV download
if (!empty($_GET['download'])) {
    $result = $conn->query(buildFilterSQL($conn, $filters)) or die($conn->error);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=users_data.csv');
    $out = fopen('php://output','w');
    fputcsv($out, ['ID','First Name','Last Name','Email','Phone','Designation','Department','Status']);
    while($row=$result->fetch_assoc()) fputcsv($out, [$row['id'],$row['fname'],$row['lname'],$row['email'],$row['phone'],$row['desi'],$row['dept'],$row['status']]);
    fclose($out);
    $conn->close();
    exit();
}

// Normal page display
$result = $conn->query(buildFilterSQL($conn, $filters));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Auditor Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../audit/audit.css">
</head>
<body>

<div class="sidebar">
    <h2>Auditor</h2>
    <a href="#" onclick="showSection('dashboard')">Dashboard</a>
</div>

<div class="main-content">
    <section id="dashboard" class="active">
        <h2>Dashboard</h2>
        <form method="POST" class="filters">
            <label>ID: <input type="text" name="id" value="<?= htmlspecialchars($filters['id'] ?? '') ?>"></label>
            <label>Category:
                <select name="category">
                    <option value="">All</option>
                    <?php foreach(['Admin','Manager','Employee','Auditor'] as $cat): ?>
                        <option value="<?= $cat ?>" <?= ($filters['category'] ?? '')==$cat?'selected':'' ?>><?= $cat ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Status:
                <select name="status">
                    <option value="">All</option>
                    <?php foreach(['Approved','Held'] as $status): ?>
                        <option value="<?= $status ?>" <?= ($filters['status'] ?? '')==$status?'selected':'' ?>><?= $status ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Date From: <input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>"></label>
            <label>Date To: <input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>"></label>
            <button type="submit">Filter</button>
            <a href="audit.php?download=1&<?= http_build_query($_GET) ?>"><button type="button">Download Excel</button></a>
        </form>

        <table id="usersTable">
            <thead>
                <tr>
                    <th>ID</th><th>First Name</th><th>Last Name</th><th>Email</th>
                    <th>Phone</th><th>Designation</th><th>Department</th>
                    <th>Status</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows): ?>
                <?php while($row=$result->fetch_assoc()): ?>
                    <tr class="<?= $row['status']=='Held'?'held':'' ?>">
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['fname']) ?></td>
                        <td><?= htmlspecialchars($row['lname']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td><?= htmlspecialchars($row['phone']) ?></td>
                        <td><?= htmlspecialchars($row['desi']) ?></td>
                        <td><?= htmlspecialchars($row['dept']) ?></td>
                        <td><?= htmlspecialchars($row['status']) ?></td>
                        <td>
                            <?php if($row['status']!='Held'): ?>
                                <form method="POST">
                                    <input type="hidden" name="userId" value="<?= $row['id'] ?>">
                                    <?php foreach(['id','category','status','date_from','date_to'] as $f): ?>
                                        <input type="hidden" name="<?= $f ?>" value="<?= htmlspecialchars($filters[$f] ?? '') ?>">
                                    <?php endforeach; ?>
                                    <button type="submit" class="hold" name="holdTransaction" onclick="holdTransaction(this)">Hold</button>
                                </form>
                            <?php else: ?>Held<?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="9">No users found</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </section>
</div>

<script>
function showSection(id){
    document.querySelectorAll('section').forEach(s=>s.classList.add('inactive'));
    document.getElementById(id).classList.remove('inactive');
}
function holdTransaction(btn){
    const row = btn.closest('tr');
    row.classList.add('held');
    row.cells[7].textContent='Held';
    btn.disabled=true;
    btn.textContent='Held';
}
</script>
</body>
</html>
<?php $conn->close(); ?>
