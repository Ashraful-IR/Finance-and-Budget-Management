<?php
// employee-dashboard.php
// ----------------------------------------------------------------------------
// Server-side: same-file JSON API + SPA (HTML+JS)
// This version avoids mysqli::get_result() so it works without mysqlnd.
// ----------------------------------------------------------------------------
include "configdb.php";
session_start();

// Demo: use session employee id if present; otherwise fall back to 1
$employee_id = isset($_SESSION['employee_id']) ? (int)$_SESSION['employee_id'] : 1;
$dept_id     = 1;

// ---------- Helper: JSON response ----------
function send_json($payload, int $code = 200) {
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  header('Cache-Control: no-store, max-age=0');
  echo json_encode($payload);
  exit;
}

// ---------- Helper: safe string ----------
function str_or_null($v) { return isset($v) ? trim((string)$v) : null; }

// ---------- Same-file API ----------
if (isset($_GET['action'])) {
  $a = $_GET['action'];

  // ---- Expenses: list/search ----
  if ($a === 'list') {
    $q      = isset($_GET['q']) ? trim($_GET['q']) : '';
    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
    $page   = max(1, (int)($_GET['page'] ?? 1));
    $limit  = max(1, min(100, (int)($_GET['limit'] ?? 100)));
    $offset = ($page - 1) * $limit;

    $sql = "SELECT expense_code, date, amount, category, reason, status
            FROM expenses
            WHERE employee_id=?";

    $whereTypes = "i";
    $whereParams = [$employee_id];

    if ($q !== '') {
      $sql .= " AND (
        expense_code LIKE CONCAT('%', ?, '%')
        OR reason LIKE CONCAT('%', ?, '%')
        OR category LIKE CONCAT('%', ?, '%')
        OR status LIKE CONCAT('%', ?, '%')
        OR CAST(amount AS CHAR) LIKE CONCAT('%', ?, '%')
        OR DATE_FORMAT(date, '%Y-%m-%d') LIKE CONCAT('%', ?, '%')
      )";
      $whereTypes .= "ssssss";
      array_push($whereParams, $q, $q, $q, $q, $q, $q);
    }

    if ($status !== '') {
      $sql .= " AND status = ?";
      $whereTypes .= "s";
      $whereParams[] = $status;
    }

    $sql .= " ORDER BY date DESC, id DESC LIMIT ? OFFSET ?";
    $whereTypes .= "ii";
    array_push($whereParams, $limit, $offset);

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($whereTypes, ...$whereParams);
    $stmt->execute();
    $stmt->bind_result($expense_code, $date, $amount, $category, $reason, $row_status);

    $rows = [];
    while ($stmt->fetch()) {
      $rows[] = [
        'expense_code' => $expense_code,
        'date'         => $date,
        'amount'       => (float)$amount,
        'category'     => $category,
        'reason'       => $reason,
        'status'       => $row_status
      ];
    }
    $stmt->close();

    send_json(['ok' => true, 'data' => $rows, 'page' => $page, 'limit' => $limit]);
  }

  // ---- Expenses: add ----
  if ($a === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $eid      = (int)($_POST['employee_id'] ?? $employee_id);
    $did      = isset($_POST['dept_id']) ? (int)$_POST['dept_id'] : $dept_id;
    $amount   = isset($_POST['amount']) ? (float)$_POST['amount'] : null;
    $category = str_or_null($_POST['category'] ?? null);
    $reason   = str_or_null($_POST['reason'] ?? null);
    $date     = str_or_null($_POST['date'] ?? null);
    $status   = str_or_null($_POST['status'] ?? 'Pending');

    if (!$eid || !$amount || !$category || !$reason || !$date) {
      send_json(['ok'=>false, 'error'=>'Missing fields'], 422);
    }

    $expense_code = 'REQ-' . strtoupper(bin2hex(random_bytes(3)));
    $stmt = $conn->prepare("INSERT INTO expenses (employee_id, dept_id, expense_code, amount, category, reason, status, date)
                            VALUES (?,?,?,?,?,?,?,?)");
    $stmt->bind_param("iisdssss", $eid, $did, $expense_code, $amount, $category, $reason, $status, $date);

    $ok = $stmt->execute();
    $insert_id = $stmt->insert_id;
    $err = $ok ? null : $stmt->error;
    $stmt->close();

    send_json(['ok'=>$ok, 'expense_code'=>$expense_code, 'id'=>$insert_id, 'error'=>$err]);
  }

  // ---- Expenses: update ----
  if ($a === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = str_or_null($_POST['expense_code'] ?? null);
    if (!$code) send_json(['ok'=>false, 'error'=>'expense_code required'], 422);

    $fields = [];
    $params = [];
    $types  = "";

    if (isset($_POST['date']))     { $fields[]="date=?";     $params[] = str_or_null($_POST['date']);     $types.="s"; }
    if (isset($_POST['amount']))   { $fields[]="amount=?";   $params[] = (float)$_POST['amount'];          $types.="d"; }
    if (isset($_POST['category'])) { $fields[]="category=?"; $params[] = str_or_null($_POST['category']); $types.="s"; }
    if (isset($_POST['reason']))   { $fields[]="reason=?";   $params[] = str_or_null($_POST['reason']);   $types.="s"; }
    if (isset($_POST['status']))   { $fields[]="status=?";   $params[] = str_or_null($_POST['status']);   $types.="s"; }

    if (!$fields) send_json(['ok'=>false, 'error'=>'No fields to update']);

    $sql = "UPDATE expenses SET ".implode(",", $fields)." WHERE expense_code=? AND employee_id=?";
    $types .= "si";
    $params[] = $code;
    $params[] = $employee_id;

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $ok = $stmt->execute();
    $affected = $stmt->affected_rows;
    $err = $ok ? null : $stmt->error;
    $stmt->close();

    send_json(['ok'=>$ok, 'affected'=>$affected, 'error'=>$err]);
  }

  // ---- Expenses: delete ----
  if ($a === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = str_or_null($_POST['expense_code'] ?? null);
    if (!$code) send_json(['ok'=>false, 'error'=>'expense_code required'], 422);

    $stmt = $conn->prepare("DELETE FROM expenses WHERE expense_code=? AND employee_id=?");
    $stmt->bind_param("si", $code, $employee_id);
    $ok = $stmt->execute();
    $affected = $stmt->affected_rows;
    $err = $ok ? null : $stmt->error;
    $stmt->close();

    send_json(['ok'=>$ok, 'affected'=>$affected, 'error'=>$err]);
  }

  // ---- Stats: salary + department + pending ----
  if ($a === 'stats') {
    // Pending
    $pending = 0;
    $stmt = $conn->prepare("SELECT COUNT(*) FROM expenses WHERE employee_id=? AND status='Pending'");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $stmt->bind_result($pending);
    $stmt->fetch();
    $stmt->close();

    // Department
    $dept = ['total_income'=>0.0, 'total_expenses'=>0.0, 'net_balance'=>0.0];
    $stmt = $conn->prepare("SELECT total_income, total_expenses FROM departments WHERE id=?");
    $stmt->bind_param("i", $dept_id);
    $stmt->execute();
    $stmt->bind_result($inc, $exp);
    if ($stmt->fetch()) {
      $dept = [
        'total_income'   => (float)$inc,
        'total_expenses' => (float)$exp,
        'net_balance'    => (float)$inc - (float)$exp
      ];
    }
    $stmt->close();

    // Salary (latest)
    $salary = ['annual_base'=>0.0, 'monthly_net'=>0.0, 'last_paid'=>null];
    $stmt = $conn->prepare("SELECT annual_base, monthly_net, last_paid
                            FROM salaries WHERE employee_id=? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $stmt->bind_result($ab, $mn, $lp);
    if ($stmt->fetch()) {
      $salary = [
        'annual_base' => (float)$ab,
        'monthly_net' => (float)$mn,
        'last_paid'   => $lp
      ];
    }
    $stmt->close();

    send_json(['ok'=>true, 'pending'=>$pending, 'department'=>$dept, 'salary'=>$salary]);
  }

  // ---- Spending summary ----
  if ($a === 'summary') {
    $stmt = $conn->prepare("SELECT category, SUM(amount) AS total_amount
                            FROM expenses
                            WHERE employee_id=? AND status='Approved'
                            GROUP BY category
                            ORDER BY total_amount DESC");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $stmt->bind_result($category, $total_amount);

    $rows = [];
    $total = 0.0;
    while ($stmt->fetch()) {
      $rows[] = ['category'=>$category, 'total_amount'=>(float)$total_amount];
      $total += (float)$total_amount;
    }
    $stmt->close();

    // compute share
    foreach ($rows as &$r) {
      $r['share_percent'] = $total > 0 ? round(($r['total_amount'] * 100.0) / $total, 2) : 0.0;
    }

    send_json(['ok'=>true, 'total'=>$total, 'rows'=>$rows]);
  }

  // ---- Account: read ----
  if ($a === 'get_account') {
    $stmt = $conn->prepare("SELECT name, email FROM employees WHERE id=? LIMIT 1");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $stmt->bind_result($name, $email);
    $profile = null;
    if ($stmt->fetch()) {
      $profile = ['name'=>$name, 'email'=>$email];
    }
    $stmt->close();
    send_json(['ok'=>true, 'profile'=>$profile]);
  }

  // ---- Account: update ----
  if ($a === 'account_update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = str_or_null($_POST['name'] ?? '');
    $email = str_or_null($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if (!$name || !$email) send_json(['ok'=>false, 'error'=>'Missing fields'], 422);

    if ($pass !== '') {
      $hash = password_hash($pass, PASSWORD_DEFAULT);
      $stmt = $conn->prepare("UPDATE employees SET name=?, email=?, password_hash=? WHERE id=?");
      $stmt->bind_param("sssi", $name, $email, $hash, $employee_id);
    } else {
      $stmt = $conn->prepare("UPDATE employees SET name=?, email=? WHERE id=?");
      $stmt->bind_param("ssi", $name, $email, $employee_id);
    }

    $ok = $stmt->execute();
    $affected = $stmt->affected_rows;
    $err = $ok ? null : $stmt->error;
    $stmt->close();

    send_json(['ok'=>$ok, 'affected'=>$affected, 'error'=>$err]);
  }

  // ---- Forecast (30-day) ----
  if ($a === 'forecast') {
    $stmt = $conn->prepare("SELECT COALESCE(SUM(amount),0) FROM expenses
                            WHERE employee_id=? AND date >= (CURRENT_DATE - INTERVAL 30 DAY)");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $stmt->bind_result($sum30);
    $stmt->fetch();
    $stmt->close();

    $avgDaily = ((float)$sum30) / 30.0;
    $projection = round($avgDaily * 30.0, 2);

    send_json(['ok'=>true, 'avg_daily'=>round($avgDaily,2), 'projected_next_month'=>round($projection,2)]);
  }

  // ---- Logout ----
  if ($a === 'logout') {
    $_SESSION = [];
    if (session_id() !== '') session_destroy();
    header("Location: login.html");
    exit;
  }

  // Unknown action
  send_json(['ok'=>false, 'error'=>'Unknown action'], 404);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Employee Dashboard – Finance & Budget</title>
  <link rel="stylesheet" href="employee-dashboard.css" />
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">
  <style>
    /* Safety: some minimal layout fallbacks if CSS is missing */
    body { font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin:0; }
    .app-shell { display:flex; min-height:100vh; }
    .sidebar { width:260px; background:#0f172a; color:#fff; display:flex; flex-direction:column; }
    .brand { display:flex; align-items:center; gap:8px; padding:16px; font-weight:700; }
    .nav { display:flex; flex-direction:column; gap:2px; padding:8px; }
    .nav .nav-item { text-align:left; border:0; background:transparent; color:#cbd5e1; padding:10px 14px; border-radius:8px; cursor:pointer; }
    .nav .nav-item.is-active { background:#1e293b; color:#fff; }
    .sidebar-footer { margin-top:auto; padding:10px 14px; opacity:.7; }
    .content { flex:1; background:#f8fafc; }
    .content-header { display:flex; justify-content:space-between; align-items:center; padding:18px 24px; border-bottom:1px solid #e2e8f0; }
    .quick-stats { display:flex; gap:16px; }
    .stat { background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:8px 12px; }
    .stat-label { display:block; font-size:12px; color:#64748b; }
    .stat-value { font-weight:700; }
    .panel { display:none; padding:24px; }
    .panel.is-visible { display:block; }
    .card { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:16px; }
    .grid-2 { display:grid; grid-template-columns: repeat(2, minmax(0,1fr)); }
    .grid-3 { display:grid; grid-template-columns: repeat(3, minmax(0,1fr)); }
    .grid-full { grid-column:1/-1; }
    .gap-16 { gap:16px; }
    .mt-16 { margin-top:16px; }
    .muted { color:#64748b; }
    .btn { border:1px solid #cbd5e1; background:#f1f5f9; border-radius:8px; padding:8px 12px; cursor:pointer; }
    .btn.primary { background:#2563eb; color:#fff; border-color:#2563eb; }
    .btn.ghost { background:transparent; }
    .btn.small { padding:6px 10px; font-size:12px; }
    .icon-btn { border:0; background:transparent; cursor:pointer; padding:4px 6px; }
    .icon-btn.danger { color:#dc2626; }
    .table-wrap { overflow:auto; }
    table { width:100%; border-collapse:collapse; }
    th, td { padding:10px; border-bottom:1px solid #e2e8f0; text-align:left; }
    .text-right { text-align:right; }
    .mini-card { background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:10px; }
    .mini-label { display:block; font-size:12px; color:#64748b; }
    .mini-value { font-weight:700; }
    .tag { display:inline-block; font-size:12px; padding:2px 8px; border-radius:999px; border:1px solid; }
    .tag.success { color:#15803d; border-color:#15803d; background:#ecfdf5; }
    .tag.warn { color:#a16207; border-color:#a16207; background:#fefce8; }
    .tag.danger { color:#b91c1c; border-color:#b91c1c; background:#fef2f2; }
    .chart-placeholder { display:flex; align-items:center; justify-content:center; height:220px; background:#f1f5f9; border:1px dashed #cbd5e1; border-radius:8px; color:#64748b; }
    input.inrow, select.inrow { width:100%; padding:6px 8px; }
    .search-wrap { display:flex; align-items:center; gap:8px; padding:6px 10px; border:1px solid #e2e8f0; border-radius:8px; background:#fff; }
    .filter { padding:6px 10px; border:1px solid #e2e8f0; border-radius:8px; background:#fff; }
    .toolbar { display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; }
    .toolbar-left { display:flex; gap:8px; align-items:center; }
    .banner.success { display:flex; gap:10px; align-items:center; padding:10px; border:1px solid #86efac; border-radius:8px; background:#f0fdf4; color:#166534; }
  </style>
</head>
<body>
  <div class="app-shell">
    <aside class="sidebar" aria-label="Employee features">
      <div class="brand"><i class="ri-wallet-3-line"></i><span>Employee Dashboard</span></div>
      <nav class="nav">
        <button type="button" class="nav-item is-active" data-target="submit-request"><i class="ri-upload-2-line"></i><span> Submit Expense</span></button>
        <button type="button" class="nav-item" data-target="forecasting"><i class="ri-line-chart-line"></i><span> Forecasting Tool</span></button>
        <button type="button" class="nav-item" data-target="request-status"><i class="ri-time-line"></i><span> Request Status</span></button>
        <button type="button" class="nav-item" data-target="salary-record"><i class="ri-bank-card-line"></i><span> Salary Record</span></button>
        <button type="button" class="nav-item" data-target="dept-balance"><i class="ri-community-line"></i><span> Department Balance</span></button>
        <button type="button" class="nav-item" data-target="spending-summary"><i class="ri-pie-chart-2-line"></i><span> Spending Summary</span></button>
        <button type="button" class="nav-item" data-target="account-management"><i class="ri-user-settings-line"></i><span> Account Management</span></button>
        <button type="button" class="nav-item" id="logout-btn"><i class="ri-logout-box-r-line"></i><span> Logout</span></button>
      </nav>
      <div class="sidebar-footer"><small>Finance & Budget © 2025</small></div>
    </aside>

    <main class="content">
      <header class="content-header">
        <h1 id="page-title">Submit Expense</h1>
        <div class="quick-stats">
          <div class="stat"><span class="stat-label">Salary (mo)</span><span id="stat-salary" class="stat-value">$0.00</span></div>
          <div class="stat"><span class="stat-label">Dept. Balance</span><span id="stat-balance" class="stat-value">$0.00</span></div>
          <div class="stat"><span class="stat-label">Pending Reqs</span><span id="stat-pending" class="stat-value">0</span></div>
        </div>
      </header>

      <!-- Submit Expense -->
      <section id="submit-request" class="panel is-visible">
        <div class="card">
          <h2>Submit a New Expense</h2>
          <p class="muted">Fill the form and submit for manager approval.</p>
          <form id="expense-form" class="grid-2 gap-16" autocomplete="off">
            <input type="hidden" name="employee_id" value="<?php echo (int)$employee_id; ?>">
            <input type="hidden" name="dept_id" value="<?php echo (int)$dept_id; ?>">
            <div class="form-field">
              <label for="exp-amount">Amount (USD)</label>
              <input id="exp-amount" name="amount" type="number" min="0" step="0.01" required />
            </div>
            <div class="form-field">
              <label for="exp-category">Category</label>
              <select id="exp-category" name="category" required>
                <option value="">Select category</option>
                <option>Travel</option><option>Meals</option><option>Training</option><option>Equipment</option><option>Other</option>
              </select>
            </div>
            <div class="form-field">
              <label for="exp-date">Date</label>
              <input id="exp-date" name="date" type="date" required />
            </div>
            <div class="form-field">
              <label for="exp-status">Initial Status</label>
              <select id="exp-status" name="status"><option>Pending</option><option>Approved</option><option>Rejected</option></select>
            </div>
            <div class="form-field grid-full">
              <label for="exp-reason">Reason</label>
              <textarea id="exp-reason" name="reason" rows="4" required></textarea>
            </div>
            <div class="form-actions grid-full">
              <button class="btn primary" type="submit"><i class="ri-send-plane-2-line"></i> Submit Request</button>
              <button class="btn ghost" type="reset">Clear</button>
              <button class="btn" type="button" id="go-to-table"><i class="ri-list-check"></i> Go to Request List</button>
            </div>
          </form>
        </div>
      </section>

      <!-- Forecasting -->
      <section id="forecasting" class="panel">
        <div class="card">
          <h2>Expense Forecasting Tool</h2>
          <div class="grid-3 gap-16">
            <div class="mini-card"><span class="mini-label">Avg. Daily Spend (30d)</span><span class="mini-value" id="avg-daily">$0.00</span></div>
            <div class="mini-card"><span class="mini-label">Projected Next Month</span><span class="mini-value" id="proj-next">$0.00</span></div>
            <div class="mini-card"><span class="mini-label">Within Budget?</span><span class="tag success" id="within-budget">Yes</span></div>
          </div>
          <div class="chart-placeholder mt-16"><span>Line chart placeholder (Actual vs Forecast)</span></div>
        </div>
      </section>

      <!-- Request Status -->
      <section id="request-status" class="panel">
        <div class="card">
          <h2>My Expense Requests</h2>
          <div class="toolbar">
            <div class="toolbar-left">
              <div class="search-wrap"><i class="ri-search-line"></i><input id="search-input" type="text" placeholder="Search by ID, reason, category, status..." /></div>
              <select id="status-filter" class="filter"><option value="">All Statuses</option><option>Pending</option><option>Approved</option><option>Rejected</option></select>
            </div>
            <div class="toolbar-right"><button class="btn" id="reset-filters" type="button"><i class="ri-refresh-line"></i> Reset</button></div>
          </div>
          <div class="table-wrap mt-16">
            <table id="requests-table">
              <thead><tr><th>ID</th><th>Date</th><th>Amount</th><th>Category</th><th>Reason</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
              <tbody id="requests-tbody"></tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- Salary -->
      <section id="salary-record" class="panel">
        <div class="card">
          <h2>Salary Record</h2>
          <div class="grid-3 gap-16">
            <div class="mini-card"><span class="mini-label">Base (Annual)</span><span class="mini-value" id="salary-annual">$0.00</span></div>
            <div class="mini-card"><span class="mini-label">Monthly Net</span><span class="mini-value" id="salary-monthly">$0.00</span></div>
            <div class="mini-card"><span class="mini-label">Last Paid</span><span class="mini-value" id="salary-lastpaid">—</span></div>
          </div>
        </div>
      </section>

      <!-- Department Balance -->
      <section id="dept-balance" class="panel">
        <div class="card">
          <h2>Department Balance</h2>
          <div class="grid-2 gap-16">
            <div class="mini-card"><span class="mini-label">Total Income</span><span class="mini-value" id="total-income">$0.00</span></div>
            <div class="mini-card"><span class="mini-label">Total Expenses</span><span class="mini-value" id="total-expenses">$0.00</span></div>
          </div>
          <div class="banner success mt-16"><i class="ri-checkbox-circle-line"></i><span>Net Balance: <strong id="net-balance">$0.00</strong></span></div>
        </div>
      </section>

      <!-- Spending Summary -->
      <section id="spending-summary" class="panel">
        <div class="card">
          <h2>Personal Spending Summary</h2>
          <div class="table-wrap mt-16">
            <table>
              <thead><tr><th>Category</th><th>Amount</th><th>Share</th></tr></thead>
              <tbody id="summary-tbody"></tbody>
            </table>
          </div>
        </div>
      </section>

      <!-- Account Management -->
      <section id="account-management" class="panel">
        <div class="card">
          <h2>Account Management</h2>
          <form id="account-form" class="grid-2 gap-16" autocomplete="off">
            <div class="form-field"><label for="account-name">Name</label><input id="account-name" name="name" type="text" required /></div>
            <div class="form-field"><label for="account-email">Email</label><input id="account-email" name="email" type="email" required /></div>
            <div class="form-field grid-full"><label for="account-password">Change Password</label><input id="account-password" name="password" type="password" placeholder="Enter new password (optional)" /></div>
            <div class="form-actions grid-full"><button class="btn primary" type="submit"><i class="ri-save-line"></i> Save Changes</button><button class="btn ghost" type="reset">Cancel</button></div>
          </form>
        </div>
      </section>
    </main>
  </div>

  <script>
    // Same-file endpoints
    const API = window.location.pathname.replace(/\/+$/, '');
    const EMPLOYEE_ID = <?php echo (int)$employee_id; ?>;
    const DEPT_ID = <?php echo (int)$dept_id; ?>;

    // ---------- Helpers ----------
    async function fetchJSON(url, opts = {}) {
      const res = await fetch(url, { credentials: 'same-origin', ...opts });
      let text = await res.text();
      try {
        const json = JSON.parse(text);
        if (!res.ok) throw Object.assign(new Error(json.error || 'Request failed'), { status: res.status, json });
        return json;
      } catch (e) {
        if (!(e instanceof SyntaxError)) throw e;
        // Non-JSON (PHP notice/HTML) – surface full text for debugging
        throw Object.assign(new Error('Invalid JSON response'), { status: res.status, body: text });
      }
    }
    function tag(s){ const cls = s==='Approved'?'success':s==='Rejected'?'danger':'warn'; return `<span class="tag ${cls}">${s}</span>`; }
    function escapeHtml(str){
      const map = { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' };
      return String(str).replace(/[&<>"']/g, ch => map[ch]);
    }
    function escapeAttr(str){ // for attribute values
      return escapeHtml(str);
    }

    // ---------- NAV + ROUTED LOADERS ----------
    document.querySelectorAll('.nav-item').forEach(btn => {
      btn.addEventListener('click', async () => {
        if (btn.id === 'logout-btn') { window.location.href = API + '?action=logout'; return; }

        document.querySelectorAll('.nav-item').forEach(b => b.classList.remove('is-active'));
        btn.classList.add('is-active');
        document.querySelectorAll('.panel').forEach(p => p.classList.remove('is-visible'));

        const target = btn.getAttribute('data-target');
        if (target) document.getElementById(target).classList.add('is-visible');
        document.getElementById('page-title').textContent = btn.textContent.trim();

        // Lazy-load per panel
        if (target === 'request-status') { loadExpenses(); }
        if (target === 'salary-record' || target === 'dept-balance') { loadStats(); }
        if (target === 'spending-summary') { loadSummary(); }
        if (target === 'account-management') { loadAccount(); }
        if (target === 'forecasting') { loadForecast(); }
      });
    });

    document.getElementById('go-to-table')?.addEventListener('click', () => {
      document.querySelector('.nav-item.is-active')?.classList.remove('is-active');
      const btn = document.querySelector('.nav-item[data-target="request-status"]');
      btn.classList.add('is-active');
      document.querySelectorAll('.panel').forEach(p => p.classList.remove('is-visible'));
      document.getElementById('request-status').classList.add('is-visible');
      document.getElementById('page-title').textContent = 'Request Status';
      loadExpenses();
    });

    // ---------- SUBMIT EXPENSE ----------
    document.getElementById('expense-form').addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(e.target);
      fd.set('employee_id', EMPLOYEE_ID);
      fd.set('dept_id', DEPT_ID);
      try {
        const j = await fetchJSON(API+'?action=add', { method:'POST', body: fd });
        if (j.ok) {
          alert('Expense submitted: ' + j.expense_code);
          e.target.reset();
          const today = new Date().toISOString().slice(0,10);
          document.getElementById('exp-date').value = today;
          loadExpenses(); loadStats(); loadSummary();
        } else {
          alert(j.error || 'Failed to submit');
        }
      } catch (err) {
        console.error(err);
        alert('Submit failed. Check server logs.');
      }
    });

    // ---------- ACCOUNT ----------
    async function loadAccount(){
      try {
        const j = await fetchJSON(API+'?action=get_account');
        if (j.ok && j.profile){
          document.getElementById('account-name').value  = j.profile.name || '';
          document.getElementById('account-email').value = j.profile.email || '';
        } else {
          document.getElementById('account-name').value = '';
          document.getElementById('account-email').value = '';
        }
      } catch (err) { console.error(err); }
    }
    document.getElementById('account-form').addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(e.target);
      try {
        const j = await fetchJSON(API+'?action=account_update', { method:'POST', body: fd });
        alert(j.ok ? 'Account updated' : (j.error || 'Failed to update'));
      } catch (err) {
        console.error(err);
        alert('Update failed. Check server logs.');
      }
    });

    // ---------- REQUEST STATUS (CRUD) ----------
    document.getElementById('search-input').addEventListener('input', () => loadExpenses());
    document.getElementById('status-filter').addEventListener('change', () => loadExpenses());
    document.getElementById('reset-filters').addEventListener('click', () => {
      document.getElementById('search-input').value = '';
      document.getElementById('status-filter').value = '';
      loadExpenses();
    });

    async function loadExpenses(){
      const q = document.getElementById('search-input').value.trim();
      const status = document.getElementById('status-filter').value;
      const url = API+'?action=list&q='+encodeURIComponent(q)+'&status='+encodeURIComponent(status);
      const tbody = document.getElementById('requests-tbody');
      tbody.innerHTML = '<tr><td colspan="7" class="muted">Loading…</td></tr>';
      try {
        const j = await fetchJSON(url);
        tbody.innerHTML = '';
        if (j.ok && j.data.length){
          j.data.forEach(r => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
              <td>${escapeHtml(r.expense_code)}</td>
              <td>${escapeHtml(r.date)}</td>
              <td>${Number(r.amount).toLocaleString(undefined,{style:'currency',currency:'USD'})}</td>
              <td>${escapeHtml(r.category)}</td>
              <td>${escapeHtml(r.reason)}</td>
              <td>${tag(r.status)}</td>
              <td class="text-right">
                <button class="icon-btn edit" data-code="${escapeAttr(r.expense_code)}" title="Edit"><i class="ri-edit-2-line"></i></button>
                <button class="icon-btn danger del" data-code="${escapeAttr(r.expense_code)}" title="Delete"><i class="ri-delete-bin-6-line"></i></button>
              </td>`;
            tbody.appendChild(tr);
          });
          attachRowHandlers();
        } else {
          tbody.innerHTML = `<tr><td colspan="7" class="muted">No matching requests.</td></tr>`;
        }
      } catch (err) {
        console.error(err);
        tbody.innerHTML = `<tr><td colspan="7" class="muted">Failed to load requests.</td></tr>`;
      }
    }

    function attachRowHandlers(){
      document.querySelectorAll('#requests-tbody .edit').forEach(btn => {
        btn.addEventListener('click', () => beginEdit(btn.closest('tr'), btn.dataset.code));
      });
      document.querySelectorAll('#requests-tbody .del').forEach(btn => {
        btn.addEventListener('click', async () => {
          if (!confirm('Delete '+btn.dataset.code+'?')) return;
          const fd = new FormData(); fd.append('expense_code', btn.dataset.code);
          try {
            const j = await fetchJSON(API+'?action=delete', { method:'POST', body: fd });
            if (j.ok){ loadExpenses(); loadStats(); loadSummary(); } else alert(j.error||'Delete failed');
          } catch (err) {
            console.error(err); alert('Delete failed. Check server logs.');
          }
        });
      });
    }

    function beginEdit(tr, code){
      const t = tr.querySelectorAll('td');
      const date = t[1].textContent.trim();
      const amount = t[2].textContent.replace(/[^0-9.\-]/g,'');
      const category = t[3].textContent.trim();
      const reason = t[4].textContent.trim();
      const status = t[5].textContent.trim();

      const categories = ['Travel','Meals','Training','Equipment','Other'];
      const statuses   = ['Pending','Approved','Rejected'];

      tr.innerHTML = `
        <td>${escapeHtml(code)}</td>
        <td><input type="date" class="inrow" value="${escapeAttr(date)}"></td>
        <td><input type="number" class="inrow" step="0.01" min="0" value="${escapeAttr(amount)}"></td>
        <td>
          <select class="inrow">
            ${categories.map(c => `<option ${c===category?'selected':''}>${c}</option>`).join('')}
          </select>
        </td>
        <td><input type="text" class="inrow" value="${escapeAttr(reason)}"></td>
        <td>
          <select class="inrow">
            ${statuses.map(s => `<option ${s===status?'selected':''}>${s}</option>`).join('')}
          </select>
        </td>
        <td class="text-right">
          <button class="btn small primary save"><i class="ri-check-line"></i> Save</button>
          <button class="btn small ghost cancel"><i class="ri-close-line"></i> Cancel</button>
        </td>`;

      tr.querySelector('.save').addEventListener('click', async () => {
        const [dateEl, amtEl, catEl, reasonEl, statusEl] = tr.querySelectorAll('.inrow');
        const fd = new FormData();
        fd.append('expense_code', code);
        fd.append('date', dateEl.value);
        fd.append('amount', amtEl.value);
        fd.append('category', catEl.value);
        fd.append('reason', reasonEl.value);
        fd.append('status', statusEl.value);
        try {
          const j = await fetchJSON(API+'?action=update', { method:'POST', body: fd });
          if (j.ok){ loadExpenses(); loadStats(); loadSummary(); } else alert(j.error||'Update failed');
        } catch (err) { console.error(err); alert('Update failed. Check server logs.'); }
      });
      tr.querySelector('.cancel').addEventListener('click', loadExpenses);
    }

    // ---------- STATS ----------
    async function loadStats(){
      try {
        const j = await fetchJSON(API+'?action=stats');
        if (j.ok){
          document.getElementById('stat-pending').textContent = j.pending ?? 0;

          document.getElementById('salary-annual').textContent  = (j.salary?.annual_base||0).toLocaleString(undefined,{style:'currency',currency:'USD'});
          document.getElementById('salary-monthly').textContent = (j.salary?.monthly_net||0).toLocaleString(undefined,{style:'currency',currency:'USD'});
          document.getElementById('salary-lastpaid').textContent= j.salary?.last_paid || '—';

          document.getElementById('total-income').textContent   = (j.department?.total_income||0).toLocaleString(undefined,{style:'currency',currency:'USD'});
          document.getElementById('total-expenses').textContent = (j.department?.total_expenses||0).toLocaleString(undefined,{style:'currency',currency:'USD'});
          document.getElementById('net-balance').textContent    = ((j.department?.net_balance)||0).toLocaleString(undefined,{style:'currency',currency:'USD'});

          document.getElementById('stat-salary').textContent    = (j.salary?.monthly_net||0).toLocaleString(undefined,{style:'currency',currency:'USD'});
          document.getElementById('stat-balance').textContent   = ((j.department?.net_balance)||0).toLocaleString(undefined,{style:'currency',currency:'USD'});
        }
      } catch (err) { console.error(err); }
    }

    // ---------- SUMMARY ----------
    async function loadSummary(){
      const tbody = document.getElementById('summary-tbody');
      tbody.innerHTML = '<tr><td colspan="3" class="muted">Loading…</td></tr>';
      try {
        const j = await fetchJSON(API+'?action=summary');
        tbody.innerHTML = '';
        if (j.ok && j.rows.length){
          j.rows.forEach(r => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${escapeHtml(r.category)}</td><td>${Number(r.total_amount).toLocaleString(undefined,{style:'currency',currency:'USD'})}</td><td>${r.share_percent}%</td>`;
            tbody.appendChild(tr);
          });
        } else {
          tbody.innerHTML = `<tr><td colspan="3" class="muted">No approved spending yet.</td></tr>`;
        }
      } catch (err) {
        console.error(err);
        tbody.innerHTML = `<tr><td colspan="3" class="muted">Failed to load summary.</td></tr>`;
      }
    }

    // ---------- FORECAST ----------
    async function loadForecast(){
      try {
        const j = await fetchJSON(API+'?action=forecast');
        if (j.ok){
          document.getElementById('avg-daily').textContent = Number(j.avg_daily||0).toLocaleString(undefined,{style:'currency',currency:'USD'});
          document.getElementById('proj-next').textContent = Number(j.projected_next_month||0).toLocaleString(undefined,{style:'currency',currency:'USD'});
          document.getElementById('within-budget').textContent = 'Yes';
        }
      } catch (err) { console.error(err); }
    }

    // ---------- INIT ----------
    function init(){
      const today = new Date().toISOString().slice(0,10);
      document.getElementById('exp-date').value = today;
      loadStats(); loadExpenses(); loadSummary(); // initial view (Submit Expense visible by default)
    }
    init();
  </script>
</body>
</html>
