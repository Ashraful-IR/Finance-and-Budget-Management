<?php
 
include "config.php"; // PHP extarnal file connetion
 
$success = $error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['Expense'])){
    $Expname=     $_POST["Expname"];
    $Purpose=     $_POST["Purpose"];
    $Amount=      $_POST["Amount"];
    $Date=        $_POST["Date"];
    $PayMethod=   $_POST["PayMethod"];
    $Status=      $_POST["Status"];
    $Designation= $_POST["Designation"];
    $Department=  $_POST["Department"];

    if(empty($Expname) || empty($Purpose) || empty($Amount) || empty($Date) || empty($PayMethod) || empty($Status) || empty($Designation) || empty($Department)) {
        $error = "All section should be filled";
    } else {
        $sql = "INSERT INTO expense (Expname,Purpose,Amount,Date,PayMethod,Status,Designation,Department) VALUES ('$Expname','$Purpose','$Amount','$Date','$PayMethod','$Status','$Designation','$Department')";
        if($conn->query($sql) === TRUE ) {
            $success = "New Expense Submited ";
        } else {
            $error ="Error" . $conn->error;
        }
    }
}

?>








<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Manager</title>
    <link rel="stylesheet" href="dashstyle.css" type="text/css">
    <script src="dashjs.js" defer></script>
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

        <a href="#" onclick="showSection('dashboard', event)">
            <ion-icon name="home-outline"></ion-icon> <span>Dashboard</span>
        </a>

        <a href="#" onclick="showSection('add', event)">
            <ion-icon name="add-circle-outline"></ion-icon> <span>Add</span>
        </a>

        <a href="#" onclick="showSection('authorize', event)">
            <ion-icon name="checkmark-done-outline"></ion-icon> <span>Authorize</span>
        </a>

        <a href="#" onclick="showSection('balance', event)">
            <ion-icon name="wallet-outline"></ion-icon> <span>Balance</span>
        </a>

        <a href="#" onclick="showSection('transactions', event)">
            <ion-icon name="list-outline"></ion-icon> <span>Transactions</span>
        </a>

        <a href="#" onclick="showSection('reports', event)">
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

            <section id="dashboard" class="section active">
                <h2>Welcome to the Dashboard</h2>
                <p>Here is your general dashboard content...</p>
            </section>

            <section id="add" class="section">
                <h2>Add Expenses</h2>
                <form method="POST" action="">
                    <label>Expense Name:</label>
                    <input type="text" name="Expname" required placeholder="Expense Name">
                    <label>Expense Purpose:</label>
                    <input type="text" name="Purpose" required placeholder="Expense Purpose">
                    <label>Amount:</label>
                    <input type="number" name="Amount" required placeholder="Amount">
                    <label>Date:</label>
                    <input type="date" name="Date" required placeholder="Date">
                    <label>Payment Method:</label>
                    <select name="PayMethod" required>
                        <option value="a" disabled selected hidden></option>
                        <option value="Cash">Cash</option>
                        <option value="Btransfer">Bank Transfer</option>
                        <option value="OnlineBank">Online Banking</option>
                        <option value="CCard">Credit Card</option>
                    </select>
                    <label>Status:</label>
                    <select name="Status" required>
                        <option value="a" disabled selected hidden></option>
                        <option value="Paid">Paid</option>
                        <option value="Due">Due</option>
                        <option value="PPaid">Partial Paid</option>
                    </select>
                    <label>Designation:</label>
                    <select name="Designation" required>
                        <option value="a" disabled selected hidden></option>
                        <option value="Admin">Admin</option>
                        <option value="Manager">Manager</option>
                        <option value="Employee">Employee</option>
                        <option value="Auditor">Auditor</option>
                    </select>
                    <label>Department:</label>
                    <select name="Department" required>
                        <option value="a" disabled selected hidden></option>
                        <option value="HR">HR</option>
                        <option value="Accounts">Accounts</option>
                        <option value="Engineering">Engineering</option>
                        <option value="Finance">Finance</option>
                    </select>
                    <button class="primary" type="submit" name="Expense">Submit</button>
                </form>
            </section>

            <section id="authorize" class="section">
                <h2>Authorize Page</h2>
                <p>Authorization related content...</p>
            </section>

            <section id="balance" class="section">
                <h2>Balance Page</h2>
                <p>Your balance details...</p>
            </section>

            <section id="transactions" class="section">
                <h2>Transactions Page</h2>
                <p>Transaction history...</p>
            </section>

            <section id="reports" class="section">
                <h2>Reports Page</h2>
                <p>Reports generation area...</p>
            </section>

        </div>
    </div>
</body>

</html>