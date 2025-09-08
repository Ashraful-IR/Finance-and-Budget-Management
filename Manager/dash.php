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
                    <label>First Name:</label>
                    <input type="text" name="fname" required placeholder="First Name">
                    <label>Last Name:</label>
                    <input type="text" name="lname" required placeholder="Last Name">
                    <label>Email:</label>
                    <input type="email" name="email" required placeholder="Email">
                    <label>Phone Number:</label>
                    <input type="tel" name="phone" required placeholder="Phone Number">
                    <label>Password:</label>
                    <input type="password" name="password" required placeholder="Password">
                    <label>Confirm Password:</label>
                    <input type="password" name="cpassword" required placeholder="Confirm Password">
                    <label>Designation:</label>
                    <select name="designation" required>
                        <option value="Admin">Admin</option>
                        <option value="Manager">Manager</option>
                        <option value="Employee">Employee</option>
                        <option value="Auditor">Auditor</option>
                    </select>
                    <label>Department:</label>
                    <select name="department" required>
                        <option value="HR">HR</option>
                        <option value="Accounts">Accounts</option>
                        <option value="Engineering">Engineering</option>
                        <option value="Finance">Finance</option>
                    </select>
                    <button class="primary" type="submit" name="createUser">Create User</button>
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