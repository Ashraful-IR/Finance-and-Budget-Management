<?php
include "configdb.php";

if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['ajax']) && $_POST['ajax'] === 'deleteUser') {
    header('Content-Type: application/json');
    $userId = (int)($_POST['userId'] ?? 0);
    $ok = $userId > 0 ? $conn->query("DELETE FROM Users WHERE id=$userId") : false;
    echo json_encode(["success" => $ok ? true : false]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['toggleUser'])) {
    $userId = (int)$_POST['userId'];
    $newStatus = ($_POST['newStatus'] === 'Active') ? 'Active' : 'Inactive';
    $conn->query("UPDATE Users SET status='$newStatus' WHERE id=$userId");
    header("Location: dash.php#statusUser");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['createUser'])) {
    $fname       = $_POST['fname'];
    $lname       = $_POST['lname'];
    $email       = $_POST['email'];
    $phone       = $_POST['phone'];
    $password    = $_POST['password'];
    $cpassword   = $_POST['cpassword'];
    $designation = $_POST['designation'];
    $department  = $_POST['department'];

    if ($password !== $cpassword) {
        $error = "Passwords do not match!";
    } else {
        $check = $conn->query("SELECT id FROM Users WHERE email='$email' LIMIT 1");
        if ($check && $check->num_rows > 0) {
            $error = "Email already exists. Please use another email.";
        } else {
            $sql_insert = "INSERT INTO Users (fname, lname, email, phone, pass, desi, dept, status) VALUES ('$fname', '$lname', '$email', '$phone', '$password', '$designation', '$department', 'Active')";
            if ($conn->query($sql_insert) === TRUE) {
                header("Location: dash.php#createUser");
                exit();
            } else {
                $error = "Error: " . $sql_insert . "<br>" . $conn->error;
            }
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['updatePassword'])) {
    $userId      = (int)($_POST['userId'] ?? 0);
    $newPassword = $_POST['newPassword'] ?? '';
    if ($userId > 0 && $newPassword !== '') {
        $conn->query("UPDATE Users SET pass='$newPassword', cpass='$newPassword' WHERE id=$userId");
        header("Location: dash.php#accountRecovery");
        exit();
    } else {
        $error = "Please enter a new password.";
    }
}

$allUsers      = $conn->query("SELECT * FROM Users");
$statusUsers   = $conn->query("SELECT id, fname, lname, desi, status FROM Users");
$recoveryUsers = $conn->query("SELECT id, fname, lname, pass FROM Users");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Admin Dashboard - User Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashstyle.css" type="text/css">

</head>
<body>
    <div class="sidebar">
        <h2>Admin</h2>
        <a href="#" onclick="showSection('dashboard')">Dashboard</a>
        <a href="#" onclick="showSection('users')">Users</a>
        <a href="#" onclick="showSection('createUser')">Create User</a>
        <a href="#" onclick="showSection('statusUser')">User Status</a>
        <a href="#" onclick="showSection('accountRecovery')">Account Recovery</a>
        <a href="#">Settings</a>
        <a href="#">Account Management</a>
    </div>

    <div class="main-content">
        <header><h1>Admin Dashboard</h1></header>

        <div class="container">
            <?php if (isset($error)): ?><div class="msg"><?php echo $error; ?></div><?php endif; ?>

            <section id="dashboard" class="active">
                <h2>Welcome to the Dashboard</h2>
                <p>Here is your general dashboard content...</p>
            </section>

            <section id="users" class="inactive">
                <h2>All Users</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th><th>First Name</th><th>Last Name</th>
                            <th>Email</th><th>Phone</th>
                            <th>Designation</th><th>Department</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersBody">
                        <?php if ($allUsers && $allUsers->num_rows > 0): ?>
                            <?php while ($row = $allUsers->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo $row['fname']; ?></td>
                                    <td><?php echo $row['lname']; ?></td>
                                    <td><?php echo $row['email']; ?></td>
                                    <td><?php echo $row['phone']; ?></td>
                                    <td><?php echo $row['desi']; ?></td>
                                    <td><?php echo $row['dept']; ?></td>
                                    <td><button class="danger" type="button" onclick="deleteUser(<?php echo $row['id']; ?>, this)">Delete</button></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="9">No users found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>

            <section id="createUser" class="inactive">
                <h2>Create New User</h2>
                <?php if (isset($success)): ?><p class="success"><?php echo $success; ?></p><?php endif; ?>
                <?php if (isset($error)): ?><p class="error"><?php echo $error; ?></p><?php endif; ?>

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
                        <option value="Sales">Sales</option>
                        <option value="Finance">Finance</option>
                        <option value="Engineering">Engineering</option>
                        <option value="HR">HR</option>
                    </select>

                    <button class="primary" type="submit" name="createUser">Create User</button>
                </form>
            </section>

            <section id="statusUser" class="inactive">
                <h2>User Status</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th><th>Name</th><th>Designation</th><th>Status</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($statusUsers && $statusUsers->num_rows > 0) {
                            while ($row = $statusUsers->fetch_assoc()) {
                                $id     = $row['id'];
                                $name   = $row['fname'] . " " . $row['lname'];
                                $desi   = $row['desi'];
                                $status = $row['status'];
                                echo "<tr>";
                                echo "<td>$id</td>";
                                echo "<td>$name</td>";
                                echo "<td>$desi</td>";
                                if (strtolower($status) === "active") {
                                    echo "<td><span class='status-active'>$status</span></td>";
                                    echo "<td>
                                            <form method='POST'>
                                                <input type='hidden' name='userId' value='$id'>
                                                <input type='hidden' name='newStatus' value='Inactive'>
                                                <button class='danger' type='submit' name='toggleUser'>Deactivate</button>
                                            </form>
                                          </td>";
                                } else {
                                    echo "<td><span class='status-inactive'>$status</span></td>";
                                    echo "<td>
                                            <form method='POST'>
                                                <input type='hidden' name='userId' value='$id'>
                                                <input type='hidden' name='newStatus' value='Active'>
                                                <button class='success' type='submit' name='toggleUser'>Activate</button>
                                            </form>
                                          </td>";
                                }
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No users found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </section>

            <section id="accountRecovery" class="inactive">
                <h2>Account Recovery</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th><th>Name</th><th>Current Password</th><th>New Password</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($recoveryUsers && $recoveryUsers->num_rows > 0) {
                            while ($r = $recoveryUsers->fetch_assoc()) {
                                $rid   = $r['id'];
                                $rname = $r['fname'] . " " . $r['lname'];
                                $rpass = $r['pass'];
                                echo "<tr>";
                                echo "<td>{$rid}</td>";
                                echo "<td>{$rname}</td>";
                                echo "<td>{$rpass}</td>";
                                echo "<td>
                                        <form method='POST' class='inline-form'>
                                            <input type='hidden' name='userId' value='{$rid}'>
                                            <input type='password' name='newPassword' placeholder='New password' required>
                                            <button type='submit' name='updatePassword'>Update password</button>
                                        </form>
                                      </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No users found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </section>

        </div>
    </div>

    <script>
        function showSection(section) {
            document.getElementById("dashboard").classList.add("inactive");
            document.getElementById("users").classList.add("inactive");
            document.getElementById("createUser").classList.add("inactive");
            document.getElementById("statusUser").classList.add("inactive");
            document.getElementById("accountRecovery").classList.add("inactive");
            document.getElementById(section).classList.remove("inactive");
        }

        window.addEventListener("load", function() {
            if (window.location.hash) {
                let section = window.location.hash.substring(1);
                if (document.getElementById(section)) {
                    showSection(section);
                }
            }
        });

        function deleteUser(id, btn) {
            if (!confirm('Are you sure you want to delete this user?')) return;
            const data = new URLSearchParams();
            data.append('ajax','deleteUser');
            data.append('userId', id);
            fetch('dash.php', {
                method: 'POST',
                headers: {'Content-Type':'application/x-www-form-urlencoded'},
                body: data.toString()
            })
            .then(r => r.json())
            .then(j => {
                if (j && j.success) {
                    const tr = btn.closest('tr');
                    const tbody = tr.parentElement;
                    tr.remove();
                    if (tbody.children.length === 0) {
                        const empty = document.createElement('tr');
                        const td = document.createElement('td');
                        td.colSpan = 9;
                        td.textContent = 'No users found';
                        empty.appendChild(td);
                        tbody.appendChild(empty);
                    }
                } else {
                    alert('Delete failed');
                }
            })
            .catch(() => alert('Delete failed'));
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
