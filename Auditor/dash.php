<?php
// Show errors for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include DB config
include "config.php";

// Ensure DB connection exists
if (!isset($conn)) die("Database connection not found.");

// Build SQL from filters
function buildFilterSQL($conn, $data) {
    $sql = "SELECT * FROM users WHERE 1=1";

    $mapping = [
        'id' => fn($v) => "id=" . (int)$v,
        'category' => fn($v) => "desi='" . $conn->real_escape_string($v) . "'",
        'status' => fn($v) => "status='" . $conn->real_escape_string($v) . "'"
    ];

    foreach ($mapping as $key => $func) {
        if (!empty($data[$key])) $sql .= " AND " . $func($data[$key]);
    }

    return $sql;
}

// Handle "Hold" action
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['holdTransaction'])) {
    $userId = (int)($_POST['userId'] ?? 0);
    if ($userId > 0) {
        $conn->query("UPDATE users SET status='Held' WHERE id=$userId");
    }

    // Preserve filters after status update
    $queryParams = array_filter([
        'id' => $_POST['id'] ?? '',
        'category' => $_POST['category'] ?? '',
        'status' => $_POST['status'] ?? ''
    ]);

    header("Location: dash.php" . ($queryParams ? '?' . http_build_query($queryParams) : '') . "#dashboard");
    exit();
}

// Get filter data
$filters = array_merge($_GET, $_POST);

// CSV download logic
if (!empty($_GET['download'])) {
    $result = $conn->query(buildFilterSQL($conn, $filters)) or die($conn->error);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=users_data.csv');

    $out = fopen('php://output', 'w');
    fputcsv($out, ['ID', 'First Name', 'Last Name', 'Email', 'Phone', 'Designation', 'Department', 'Status']);

    while ($row = $result->fetch_assoc()) {
        fputcsv($out, [
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

    fclose($out);
    $conn->close();
    exit();
}

// Load records for page into array
$res = $conn->query(buildFilterSQL($conn, $filters));
$users = [];
if ($res && $res->num_rows) {
    while ($row = $res->fetch_assoc()) {
        $users[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Auditor Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../Auditor/dash.css">
</head>
<body>

<div class="sidebar">
    <h2>Auditor</h2>
    <a href="#" onclick="showSection('dashboard')">Dashboard</a>
</div>

<div class="main-content">
    <section id="dashboard" class="active">
        <h2>Dashboard</h2>

        <!-- Hidden filters/options + full table -->
        <div id="dashboardOptions" style="display:none;">
            <form method="POST" class="filters">
                <label>ID:
                    <input type="text" name="id" value="<?= htmlspecialchars($filters['id'] ?? '') ?>">
                </label>
                <label>Category:
                    <select name="category">
                        <option value="">All</option>
                        <?php foreach (['Admin', 'Manager', 'Employee', 'Auditor'] as $cat): ?>
                            <option value="<?= $cat ?>" <?= ($filters['category'] ?? '') === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>Status:
                    <select name="status">
                        <option value="">All</option>
                        <?php foreach (['Approved', 'Held'] as $status): ?>
                            <option value="<?= $status ?>" <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>><?= $status ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>

                <button type="submit">Filter</button>

                <?php
                $downloadFilters = $filters;
                unset($downloadFilters['date_from'], $downloadFilters['date_to']);
                ?>
                <a href="dash.php?download=1&<?= http_build_query($downloadFilters) ?>" class="download-btn">Download Excel</a>
            </form>

            <!-- Full table -->
            <table id="usersTableFull">
                <thead>
                    <tr>
                        <th>ID</th><th>First Name</th><th>Last Name</th><th>Email</th>
                        <th>Phone</th><th>Designation</th><th>Department</th>
                        <th>Status</th><th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $row): ?>
                        <tr class="<?= $row['status'] === 'Held' ? 'held' : '' ?>">
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['fname']) ?></td>
                            <td><?= htmlspecialchars($row['lname']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['phone']) ?></td>
                            <td><?= htmlspecialchars($row['desi']) ?></td>
                            <td><?= htmlspecialchars($row['dept']) ?></td>
                            <td><?= htmlspecialchars($row['status']) ?></td>
                            <td>
                                <?php if ($row['status'] !== 'Held'): ?>
                                    <form method="POST" style="margin:0;">
                                        <input type="hidden" name="userId" value="<?= $row['id'] ?>">
                                        <?php foreach (['id', 'category', 'status'] as $f): ?>
                                            <input type="hidden" name="<?= $f ?>" value="<?= htmlspecialchars($filters[$f] ?? '') ?>">
                                        <?php endforeach; ?>
                                        <button type="submit" name="holdTransaction" onclick="holdTransaction(this)">Hold</button>
                                    </form>
                                <?php else: ?>
                                    Held
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="9">No users found</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Minimal table (default view) -->
        <table id="usersTableMinimal">
            <thead>
                <tr>
                    <th>ID</th><th>Name</th><th>Department</th><th>Email</th>
                </tr>
            </thead>
            <tbody>
            <?php if (!empty($users)): ?>
                <?php foreach ($users as $row): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['fname'] . " " . $row['lname']) ?></td>
                        <td><?= htmlspecialchars($row['dept']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4">No users found</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </section>
</div>

<script>
function showSection(id) {
    document.querySelectorAll('section').forEach(s => s.classList.add('inactive'));
    document.getElementById(id).classList.remove('inactive');

    if (id === 'dashboard') {
        // Hide minimal, show full view
        document.getElementById('usersTableMinimal').style.display = 'none';
        document.getElementById('dashboardOptions').style.display = 'block';
    }
}

function holdTransaction(btn) {
    const row = btn.closest('tr');
    row.classList.add('held');
    row.cells[7].textContent = 'Held';
    btn.disabled = true;
    btn.textContent = 'Held';
}
</script>

</body>
</html>

<?php $conn->close(); ?>
