<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Auditor Dashboard</title>
  <link rel="stylesheet" href="../audit/audit.css">
  <script defer src="../audit/config.js"></script>
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

        <!-- Filters -->
        <div class="filters">
          <label>Date From: <input type="date" id="dateFromDashboard"></label>
          <label>Date To: <input type="date" id="dateToDashboard"></label>
          <button onclick="filterByDate('Dashboard')">Search by Date</button>

          <label>Category:
            <select id="categoryFilterDashboard" onchange="filterByCategory('Dashboard')">
              <option value="">All</option>
              <option value="Income">Income</option>
              <option value="Expense">Expense</option>
              <option value="Savings">Savings</option>
              <option value="Official">Official</option>
            </select>
          </label>

          <label>Status:
            <select id="statusFilterDashboard" onchange="filterByStatus('Dashboard')">
              <option value="">All</option>
              <option value="Approved">Approved</option>
              <option value="Held">Held</option>
            </select>
          </label>
        </div>
        
        <label>
            ID: <input type="text" id="idFilterDashboard" placeholder="Enter ID" onkeyup="filterByID('Dashboard')">
        </label>



        <!-- Summary -->
        <div id="summaryDashboard">
          <p>Total Income: $<span id="totalIncomeDashboard">0</span></p>
          <p>Total Expenses: $<span id="totalExpenseDashboard">0</span></p>
          <p>Total Savings: $<span id="totalSavingsDashboard">0</span></p>
          <p>Total Official: $<span id="totalOfficialDashboard">0</span></p>
          <button onclick="downloadExcel('transactionTableDashboard')">Download Report</button>
        </div>

        <!-- Transactions Table -->
        <table id="transactionTableDashboard" class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Date</th>
              <th>Time</th>
              <th>F_NAME</th>
              <th>L_NAME</th>
              <th>Email</th>
              <th>Category</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>1</td>
              <td>2025-09-06</td>
              <td>10:00</td>
              <td>Arif</td>
              <td>Hasan</td>
              <td>arif@example.com</td>
              <td>Income</td>
              <td>500</td>
              <td>Approved</td>
              <td><button onclick="holdTransaction(this)">Hold</button></td>
            </tr>
            <tr>
              <td>2</td>
              <td>2025-09-05</td>
              <td>14:30</td>
              <td>John</td>
              <td>Doe</td>
              <td>john@example.com</td>
              <td>Expense</td>
              <td>300</td>
              <td>Approved</td>
              <td><button onclick="holdTransaction(this)">Hold</button></td>
            </tr>
            <tr>
              <td>3</td>
              <td>2025-09-05</td>
              <td>16:15</td>
              <td>Jane</td>
              <td>Smith</td>
              <td>jane@example.com</td>
              <td>Income</td>
              <td>1200</td>
              <td>Approved</td>
              <td><button onclick="holdTransaction(this)">Hold</button></td>
            </tr>
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
</body>
</html>
