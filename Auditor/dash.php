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

    if (!empty($data['id'])) {
        $sql .= " AND id=" . (int)$data['id'];
    }

    if (!empty($data['category'])) {
        $sql .= " AND desi='" . $conn->real_escape_string($data['category']) . "'";
    }

    if (!empty($data['status'])) {
        $sql .= " AND status='" . $conn->real_escape_string($data['status']) . "'";
    }

    return $sql;
}

// Handle "Hold" action (AJAX safe)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['holdTransaction'])) {
    $userId = (int)($_POST['userId'] ?? 0);
    if ($userId > 0) {
        if (!$conn->query("UPDATE users SET status='Held' WHERE id=$userId")) {
            http_response_code(500);
            echo "DB update failed: " . $conn->error;
            exit;
        }
        exit("OK");
    }
}

// Load users for Dashboard & User Credentials
$filters = $_POST ?? [];
$res = $conn->query(buildFilterSQL($conn, $filters));
$users = [];
$usersList = [];
if ($res && $res->num_rows) {
    while ($row = $res->fetch_assoc()) {
        $users[] = $row;
        $usersList[] = $row;
    }
}

// Load expenses for Hidden Info
$expensesRes = $conn->query("SELECT * FROM expenses");
$expenses = [];
$totalAmount = 0;
if ($expensesRes && $expensesRes->num_rows) {
    while ($row = $expensesRes->fetch_assoc()) {
        $expenses[] = $row;
        $totalAmount += floatval($row['amount']);
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
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<style>
/* --- Extra fixes --- */
.hold-btn {
    background-color: #007BFF;
    color: white;
    border: none;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    font-family: 'Poppins', sans-serif;
}
.hold-btn:hover { background-color: #0056b3; }
tr.held { background-color: #f8d7da !important; color: #721c24 !important; font-weight: 500; }
td.status-cell.held-text { font-weight: bold; color: #b91c1c; }

.filters-row {
    display: flex;
    align-items: flex-end;
    gap: 10px;
    margin-bottom: 15px;
}
.filters-row .filters {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}
.download-btn {
    display: inline-block;
    background-color: #007bff;
    color: #fff;
    padding: 10px 18px;
    border-radius: 6px;
    text-decoration: none;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
    box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
    border: none;
    cursor: pointer;
    height: fit-content;
}
</style>
</head>
<body>

<div class="sidebar">
    <h2>Auditor</h2>
    <a href="#" onclick="showSection('dashboard')">Dashboard</a>
    <a href="#" onclick="showSection('Hiddeninfo')">Hidden Info</a>
    <a href="#" onclick="showSection('UserInfo')">ID PASS</a>
</div>

<div class="main-content">
<!-- Dashboard Section -->
<section id="dashboard" class="active">
<h2>Dashboard</h2>
<div id="dashboardOptions" style="display:none;">
    <div class="filters-row">
        <form id="filterForm" class="filters">
            <label>ID:
                <input type="text" name="id" value="<?= htmlspecialchars($filters['id'] ?? '', ENT_QUOTES) ?>">
            </label>
            <label>Category:
                <select name="category">
                    <option value="">All</option>
                    <?php foreach(['Admin','Manager','Employee','Auditor'] as $cat): ?>
                    <option value="<?= $cat ?>" <?= ($filters['category'] ?? '') === $cat ? 'selected' : '' ?>><?= $cat ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Status:
                <select name="status">
                    <option value="">All</option>
                    <?php foreach(['Approved','Held'] as $status): ?>
                    <option value="<?= $status ?>" <?= ($filters['status'] ?? '') === $status ? 'selected' : '' ?>><?= $status ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button type="submit">Filter</button>
        </form>
        <button class="download-btn" id="downloadBtn">Download CSV</button>
    </div>
    

    <div id="usersTableFullContainer">
        <table id="usersTableFull">
            <thead>
                <tr>
                    <th>ID</th><th>First Name</th><th>Last Name</th><th>Email</th>
                    <th>Phone</th><th>Designation</th><th>Department</th>
                    <th>Status</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if(!empty($users)): ?>
                <?php foreach($users as $row): ?>
                <tr class="<?= $row['status']==='Held' ? 'held':'' ?>">
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['fname'] ?? '', ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['lname'] ?? '', ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['email'] ?? '', ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['phone'] ?? '', ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['desi'] ?? '', ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['dept'] ?? '', ENT_QUOTES) ?></td>
                    <td class="status-cell <?= $row['status']==='Held' ? 'held-text':'' ?>">
                        <?= htmlspecialchars($row['status'] ?? '', ENT_QUOTES) ?>
                    </td>
                    <td>
                        <?php if(($row['status'] ?? '')!=='Held'): ?>
                        <button type="button" class="hold-btn" data-id="<?= $row['id'] ?>">Hold</button>
                        <?php else: ?><span class="held-text">Held</span><?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="9">No users found</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<table id="usersTableMinimal">
<thead>
<tr>
<th>ID</th><th>Name</th><th>Department</th><th>Email</th>
</tr>
</thead>
<tbody>
<?php if(!empty($users)): ?>
<?php foreach($users as $row): ?>
<tr>
<td><?= $row['id'] ?></td>
<td><?= htmlspecialchars(($row['fname']??'')." ".($row['lname']??''), ENT_QUOTES) ?></td>
<td><?= htmlspecialchars($row['dept']??'', ENT_QUOTES) ?></td>
<td><?= htmlspecialchars($row['email']??'', ENT_QUOTES) ?></td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="4">No users found</td></tr>
<?php endif; ?>
</tbody>
</table>
</section>

<!-- Hidden Info Section -->
<section id="Hiddeninfo" class="inactive">
<h2>Hidden Info (Expenses)</h2>
<table>
<thead>
<tr>
<th>ID</th><th>Date</th><th>Expense Code</th><th>Amount</th><th>Category</th><th>Status</th>
</tr>
</thead>
<tbody>
<?php if(!empty($expenses)): ?>
<?php foreach($expenses as $row): ?>
<tr>
<td><?= $row['id'] ?></td>
<td><?= htmlspecialchars($row['date']??'', ENT_QUOTES) ?></td>
<td><?= htmlspecialchars($row['expense_code']??'', ENT_QUOTES) ?></td>
<td><?= htmlspecialchars($row['amount']??'', ENT_QUOTES) ?></td>
<td><?= htmlspecialchars($row['category']??'', ENT_QUOTES) ?></td>
<td><?= htmlspecialchars($row['status']??'', ENT_QUOTES) ?></td>
</tr>
<?php endforeach; ?>
<tr>
<td colspan="3"><strong>Total Amount</strong></td>
<td colspan="3"><strong><?= number_format($totalAmount,2) ?></strong></td>
</tr>
<?php else: ?>
<tr><td colspan="6">No expenses found</td></tr>
<?php endif; ?>
</tbody>
</table>
</section>

<!-- User Credentials Section -->
<section id="UserInfo" class="inactive">
<h2>User Credentials (Email & Password)</h2>
<table>
<thead><tr><th>Email</th><th>Password</th></tr></thead>
<tbody>
<?php if(!empty($usersList)): ?>
<?php foreach($usersList as $u): ?>
<tr>
<td><?= htmlspecialchars($u['email']??'', ENT_QUOTES) ?></td>
<td><?= htmlspecialchars($u['pass']??'', ENT_QUOTES) ?></td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr><td colspan="2">No users found</td></tr>
<?php endif; ?>
</tbody>
</table>
</section>
</div>

<script>
function showSection(id){
    document.querySelectorAll('section').forEach(s=>s.classList.add('inactive'));
    document.querySelectorAll('section').forEach(s=>s.classList.remove('active'));
    document.getElementById(id).classList.remove('inactive');
    document.getElementById(id).classList.add('active');

    if(id==='dashboard'){
        document.getElementById('usersTableMinimal').style.display='none';
        document.getElementById('dashboardOptions').style.display='block';
    } else {
        document.getElementById('dashboardOptions').style.display='none';
        document.getElementById('usersTableMinimal').style.display='none';
    }
}

// AJAX "Hold" button
$(document).on('click', '.hold-btn', function(e){
    e.preventDefault();
    const $btn = $(this);
    const userId = $btn.data('id');
    $.post('dash.php', { holdTransaction: 1, userId: userId }, function(){
        $('#filterForm').submit(); // reload table from DB
    }).fail(function(xhr){
        alert("Error: " + xhr.responseText);
    });
});

// AJAX Filter Submission
$('#filterForm').on('submit', function(e){
    e.preventDefault();
    $.post('dash.php', $(this).serialize(), function(data){
        const newTable = $(data).find('#usersTableFullContainer').html();
        $('#usersTableFullContainer').html(newTable);
    });
});

// Download CSV
$('#downloadBtn').on('click', function(){
    const params = $('#filterForm').serialize();
    window.location = 'download.php?' + params;
});
</script>

</body>
</html>
<?php $conn->close(); ?>
