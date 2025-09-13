<?php 
// Show errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include DB config
include "config.php";
if (!isset($conn)) die("Database connection not found.");

// Handle Hold / Approve action (AJAX)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['updateStatus'])) {
    $userId = (int)($_POST['userId'] ?? 0);
    $status = $_POST['status'] ?? 'Active';
    if ($userId > 0) {
        if (!$conn->query("UPDATE audit SET status='$status' WHERE id=$userId")) {
            http_response_code(500);
            echo "DB update failed: " . $conn->error;
            exit;
        }
        exit("OK");
    }
}

// Load audit data
$where = "1=1";
if (!empty($_POST['id'])) {
    $id = (int)$_POST['id'];
    $where .= " AND id = $id";
}

$res = $conn->query("SELECT id, username, name, phone, account_number, credit, debit, balance, email, password, status FROM audit WHERE $where");
$users = [];
if ($res && $res->num_rows) {
    while ($row = $res->fetch_assoc()) {
        $users[] = $row;
    }
}

// Load expenses (Hidden info)
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
<div id="dashboardOptions" style="display:block;">
    <div class="filters-row">
        <form id="filterForm" class="filters">
            <label>ID:
                <input type="text" name="id">
            </label>
            <button type="submit" class="filter-btn">Filter</button>
        </form>
        <button class="download-btn" id="downloadBtn">Download CSV</button>
    </div>

    <div id="usersTableFullContainer">
        <table id="usersTableFull">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Account Number</th>
                    <th>Credit</th>
                    <th>Debit</th>
                    <th>Balance</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if(!empty($users)): ?>
                <?php foreach($users as $row): ?>
                <tr class="<?= ($row['status'] ?? '')==='Held' ? 'held' : '' ?>">
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['name'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['phone'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['account_number'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['credit'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['debit'], ENT_QUOTES) ?></td>
                    <td><?= htmlspecialchars($row['balance'], ENT_QUOTES) ?></td>
                    <td class="status-cell <?= ($row['status'] ?? '')==='Held' ? 'held-text':'' ?>">
                        <?= htmlspecialchars($row['status'] ?? 'Active', ENT_QUOTES) ?>
                    </td>
                    <td>
                        <?php if(($row['status'] ?? '')==='Held'): ?>
                            <button type="button" class="hold-btn approve-btn" data-id="<?= $row['id'] ?>" data-action="approve">Approve</button>
                        <?php else: ?>
                            <button type="button" class="hold-btn" data-id="<?= $row['id'] ?>" data-action="hold">Hold</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="9">No records found</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</section>

<!-- Hidden Info Section -->
<section id="Hiddeninfo" class="inactive">
<h2>Hidden Info (Transactions)</h2>

<!-- Date Filter -->
<div class="filters-row">
    <form id="transactionFilterForm" class="filters" method="get">
        <label>From:
            <input type="date" name="from_date" value="<?= htmlspecialchars($_GET['from_date'] ?? '', ENT_QUOTES) ?>">
        </label>
        <label>To:
            <input type="date" name="to_date" value="<?= htmlspecialchars($_GET['to_date'] ?? '', ENT_QUOTES) ?>">
        </label>
        <button type="submit">Filter</button>
    </form>
</div>

<?php
// Build filter query
$where = "1=1";
if (!empty($_GET['from_date'])) {
    $from = $conn->real_escape_string($_GET['from_date']);
    $where .= " AND transaction_date >= '$from'";
}
if (!empty($_GET['to_date'])) {
    $to = $conn->real_escape_string($_GET['to_date']);
    $where .= " AND transaction_date <= '$to'";
}

// Fetch transactions
$transactionsRes = $conn->query("SELECT name, account_number, debit, credit, balance, transaction_id, transaction_date FROM audit WHERE $where");

$totalBalance = 0;
$transactions = [];
if ($transactionsRes && $transactionsRes->num_rows) {
    while($row = $transactionsRes->fetch_assoc()) {
        $totalBalance += floatval($row['balance']);
        $transactions[] = $row;
    }
}
?>

<table>
<thead>
<tr>
<th>Name</th>
<th>Account Number</th>
<th>Debit</th>
<th>Credit</th>
<th>Balance</th>
<th>Transaction ID</th>
<th>Transaction Date</th>
</tr>
</thead>
<tbody>
<?php if(!empty($transactions)): ?>
    <?php foreach($transactions as $row): ?>
<tr>
<td><?= htmlspecialchars($row['name'], ENT_QUOTES) ?></td>
<td><?= htmlspecialchars($row['account_number'], ENT_QUOTES) ?></td>
<td><?= htmlspecialchars($row['debit'], ENT_QUOTES) ?></td>
<td><?= htmlspecialchars($row['credit'], ENT_QUOTES) ?></td>
<td><?= htmlspecialchars($row['balance'], ENT_QUOTES) ?></td>
<td><?= htmlspecialchars($row['transaction_id'], ENT_QUOTES) ?></td>
<td><?= htmlspecialchars($row['transaction_date'], ENT_QUOTES) ?></td>
</tr>
    <?php endforeach; ?>
<tr>
<td colspan="4"><strong>Total Balance</strong></td>
<td colspan="3"><strong><?= number_format($totalBalance, 2) ?></strong></td>
</tr>
<?php else: ?>
<tr><td colspan="7">No transactions found</td></tr>
<?php endif; ?>
<script>
// Check URL params to stay on Hidden Info
document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    if(params.has('from_date') || params.has('to_date')){
        showSection('Hiddeninfo');
    }
});
</script>

</tbody>
</table>
</section>


<!-- User Credentials Section -->
<section id="UserInfo" class="inactive">
<h2>User Credentials (Username, Email & Password)</h2>
<table>
<thead>
<tr>
    <th>Username</th>
    <th>Email</th>
    <th>Password</th>
</tr>
</thead>
<tbody>
<?php if(!empty($users)): ?>
    <?php foreach($users as $u): ?>
    <tr>
        <td><?= htmlspecialchars($u['username'] ?? '', ENT_QUOTES) ?></td>
        <td><?= htmlspecialchars($u['email'] ?? '', ENT_QUOTES) ?></td>
        <td><?= htmlspecialchars($u['password'] ?? '', ENT_QUOTES) ?></td>
    </tr>
    <?php endforeach; ?>
<?php else: ?>
<tr><td colspan="3">No records found</td></tr>
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
        document.getElementById('dashboardOptions').style.display='block';
    } else {
        document.getElementById('dashboardOptions').style.display='none';
    }
}

// AJAX Hold/Approve
$(document).on('click', '.hold-btn', function(e){
    e.preventDefault();
    const $btn = $(this);
    const userId = $btn.data('id');
    const action = $btn.data('action');
    const newStatus = action === 'hold' ? 'Held' : 'Active';

    $.post('dash.php', { updateStatus: 1, userId: userId, status: newStatus }, function(){
        location.reload(); // reload table
    }).fail(function(xhr){
        alert("Error: " + xhr.responseText);
    });
});

// Filter form
$('#filterForm').on('submit', function(e){
    e.preventDefault();
    $.post('dash.php', $(this).serialize(), function(data){
        const newTable = $(data).find('#usersTableFullContainer').html();
        $('#usersTableFullContainer').html(newTable);
    });
});

// Download CSV
$('#downloadBtn').on('click', function(){
    window.location = 'download.php';
});
</script>

</body>
</html>
<?php $conn->close(); ?>
