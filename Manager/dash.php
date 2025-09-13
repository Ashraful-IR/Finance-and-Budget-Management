<?php
session_start();
include "config.php"; // PHP extarnal file connetion


 
$success = $error = "";
if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

if (isset($_GET['error']) && $_GET['error'] === 'invalid') {
    $error = "Enter a valid ID.";
}

$error = "";
if (isset($_GET['error']) && $_GET['error'] === 'invalid') {
    $error = "Enter a valid ID.";
}
$uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
$me = null;
if ($uid > 0) {
    $resMe = $conn->query("SELECT * FROM Users WHERE id=$uid LIMIT 1");
    $me = $resMe ? $resMe->fetch_assoc() : null;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: ../Login/login.php");
    exit();
}

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
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
            exit();
        } else {
            $error ="Error" . $conn->error;
        }
    }
}


// Calculate totals
$totalExpense = 0;
$totalIncome = 0;

$resExp = $conn->query("SELECT SUM(Amount) AS total FROM expense");
if ($resExp && $resExp->num_rows > 0) {
    $totalExpense = (float) $resExp->fetch_assoc()['total'];
}

$resInc = $conn->query("SELECT SUM(Amount) AS total FROM income");
if ($resInc && $resInc->num_rows > 0) {
    $totalIncome = (float) $resInc->fetch_assoc()['total'];
}

// Current balance = income - expense
$balance = $totalIncome - $totalExpense;




$allExpense      = $conn->query("SELECT * FROM expense");
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['ajax']) && $_POST['ajax'] === 'deleteExpense') {
    header('Content-Type: application/json');
    $Id = (int)($_POST['Id'] ?? 0);
    $ok = $Id > 0 ? $conn->query("DELETE FROM expense WHERE Id=$Id") : false;
    echo json_encode(["success" => $ok ? true : false]);
    exit;
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addincome'])){
    $IncomeName=  $_POST["IncomeName"];
    $IncomeSource=$_POST["IncomeSource"];
    $Amount=      $_POST["Amount"];
    $Date=        $_POST["Date"];
    $PayMethod=   $_POST["PayMethod"];
    $Status=      $_POST["Status"];
    $Designation= $_POST["Designation"];
    $Department=  $_POST["Department"];

    if(empty($IncomeName) || empty($IncomeSource) || empty($Amount) || empty($Date) || empty($PayMethod) || empty($Status) || empty($Designation) || empty($Department)) {
        $error = "All section should be filled";
    } else {
        $sql = "INSERT INTO income (IncomeName,IncomeSource,Amount,Date,PayMethod,Status,Designation,Department) VALUES ('$IncomeName','$IncomeSource','$Amount','$Date','$PayMethod','$Status','$Designation','$Department')";
        if($conn->query($sql) === TRUE ) {
            $success = "New Expense Submited ";
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
            exit();
        } else {
            $error ="Error" . $conn->error;
        }
    }
}
$allIncome      = $conn->query("SELECT * FROM income");
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['ajax']) && $_POST['ajax'] === 'deleteIncome') {
    header('Content-Type: application/json');
    $Id = (int)($_POST['Id'] ?? 0);
    $ok = $Id > 0 ? $conn->query("DELETE FROM income WHERE Id=$Id") : false;
    echo json_encode(["success" => $ok ? true : false]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['updateMyProfile']) && $uid > 0) {
    $my_fname = $_POST['my_fname'];
    $my_lname = $_POST['my_lname'];
    $my_email = $_POST['my_email'];
    $my_phone = $_POST['my_phone'];
    $my_desi  = $_POST['my_desi'];
    $my_dept  = $_POST['my_dept'];

    if ($my_fname==="" || $my_lname==="" || $my_email==="" || $my_phone==="" || $my_desi==="" || $my_dept==="") {
        $error = "All fields are required.";
    } else {
        $dup = $conn->query("SELECT id FROM Users WHERE email='$my_email' AND id<>$uid LIMIT 1");
        if ($dup && $dup->num_rows > 0) {
            $error = "Email already exists.";
        } else {
            $conn->query("UPDATE Users SET fname='$my_fname', lname='$my_lname', email='$my_email', phone='$my_phone', desi='$my_desi', dept='$my_dept' WHERE id=$uid");
            header("Location: dash.php#accountMgmt");
            exit();
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['updateMyPassword']) && $uid > 0) {
    $p1 = $_POST['myNewPassword'] ?? '';
    $p2 = $_POST['myConfirmPassword'] ?? '';
    if ($p1 === '' || $p2 === '') {
        $error = "Enter password.";
    } elseif ($p1 !== $p2) {
        $error = "Passwords do not match.";
    } else {
        $conn->query("UPDATE Users SET pass='$p1', cpass='$p1' WHERE id=$uid");
        header("Location: dash.php#accountMgmt");
        exit();
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['deleteMyAccount']) && $uid > 0) {
    $conn->query("DELETE FROM Users WHERE id=$uid");
    session_destroy();
    header("Location: ../Login/login.php");
    exit();
}
$activeSection = "dashboard";
$reportRow = null;
$reportTable = null;
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generateReport'])) {
    $activeSection = "reports";
    $reportId = (int)($_POST['reportId'] ?? 0);
    $reportTable = $_POST['reportTable'] ?? 'expense';

    $allowedTables = ['expense', 'transaction', 'income'];
    if (!in_array($reportTable, $allowedTables)) {
        $error = "Invalid table selected.";
    } elseif ($reportId <= 0) {
        $error = "Enter a valid ID.";
    } else {
        $res = $conn->query("SELECT * FROM $reportTable WHERE Id=$reportId LIMIT 1");
        if ($res && $res->num_rows > 0) {
            $reportRow = $res->fetch_assoc();
        } else {
            $error = "No record found with ID: $reportId in table $reportTable";
        }
    }
}




