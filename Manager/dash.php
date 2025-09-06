<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Manager</title>
    <link rel="stylesheet" href="../Manager/dashstyle.css" type="text/css">
    <script src="../Manager/dashjs.js"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>

<body>

    <div class="menu">
        <div class="menu-header">
            <h2>Manager</h2>
            <div class="menu-toggle" onclick="toggleMenu()">
                <ion-icon name="menu-outline"></ion-icon>
            </div>
        </div>
        <a href="#" onclick="showSection('dashboard')">
            <ion-icon name="home-outline"></ion-icon> <span>Dashboard</span>
        </a>
        <a href="#" onclick="showSection('Add')">
            <ion-icon name="add-circle-outline"></ion-icon> <span>Add</span>
        </a>
        <a href="#" onclick="showSection('Authorize')">
            <ion-icon name="checkmark-done-outline"></ion-icon> <span>Authorize</span>
        </a>
        <a href="#" onclick="showSection('Balance')">
            <ion-icon name="wallet-outline"></ion-icon> <span>Balance</span>
        </a>
        <a href="#" onclick="showSection('Transactions')">
            <ion-icon name="list-outline"></ion-icon> <span>Transactions</span>
        </a>
        <a href="#" onclick="showSection('Generate_reports')">
            <ion-icon name="document-text-outline"></ion-icon> <span>Reports</span>
        </a>
        <a href="#">
            <ion-icon name="person-outline"></ion-icon> <span>Account</span>
        </a>
        <a href="#" onclick="logout()" class="menu-bottom">
            <ion-icon name="log-out-outline"></ion-icon> <span>Log Out</span>
        </a>
    </div>

  
    <div class="topbar">
        <h1>Manager Dashboard</h1>
    </div>
    <div class="main-content">
        <div class="content">
            <div id="dashboard" class="section active">
                <p>Welcome to the Manager Dashboard. Use the menu to navigate through different sections.</p>
            </div>

            <div id="Add" class="section">
                <div id="addButtons" style="display: none;">
                    <h1>Welcome!</h1>
                    <button type="button" onclick="showAddForm()">Add Expense</button>
                    <button type="button" onclick="showDeleteTable()">Delete Expense</button>
                </div>
            </div>

            <div id="Authorize" class="section">
                <h1>Pending Authorizations</h1>
                <ul id="authorizationList"></ul>
            </div>

            <div id="Balance" class="section">
                <h1>Current Balance</h1>
                <p id="balanceAmount">$0.00</p>
                <button type="button" onclick="refreshBalance()">Refresh Balance</button>
            </div>

            <div id="Transactions" class="section">
                <h1>Transaction History</h1>
                <ul id="transactionList"></ul>
            </div>

            <div id="Generate_reports" class="section">
                <h1>Generate Reports</h1>
                <button type="button" onclick="generateReport()">Generate Report</button>
                <div id="reportSection"></div>
            </div>
        </div>
    </div>

</body>
</html>
