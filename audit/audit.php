<?php
include 'confdb.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Auditor Dashboard</title>
  <link rel="stylesheet" href="../audit/audit.css">
</head>

<body>
<div class="container">

  <!-- Sidebar -->
  <div class="menu">
    <div class="menu-header">
      <h2>Auditor</h2>
      <div class="menu-toggle" onclick="toggleMenu()">
        <ion-icon name="ellipsis-vertical-outline"></ion-icon>
      </div>
    </div>
    <a href="#" onclick="showSection('dashboard')"><ion-icon name="home-outline"></ion-icon> Dashboard</a>
    <a href="#" onclick="showSection('search')"><ion-icon name="search-outline"></ion-icon> Search</a>
    <a href="#" onclick="showSection('reports')"><ion-icon name="document-text-outline"></ion-icon> Reports</a>
    <a href="#" onclick="showSection('accounts')"><ion-icon name="person-outline"></ion-icon> Accounts</a>
  </div>

  <!-- Content -->
  <div class="content">

    <!-- Dashboard Section -->
    <div id="dashboard" class="content-section active">
      <h1>Dashboard</h1>

      <div class="filters">
        <label>Date From: <input type="date" id="dateFromDashboard"></label>
        <label>Date To: <input type="date" id="dateToDashboard"></label>
        <button onclick="filterByDate('Dashboard')">Search by Date</button>

        <label>Category:
          <select id="categoryFilterDashboard" onchange="filterByCategory('Dashboard')">
            <option value="">All</option>
            <option value="Admin">Admin</option>
            <option value="Manager">Manager</option>
            <option value="Employee">Employee</option>
            <option value="Auditor">Auditor</option>
          </select>
        </label>

        <label>Status:
          <select id="statusFilterDashboard" onchange="filterByStatus('Dashboard')">
            <option value="">All</option>
            <option value="Approved">Approved</option>
            <option value="Held">Held</option>
          </select>
        </label>

        <label>ID: <input type="text" id="idFilterDashboard" placeholder="Enter ID" onkeyup="filterByID('Dashboard')"></label>
      </div>

      <div id="summaryDashboard">
        <p>Total Income: $<span id="totalIncomeDashboard">0</span></p>
        <p>Total Expenses: $<span id="totalExpenseDashboard">0</span></p>
        <p>Total Savings: $<span id="totalSavingsDashboard">0</span></p>
        <p>Total Official: $<span id="totalOfficialDashboard">0</span></p>
        <button onclick="downloadExcel('transactionTableDashboard')">Download Report</button>
      </div>

      <table id="transactionTableDashboard" class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Designation</th>
            <th>Department</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $sql = "SELECT id, fname, lname, email, phone, desi, dept, status FROM users";
          $result = $conn->query($sql);
          if ($result && $result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                  echo "<tr>";
                  echo "<td>" . htmlspecialchars($row["id"]) . "</td>";
                  echo "<td>" . htmlspecialchars($row["fname"]) . "</td>";
                  echo "<td>" . htmlspecialchars($row["lname"]) . "</td>";
                  echo "<td>" . htmlspecialchars($row["email"]) . "</td>";
                  echo "<td>" . htmlspecialchars($row["phone"]) . "</td>";
                  echo "<td>" . htmlspecialchars($row["desi"]) . "</td>";
                  echo "<td>" . htmlspecialchars($row["dept"]) . "</td>";
                  echo "<td>" . htmlspecialchars($row["status"]) . "</td>";
                  echo "<td><button onclick=\"holdTransaction(this)\">Hold</button></td>";
                  echo "</tr>";
              }
          } else {
              echo "<tr><td colspan='9'>No users found</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>

    <!-- Search Section -->
    <div id="search" class="content-section">
      <h1>Search Section</h1>
      <p>Filter and search transactions here.</p>
    </div>

    <!-- Reports Section -->
    <div id="reports" class="content-section">
      <h1>Reports Section</h1>
      <p>Generate and download audit reports here.</p>
    </div>

    <!-- Accounts Section -->
    <div id="accounts" class="content-section">
      <h1>Accounts Section</h1>
      <p>Manage user accounts here.</p>
    </div>

  </div>
</div>

<!-- Ionicons -->
<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

<script>
// JS functions from your config.js
function toggleMenu() { document.querySelector('.menu').classList.toggle('active'); }
function showSection(sectionId) {
  document.querySelectorAll('.content-section').forEach(sec => sec.classList.remove('active'));
  document.getElementById(sectionId).classList.add('active');
}
function holdTransaction(btn) {
  const row = btn.closest('tr');
  row.style.backgroundColor = 'rgba(254, 17, 17, 0.7)';
  row.cells[7].textContent = 'Held';
  updateSummary('transactionTableDashboard', 'totalIncomeDashboard', 'totalExpenseDashboard', 'totalSavingsDashboard', 'totalOfficialDashboard');
}
// ... keep all other JS functions like filterByCategory, filterByStatus, filterByDate, updateSummary, filterByID, downloadExcel
</script>

</body>
</html>
