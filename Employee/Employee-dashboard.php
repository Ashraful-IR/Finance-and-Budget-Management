<?php
session_start();
if (!isset($_SESSION['employee_id'])) {
  $_SESSION['employee_id'] = 1;
}
if (!isset($_SESSION['dept_id'])) {
  $_SESSION['dept_id'] = 1;
}

$employee_id = (int)$_SESSION['employee_id'];
$dept_id     = (int)$_SESSION['dept_id'];

function json_out($data, int $code = 200){
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  header('Cache-Control: no-store, max-age=0');
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
  exit;
}

function s($v){ return isset($v) ? trim((string)$v) : null; }

function bind_params_dynamic(mysqli_stmt $stmt, string $types, array $params): void {
  $refs = [];
  foreach ($params as $k => &$v) { $refs[$k] = &$v; } 
  $stmt->bind_param($types, ...$refs);
}

if (isset($_GET['action'])) {
  include "configdb.php"; 
  $a = $_GET['action'];

  if ($a === 'list') {
    $q      = s($_GET['q'] ?? '');
    $status = s($_GET['status'] ?? '');
    $page   = max(1, (int)($_GET['page'] ?? 1));
    $limit  = max(1, min(200, (int)($_GET['limit'] ?? 50)));
    $offset = ($page - 1) * $limit;

    $sql    = "SELECT expense_code, date, amount, category, reason, status
               FROM expenses
               WHERE employee_id=?";
    $types  = "i";
    $params = [$employee_id];

    if ($q !== '') {
      $sql .= " AND (
        expense_code LIKE CONCAT('%', ?, '%') OR
        reason       LIKE CONCAT('%', ?, '%') OR
        category     LIKE CONCAT('%', ?, '%') OR
        status       LIKE CONCAT('%', ?, '%') OR
        CAST(amount AS CHAR) LIKE CONCAT('%', ?, '%') OR
        DATE_FORMAT(date, '%Y-%m-%d') LIKE CONCAT('%', ?, '%')
      )";
      $types  .= "ssssss";
      $params[] = $q; $params[] = $q; $params[] = $q; $params[] = $q; $params[] = $q; $params[] = $q;
    }
    if ($status !== '') {
      $sql   .= " AND status=?";
      $types .= "s";
      $params[] = $status;
    }

    $sql .= " ORDER BY date DESC, id DESC LIMIT ? OFFSET ?";
    $types  .= "ii";
    $params[] = $limit;
    $params[] = $offset;

    $stmt = $conn->prepare($sql);
    if (!$stmt) json_out(['ok'=>false, 'error'=>$conn->error], 500);
    bind_params_dynamic($stmt, $types, $params);
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
    json_out(['ok'=>true, 'data'=>$rows, 'page'=>$page, 'limit'=>$limit]);
  }

  if ($a === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $eid      = (int)($_POST['employee_id'] ?? $employee_id);
    $did      = (int)($_POST['dept_id'] ?? $dept_id);
    $amount   = isset($_POST['amount']) ? (float)$_POST['amount'] : null;
    $category = s($_POST['category'] ?? null);
    $reason   = s($_POST['reason'] ?? null);
    $date     = s($_POST['date'] ?? null);
    $status   = s($_POST['status'] ?? 'Pending');

    if (!$eid || !$did || $amount === null || $amount < 0 || !$category || !$reason || !$date) {
      json_out(['ok'=>false, 'error'=>'Missing or invalid fields'], 422);
    }

    $expense_code = 'REQ-' . strtoupper(bin2hex(random_bytes(3)));

    $stmt = $conn->prepare("INSERT INTO expenses
      (employee_id, dept_id, expense_code, amount, category, reason, status, date)
      VALUES (?,?,?,?,?,?,?,?)");
    if (!$stmt) json_out(['ok'=>false, 'error'=>$conn->error], 500);

    $stmt->bind_param("iisdssss", $eid, $did, $expense_code, $amount, $category, $reason, $status, $date);
    $ok = $stmt->execute();
    $id = $stmt->insert_id;
    $err = $ok ? null : $stmt->error;
    $stmt->close();

    json_out(['ok'=>$ok, 'id'=>$id, 'expense_code'=>$expense_code, 'error'=>$err]);
  }

  if ($a === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = s($_POST['expense_code'] ?? null);
    if (!$code) json_out(['ok'=>false, 'error'=>'expense_code required'], 422);

    $set = []; $types = ""; $params = [];

    if (isset($_POST['date']))     { $set[] = "date=?";     $types.="s"; $params[] = (string)$_POST['date']; }
    if (isset($_POST['amount']))   { $set[] = "amount=?";   $types.="d"; $params[] = (float)$_POST['amount']; }
    if (isset($_POST['category'])) { $set[] = "category=?"; $types.="s"; $params[] = s($_POST['category']); }
    if (isset($_POST['reason']))   { $set[] = "reason=?";   $types.="s"; $params[] = s($_POST['reason']); }
    if (isset($_POST['status']))   { $set[] = "status=?";   $types.="s"; $params[] = s($_POST['status']); }

    if (!$set) json_out(['ok'=>false, 'error'=>'No fields to update'], 422);

    $sql = "UPDATE expenses SET ".implode(",", $set)." WHERE expense_code=? AND employee_id=?";
    $types .= "si"; $params[] = $code; $params[] = $employee_id;

    $stmt = $conn->prepare($sql);
    if (!$stmt) json_out(['ok'=>false, 'error'=>$conn->error], 500);
    bind_params_dynamic($stmt, $types, $params);
    $ok = $stmt->execute();
    $affected = $stmt->affected_rows;
    $err = $ok ? null : $stmt->error;
    $stmt->close();

    json_out(['ok'=>$ok, 'affected'=>$affected, 'error'=>$err]);
  }

  if ($a === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = s($_POST['expense_code'] ?? null);
    if (!$code) json_out(['ok'=>false, 'error'=>'expense_code required'], 422);

    $stmt = $conn->prepare("DELETE FROM expenses WHERE expense_code=? AND employee_id=?");
    if (!$stmt) json_out(['ok'=>false, 'error'=>$conn->error], 500);
    $stmt->bind_param("si", $code, $employee_id);
    $ok = $stmt->execute();
    $affected = $stmt->affected_rows;
    $err = $ok ? null : $stmt->error;
    $stmt->close();

    json_out(['ok'=>$ok, 'affected'=>$affected, 'error'=>$err]);
  }

  if ($a === 'stats') {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM expenses WHERE employee_id=? AND status='Pending'");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $stmt->bind_result($pending);
    $stmt->fetch();
    $stmt->close();

    $dept = ['total_income'=>0.0, 'total_expenses'=>0.0, 'net_balance'=>0.0];
    $stmt = $conn->prepare("SELECT total_income, total_expenses FROM departments WHERE id=? LIMIT 1");
    $stmt->bind_param("i", $dept_id);
    $stmt->execute();
    $stmt->bind_result($inc, $exp);
    if ($stmt->fetch()) {
      $dept['total_income']   = (float)$inc;
      $dept['total_expenses'] = (float)$exp;
      $dept['net_balance']    = (float)$inc - (float)$exp;
    }
    $stmt->close();

    $salary = ['annual_base'=>0.0, 'monthly_net'=>0.0, 'last_paid'=>null];
    $stmt = $conn->prepare("SELECT annual_base, monthly_net, last_paid
                            FROM salaries WHERE employee_id=? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $stmt->bind_result($ab, $mn, $lp);
    if ($stmt->fetch()) {
      $salary = ['annual_base'=>(float)$ab, 'monthly_net'=>(float)$mn, 'last_paid'=>$lp];
    }
    $stmt->close();

    json_out(['ok'=>true, 'pending'=>$pending, 'department'=>$dept, 'salary'=>$salary]);
  }

  if ($a === 'summary') {
    $stmt = $conn->prepare("SELECT category, SUM(amount) AS total_amount
                            FROM expenses
                            WHERE employee_id=? AND status='Approved'
                            GROUP BY category ORDER BY total_amount DESC");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $stmt->bind_result($cat, $tot);

    $rows = []; $grand = 0.0;
    while ($stmt->fetch()) {
      $rows[] = ['category'=>$cat, 'total_amount'=>(float)$tot];
      $grand += (float)$tot;
    }
    $stmt->close();

    foreach ($rows as &$r) {
      $r['share_percent'] = $grand > 0 ? round(($r['total_amount']*100)/$grand, 2) : 0.0;
    }
    json_out(['ok'=>true, 'total'=>$grand, 'rows'=>$rows]);
  }

  if ($a === 'get_account') {
    $stmt = $conn->prepare("SELECT name, email FROM employees WHERE id=? LIMIT 1");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $stmt->bind_result($name, $email);
    $profile = null;
    if ($stmt->fetch()) $profile = ['name'=>$name, 'email'=>$email];
    $stmt->close();
    json_out(['ok'=>true, 'profile'=>$profile]);
  }

  if ($a === 'account_update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = s($_POST['name'] ?? '');
    $email = s($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    if (!$name || !$email) json_out(['ok'=>false, 'error'=>'Missing fields'], 422);

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

    json_out(['ok'=>$ok, 'affected'=>$affected, 'error'=>$err]);
  }

  if ($a === 'forecast') {
    $stmt = $conn->prepare("SELECT COALESCE(SUM(amount),0)
                            FROM expenses
                            WHERE employee_id=? AND date >= (CURRENT_DATE - INTERVAL 30 DAY)");
    $stmt->bind_param("i", $employee_id);
    $stmt->execute();
    $stmt->bind_result($sum30);
    $stmt->fetch();
    $stmt->close();

    $avgDaily = ((float)$sum30) / 30.0;
    $proj     = round($avgDaily * 30.0, 2);
    json_out(['ok'=>true, 'avg_daily'=>round($avgDaily,2), 'projected_next_month'=>$proj]);
  }

  if ($a === 'logout') {
    $_SESSION = [];
    if (session_id() !== '') session_destroy();
    header("Location: ../Login/login.php");
    exit;
  }

  json_out(['ok'=>false, 'error'=>'Unknown action'], 404);
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

      <section id="submit-request" class="panel is-visible">
        <div class="card">
          <h2>Submit a New Expense</h2>
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
                <option>Travel</option>
                <option>Meals</option>
                <option>Training</option>
                <option>Equipment</option>
                <option>Other</option>
              </select>
            </div>

            <div class="form-field">
              <label for="exp-date">Date</label>
              <input id="exp-date" name="date" type="date" required />
            </div>

            <div class="form-field">
              <label for="exp-status">Initial Status</label>
              <select id="exp-status" name="status">
                <option>Pending</option>
                <option>Approved</option>
                <option>Rejected</option>
              </select>
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

      <section id="forecasting" class="panel">
        <div class="card">
          <h2>Expense Forecasting Tool</h2>
          <div class="grid-3 gap-16">
            <div class="mini-card"><span class="mini-label">Avg. Daily Spend (30d)</span><span class="mini-value" id="avg-daily">$0.00</span></div>
            <div class="mini-card"><span class="mini-label">Projected Next Month</span><span class="mini-value" id="proj-next">$0.00</span></div>
            <div class="mini-card"><span class="mini-label">Within Budget?</span><span class="tag success" id="within-budget">Yes</span></div>
          </div>
        </div>
      </section>

      <section id="request-status" class="panel">
        <div class="card">
          <h2>My Expense Requests</h2>
          <div class="toolbar">
            <div class="toolbar-left">
              <div class="search-wrap"><i class="ri-search-line"></i><input id="search-input" type="text" placeholder="Search by ID, reason, category, status." /></div>
              <select id="status-filter" class="filter">
                <option value="">All Statuses</option><option>Pending</option><option>Approved</option><option>Rejected</option>
              </select>
            </div>
          </div>
          <div class="table-wrap mt-16">
            <table id="requests-table">
              <thead>
                <tr><th>ID</th><th>Date</th><th>Amount</th><th>Category</th><th>Reason</th><th>Status</th><th>Actions</th></tr>
              </thead>
              <tbody id="requests-tbody"></tbody>
            </table>
          </div>
        </div>
      </section>

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

      <section id="account-management" class="panel">
        <div class="card">
          <h2>Account Management</h2>
          <form id="account-form" class="grid-2 gap-16" autocomplete="off">
            <div class="form-field"><label for="account-name">Name</label><input id="account-name" name="name" type="text" required /></div>
            <div class="form-field"><label for="account-email">Email</label><input id="account-email" name="email" type="email" required /></div>
            <div class="form-field grid-full"><label for="account-password">Change Password</label><input id="account-password" name="password" type="password" placeholder="New password (optional)" /></div>
            <div class="form-actions grid-full"><button class="btn primary" type="submit"><i class="ri-save-line"></i> Save Changes</button></div>
          </form>
        </div>
      </section>
    </main>
  </div>

  <script>
    const API = window.location.pathname.replace(/\/+$/, '');
    const EMPLOYEE_ID = <?php echo (int)$employee_id; ?>;
    const DEPT_ID = <?php echo (int)$dept_id; ?>;

    async function fetchJSON(url, options = {}) {
      const res = await fetch(url, { credentials: 'same-origin', ...options });
      const text = await res.text();
      try { return JSON.parse(text); }
      catch { throw new Error('Invalid JSON from server:\n' + text); }
    }

    const $  = sel => document.querySelector(sel);
    const $$ = sel => Array.from(document.querySelectorAll(sel));

    $$('.nav-item').forEach(btn => {
      btn.addEventListener('click', () => {
        if (btn.id === 'logout-btn') { window.location.href = API + '?action=logout'; return; }
        $$('.nav-item').forEach(b => b.classList.remove('is-active'));
        btn.classList.add('is-active');
        $$('.panel').forEach(p => p.classList.remove('is-visible'));
        const target = btn.getAttribute('data-target');
        if (target) document.getElementById(target).classList.add('is-visible');
        $('#page-title').textContent = btn.textContent.trim();

        if (target === 'request-status')      loadExpenses();
        if (target === 'salary-record' ||
            target === 'dept-balance')        loadStats();
        if (target === 'spending-summary')    loadSummary();
        if (target === 'account-management')  loadAccount();
        if (target === 'forecasting')         loadForecast();
      });
    });

    $('#expense-form').addEventListener('submit', async e => {
      e.preventDefault();
      const fd = new FormData(e.target);
      fd.set('employee_id', EMPLOYEE_ID);
      fd.set('dept_id', DEPT_ID);
      try {
        const j = await fetchJSON(API+'?action=add', { method:'POST', body: fd });
        alert('Expense submitted: ' + j.expense_code);
        e.target.reset();
        if ($('#exp-date')) $('#exp-date').value = new Date().toISOString().slice(0,10);
        loadExpenses(); loadStats(); loadSummary();
      } catch (err) { alert('Submit failed: ' + err.message); }
    });

    async function loadExpenses(){
      const q = ($('#search-input')||{}).value?.trim?.() || '';
      const status = ($('#status-filter')||{}).value || '';
      const tbody = $('#requests-tbody');
      if (!tbody) return;
      tbody.innerHTML = '<tr><td colspan="7">Loading…</td></tr>';
      try {
        const j = await fetchJSON(API+'?action=list&q='+encodeURIComponent(q)+'&status='+encodeURIComponent(status));
        tbody.innerHTML = '';
        if (j.data && j.data.length) {
          j.data.forEach(r => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${r.expense_code}</td><td>${r.date}</td><td>$${r.amount.toFixed(2)}</td><td>${r.category}</td><td>${r.reason}</td><td>${r.status}</td><td></td>`;
            tbody.appendChild(tr);
          });
        } else {
          tbody.innerHTML = '<tr><td colspan="7">No results.</td></tr>';
        }
      } catch (err) {
        tbody.innerHTML = '<tr><td colspan="7">Failed to load.</td></tr>';
      }
    }

    async function loadStats(){
      try {
        const j = await fetchJSON(API+'?action=stats');
        $('#stat-pending').textContent = j.pending ?? 0;
        $('#salary-annual').textContent = '$'+((j.salary?.annual_base)||0).toFixed(2);
        $('#salary-monthly').textContent = '$'+((j.salary?.monthly_net)||0).toFixed(2);
        $('#salary-lastpaid').textContent = j.salary?.last_paid || '—';
        $('#total-income').textContent = '$'+((j.department?.total_income)||0).toFixed(2);
        $('#total-expenses').textContent = '$'+((j.department?.total_expenses)||0).toFixed(2);
        $('#net-balance').textContent = '$'+((j.department?.net_balance)||0).toFixed(2);
        $('#stat-salary').textContent = '$'+((j.salary?.monthly_net)||0).toFixed(2);
        $('#stat-balance').textContent = '$'+((j.department?.net_balance)||0).toFixed(2);
      } catch {}
    }

    async function loadSummary(){
      const tbody = $('#summary-tbody');
      if (!tbody) return;
      tbody.innerHTML = '<tr><td colspan="3">Loading…</td></tr>';
      try {
        const j = await fetchJSON(API+'?action=summary');
        tbody.innerHTML = '';
        if (j.rows && j.rows.length){
          j.rows.forEach(r => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>${r.category}</td><td>$${r.total_amount.toFixed(2)}</td><td>${r.share_percent}%</td>`;
            tbody.appendChild(tr);
          });
        } else {
          tbody.innerHTML = '<tr><td colspan="3">No approved spending yet.</td></tr>';
        }
      } catch {
        tbody.innerHTML = '<tr><td colspan="3">Failed to load.</td></tr>';
      }
    }

    async function loadAccount(){
      try {
        const j = await fetchJSON(API+'?action=get_account');
        if (j.profile) {
          $('#account-name').value  = j.profile.name  || '';
          $('#account-email').value = j.profile.email || '';
        }
      } catch {}
    }

    async function loadForecast(){
      try {
        const j = await fetchJSON(API+'?action=forecast');
        $('#avg-daily').textContent = '$'+(j.avg_daily||0).toFixed(2);
        $('#proj-next').textContent = '$'+(j.projected_next_month||0).toFixed(2);
      } catch {}
    }

    (function init(){
      if ($('#exp-date')) $('#exp-date').value = new Date().toISOString().slice(0,10);
      loadStats(); loadExpenses(); loadSummary();
      document.getElementById('go-to-table')?.addEventListener('click', () => {
        document.querySelector('[data-target="request-status"]').click();
      });
      document.getElementById('account-form')?.addEventListener('submit', async e => {
        e.preventDefault();
        const fd = new FormData(e.target);
        try {
          await fetchJSON(API+'?action=account_update', { method:'POST', body: fd });
          alert('Account updated');
        } catch (err) { alert('Save failed: ' + err.message); }
      });
    })();
  </script>
</body>
</html>
