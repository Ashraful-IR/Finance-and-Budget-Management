<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Auditor Dashboard</title>
  <link rel="stylesheet" href="audit.css">
<script src="config.js"></script>
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
      <a href="#" onclick="showSection('dashboard')">
        <ion-icon name="home-outline"></ion-icon> Dashboard
      </a>
      <a href="#" onclick="showSection('Search')">
        <ion-icon name="search-outline"></ion-icon> Search
      </a>
      <a href="#" onclick="showSection('Authorize')">
        <ion-icon name="checkmark-done-outline"></ion-icon> Authorize
      </a>
      <a href="#" onclick="showSection('Balance')">
        <ion-icon name="wallet-outline"></ion-icon> Balance
      </a>
      <a href="#" onclick="showSection('Generate_reports')">
        <ion-icon name="document-text-outline"></ion-icon> Reports review
      </a>
      <a href="#" onclick="showSection('Accounts')">
        <ion-icon name="person-outline"></ion-icon> Accounts
      </a>
    </div>

    <!-- Content -->
    <div class="content">

      <!-- Dashboard -->
      <div id="dashboard" class="content-section active">
        <h1>Auditor Dashboard</h1>

        <!-- Filters -->
        <div class="filters">
          <label>Date From: <input type="date" id="dateFrom"></label>
          <label>Date To: <input type="date" id="dateTo"></label>
          <button onclick="filterByDate()">Search by Date</button>

          <label>Category:
            <select id="categoryFilter" onchange="filterByCategory()">
              <option value="">All</option>
              <option value="Income">Income</option>
              <option value="Expense">Expense</option>
            </select>
          </label>
        </div>

        <!-- Summary -->
        <div id="summary">
          <p>Total Income: $<span id="totalIncome">0</span></p>
          <p>Total Expenses: $<span id="totalExpense">0</span></p>
          <button onclick="downloadExcel()">Download Report</button>
        </div>

        <!-- Transactions Table -->
        <table id="transactionTable" class="table">
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

      <!-- Other Sections -->
      <div id="Search" class="content-section">Search Section</div>
      <div id="Authorize" class="content-section">Authorize Section</div>
      <div id="Balance" class="content-section">Balance Section</div>
      <div id="Generate_reports" class="content-section">Reports Section</div>
      <div id="Accounts" class="content-section">Accounts</div>

    </div>

  </div>

  <!-- JS -->
  <script src="audit.js"></script>

  <!-- Ionicons -->
  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>
</html>