// Handle GET (after redirect)
if (isset($_GET['reportId'])) {
    $reportId = (int) $_GET['reportId'];
    $res = $conn->query("SELECT * FROM expense WHERE Id=$reportId LIMIT 1");
    if ($res && $res->num_rows > 0) {
        $reportRow = $res->fetch_assoc();
    } else {
        $error = "No expense found with ID: $reportId";
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
            <ion-icon name="add-circle-outline"></ion-icon> <span>Add Expense</span>
        </a>
        <a href="#" onclick="showSection('addincome', event)">
            <ion-icon name="cash-outline"></ion-icon> <span>Add Income</span>
        </a>
        <a href="#" onclick="showSection('ShowInc', event)">
            <ion-icon name="bag-add-outline"></ion-icon> <span>Show Income</span>
        </a>
        <a href="#" onclick="showSection('ShowExp', event)">
            <ion-icon name="list-outline"></ion-icon> <span>Show Expense</span>
        </a>

        <a href="#" onclick="showSection('authorize', event)">
            <ion-icon name="checkmark-done-outline"></ion-icon> <span>Authorize</span>
        </a>

        <a href="#" onclick="showSection('balance', event)">
            <ion-icon name="wallet-outline"></ion-icon> <span>Balance</span>
        </a>

        <a href="#" onclick="showSection('reports', event)">
            <ion-icon name="document-text-outline"></ion-icon> <span>Reports</span>
        </a>

        <a href="#" onclick="showSection('account', event)">
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
                        <option value="Bank transfer">Bank Transfer</option>
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



            <section id="addincome" class="section">
                <h2>Add Income</h2>
                <form method="POST" action="">
                    <label>Income Name:</label>
                    <input type="text" name="IncomeName" required placeholder="Income Name">
                    <label>Income Source:</label>
                    <input type="text" name="IncomeSource" required placeholder="Income Source">
                    <label>Amount:</label>
                    <input type="number" name="Amount" required placeholder="Amount">
                    <label>Date:</label>
                    <input type="date" name="Date" required placeholder="Date">
                    <label>Payment Method:</label>
                    <select name="PayMethod" required>
                        <option value="a" disabled selected hidden></option>
                        <option value="Cash">Cash</option>
                        <option value="Bank transfer">Bank Transfer</option>
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
                    <button class="primary" type="submit" name="addincome">Submit</button>
                </form>

            </section>




            <section id="ShowExp" class="section">
                <h2>All Expense</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Expense Name</th>
                            <th>Expense Purpose</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Designation</th>
                            <th>Department</th>

                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersBody">
                        <?php if ($allExpense && $allExpense->num_rows > 0): while ($row = $allExpense->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['Id']; ?></td>
                            <td><?php echo $row['Expname']; ?></td>
                            <td><?php echo $row['Purpose']; ?></td>
                            <td><?php echo $row['Amount']; ?></td>
                            <td><?php echo $row['Date']; ?></td>
                            <td><?php echo $row['PayMethod']; ?></td>
                            <td><?php echo $row['Status']; ?></td>
                            <td><?php echo $row['Designation']; ?></td>
                            <td><?php echo $row['Department']; ?></td>
                            <td>
                                <button class="danger" type="button"
                                    onclick="deleteExpense(<?php echo $row['Id']; ?>, this)">Delete</button>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="8">No users found</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>


            <section id="ShowInc" class="section">
                <h2>All Icome</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Income Name</th>
                            <th>Income Source</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Designation</th>
                            <th>Department</th>

                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersBody">
                        <?php if ($allIncome && $allIncome->num_rows > 0): while ($row = $allIncome->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['Id']; ?></td>
                            <td><?php echo $row['IncomeName']; ?></td>
                            <td><?php echo $row['IncomeSource']; ?></td>
                            <td><?php echo $row['Amount']; ?></td>
                            <td><?php echo $row['Date']; ?></td>
                            <td><?php echo $row['PayMethod']; ?></td>
                            <td><?php echo $row['Status']; ?></td>
                            <td><?php echo $row['Designation']; ?></td>
                            <td><?php echo $row['Department']; ?></td>
                            <td>
                                <button class="danger" type="button"
                                    onclick="deleteIncome(<?php echo $row['Id']; ?>, this)">Delete</button>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="8">No users found</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>


            <section id="authorize" class="section">
                <h2>Authorize Page</h2>
                <p>Authorization related content...</p>
            </section>

            <section id="balance" class="section">
                <h2>Balance Page</h2>
                <p>Total Income: <?php echo number_format($totalIncome, 2); ?></p>
                <p>Total Expense: <?php echo number_format($totalExpense, 2); ?></p>
                <p><strong>Current Balance: <?php echo number_format($balance, 2); ?></strong></p>
            </section>

            <section id="transactions" class="section">
                <h2>Transactions Page</h2>
                <p>Transaction history...</p>
            </section>

            <section id="reports" class="section">
                <h2>Reports Page</h2>
                <p>Generate report by entering ID:</p>
                <form method="POST" action="">
                    <label>Select Table:</label>
                    <select name="reportTable" required>
                        <option value="a" disabled selected hidden></option>
                        <option value="expense">Expense</option>
                        <option value="transaction">Transaction</option>
                        <option value="income">Income</option>
                    </select>

                    <label>Enter ID:</label>
                    <input type="number" name="reportId" placeholder="Enter ID" required
                        value="<?php echo isset($_POST['reportId']) ? (int)$_POST['reportId'] : ''; ?>">

                    <button type="submit" name="generateReport">Generate Report</button>
                </form>
                <?php if ($error): ?>
                <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>

                <?php if ($reportRow): ?>
                <h3>Report Result (<?php echo ucfirst($reportTable); ?>)</h3>
                <table border="1" cellpadding="8" cellspacing="0">
                    <tbody>
                        <?php foreach ($reportRow as $key => $value): ?>
                        <tr>
                            <th><?php echo htmlspecialchars($key); ?></th>
                            <td><?php echo htmlspecialchars($value); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </section>


            <section id="account" class="section">
                <h2>Account Management</h2>
                <?php if ($uid===0 || !$me): ?>
                <p>Please log in first.</p>
                <?php else: ?>
                <h3>Your Profile</h3>
                <table>
                    <tr>
                        <th>ID</th>
                        <td><?php echo $me['id']; ?></td>
                    </tr>
                    <tr>
                        <th>Name</th>
                        <td><?php echo $me['fname']." ".$me['lname']; ?></td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td><?php echo $me['email']; ?></td>
                    </tr>
                    <tr>
                        <th>Phone</th>
                        <td><?php echo $me['phone']; ?></td>
                    </tr>
                    <tr>
                        <th>Designation</th>
                        <td><?php echo $me['desi']; ?></td>
                    </tr>
                    <tr>
                        <th>Department</th>
                        <td><?php echo $me['dept']; ?></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td><?php echo $me['status']; ?></td>
                    </tr>
                </table>

                <h3>Edit Profile</h3>
                <form method="POST">
                    <input type="text" name="my_fname" value="<?php echo $me['fname']; ?>" required>
                    <input type="text" name="my_lname" value="<?php echo $me['lname']; ?>" required>
                    <input type="email" name="my_email" value="<?php echo $me['email']; ?>" required>
                    <input type="text" name="my_phone" value="<?php echo $me['phone']; ?>" required>
                    <select name="my_desi" required>
                        <option <?php if($me['desi']=='Admin') echo 'selected'; ?>>Admin</option>
                        <option <?php if($me['desi']=='Manager') echo 'selected'; ?>>Manager</option>
                        <option <?php if($me['desi']=='Employee') echo 'selected'; ?>>Employee</option>
                        <option <?php if($me['desi']=='Auditor') echo 'selected'; ?>>Auditor</option>
                    </select>
                    <select name="my_dept" required>
                        <option <?php if($me['dept']=='HR') echo 'selected'; ?>>HR</option>
                        <option <?php if($me['dept']=='Accounts') echo 'selected'; ?>>Accounts</option>
                        <option <?php if($me['dept']=='Engineering') echo 'selected'; ?>>Engineering</option>
                        <option <?php if($me['dept']=='Finance') echo 'selected'; ?>>Finance</option>
                    </select>
                    <button class="primary" type="submit" name="updateMyProfile">Save Changes</button>
                </form>

                <h3>Change Password</h3>
                <form method="POST">
                    <input type="password" name="myNewPassword" placeholder="New password" required>
                    <input type="password" name="myConfirmPassword" placeholder="Confirm password" required>
                    <button type="submit" name="updateMyPassword">Update Password</button>
                </form>

                <h3>Delete Account</h3>
                <form method="POST" onsubmit="return confirm('Delete your account? This cannot be undone.');">
                    <button class="danger" type="submit" name="deleteMyAccount">Delete My Account</button>
                </form>
                <?php endif; ?>
            </section>

        </div>
    </div>
</body>
<script>
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}
</script>

</html>