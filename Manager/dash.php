<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Manager</title>
    <link rel="stylesheet" href="../Manager/dashstyle.css" type="text/css">
    <script src="../Manager/dashjs.js"></script>
</head>

<body>
    <div class="container">

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

        </div>

        <div class="content">
            <div id="dashboard" class="section active"></div>

            <div id="Add" class="section">
                <h1>Welcome!</h1>
                <form id="addExpenseForm">
                    <input type="text" id="expenseName" placeholder="Expense Name" required>
                    <input type="number" id="expenseAmount" placeholder="Amount" required>
                    <button type="submit">Add Expense</button>
                </form>
                <ul id="expenseList"></ul>
            </div>

            <div id="Authorize" class="section"></div>
            <div id="Balance" class="section"></div>
            <div id="Transactions" class="section"></div>
            <div id="Generate_reports" class="section"></div>
        </div>

    </div>

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>

</html>