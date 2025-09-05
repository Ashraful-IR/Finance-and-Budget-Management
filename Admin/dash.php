<?php
session_start();
include "configdb.php";

if ($conn->connect_error) { die("Connection failed: " . $conn->connect_error); }

$error = "";
$uid = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['logout'])) {
    session_unset();
    session_destroy();
    header("Location: ../Login/login.php");
    exit();
}

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

$assign_msg = "";
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['assignManager'])) {
    $dept = $_POST['dept'] ?? '';
    $manager_id = (int)($_POST['manager_id'] ?? 0);
    if (in_array($dept, ['HR','Accounts','Engineering','Finance']) && $manager_id > 0) {
        $conn->query("UPDATE Users SET dept='$dept' WHERE id=$manager_id");
        $_SESSION['assign_msg'] = "Successfully assign.";
        header("Location: dash.php#assignManager");
        exit();
    } else {
        $error = "Please select department and manager.";
    }
}

$allUsers      = $conn->query("SELECT * FROM Users");
$statusUsers   = $conn->query("SELECT id, fname, lname, desi, status FROM Users");
$recoveryUsers = $conn->query("SELECT id, fname, lname, pass FROM Users");
$me = null;
if ($uid > 0) {
    $resMe = $conn->query("SELECT * FROM Users WHERE id=$uid LIMIT 1");
    $me = $resMe ? $resMe->fetch_assoc() : null;
}
$managers = $conn->query("SELECT id, fname, lname FROM Users WHERE desi='Manager' ORDER BY fname");
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
        <a href="#" onclick="showSection('accountMgmt')">Account Management</a>
        <a href="#" onclick="showSection('assignManager')">Assign Manager</a>
         <form method="post" style="padding:300px 30px 0px 30px;">
            <button class="danger" type="submit" name="logout" style="width:100%;">Logout</button>
        </form>
    </div>

    <div class="main-content">
        <header><h1>Admin Dashboard</h1></header>

        <div class="container">
            <?php if ($error !== ""): ?><div class="msg"><?php echo $error; ?></div><?php endif; ?>

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
                        <?php if ($allUsers && $allUsers->num_rows > 0): while ($row = $allUsers->fetch_assoc()): ?>
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
                        <?php endwhile; else: ?>
                            <tr><td colspan="8">No users found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>

            <section id="createUser" class="inactive">
                <h2>Create New User</h2>
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

            <section id="statusUser" class="inactive">
                <h2>User Status</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th><th>Name</th><th>Designation</th><th>Status</th><th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($statusUsers && $statusUsers->num_rows > 0): while ($row = $statusUsers->fetch_assoc()):
                            $id=$row['id']; $name=$row['fname']." ".$row['lname']; $desi=$row['desi']; $status=$row['status']; ?>
                            <tr>
                                <td><?php echo $id; ?></td>
                                <td><?php echo $name; ?></td>
                                <td><?php echo $desi; ?></td>
                                <?php if (strtolower($status)==="active"): ?>
                                    <td><span class="status-active"><?php echo $status; ?></span></td>
                                    <td>
                                        <form method="POST">
                                            <input type="hidden" name="userId" value="<?php echo $id; ?>">
                                            <input type="hidden" name="newStatus" value="Inactive">
                                            <button class="danger" type="submit" name="toggleUser">Deactivate</button>
                                        </form>
                                    </td>
                                <?php else: ?>
                                    <td><span class="status-inactive"><?php echo $status; ?></span></td>
                                    <td>
                                        <form method="POST">
                                            <input type="hidden" name="userId" value="<?php echo $id; ?>">
                                            <input type="hidden" name="newStatus" value="Active">
                                            <button class="success" type="submit" name="toggleUser">Activate</button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="5">No users found</td></tr>
                        <?php endif; ?>
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
                        <?php if ($recoveryUsers && $recoveryUsers->num_rows > 0): while ($r = $recoveryUsers->fetch_assoc()):
                            $rid=$r['id']; $rname=$r['fname']." ".$r['lname']; $rpass=$r['pass']; ?>
                            <tr>
                                <td><?php echo $rid; ?></td>
                                <td><?php echo $rname; ?></td>
                                <td><?php echo $rpass; ?></td>
                                <td>
                                    <form method="POST">
                                        <input type="hidden" name="userId" value="<?php echo $rid; ?>">
                                        <input type="password" name="newPassword" placeholder="New password" required>
                                        <button type="submit" name="updatePassword">Update password</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="4">No users found</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>

            <section id="accountMgmt" class="inactive">
                <h2>Account Management</h2>
                <?php if ($uid===0 || !$me): ?>
                    <p>Please log in first.</p>
                <?php else: ?>
                    <h3>Your Profile</h3>
                    <table>
                        <tr><th>ID</th><td><?php echo $me['id']; ?></td></tr>
                        <tr><th>Name</th><td><?php echo $me['fname']." ".$me['lname']; ?></td></tr>
                        <tr><th>Email</th><td><?php echo $me['email']; ?></td></tr>
                        <tr><th>Phone</th><td><?php echo $me['phone']; ?></td></tr>
                        <tr><th>Designation</th><td><?php echo $me['desi']; ?></td></tr>
                        <tr><th>Department</th><td><?php echo $me['dept']; ?></td></tr>
                        <tr><th>Status</th><td><?php echo $me['status']; ?></td></tr>
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

            <section id="assignManager" class="inactive">
                <h2>Assign Manager</h2>
                <?php if (!empty($_SESSION['assign_msg'])): ?>
                    <div class="msg"><?php echo $_SESSION['assign_msg']; unset($_SESSION['assign_msg']); ?></div>
                <?php endif; ?>
                <form method="POST">
                    <select name="dept" required>
                        <option value="">Select Department</option>
                        <option value="HR">HR</option>
                        <option value="Accounts">Accounts</option>
                        <option value="Engineering">Engineering</option>
                        <option value="Finance">Finance</option>
                    </select>
                    <select name="manager_id" required>
                        <option value="">Select Manager</option>
                        <?php if ($managers && $managers->num_rows>0): while($m=$managers->fetch_assoc()): ?>
                            <option value="<?php echo $m['id']; ?>"><?php echo $m['fname']." ".$m['lname']; ?></option>
                        <?php endwhile; endif; ?>
                    </select>
                    <button class="success" type="submit" name="assignManager">Assign Manager</button>
                </form>
            </section>

        </div>
    </div>

    <script>
        function showSection(section) {
            ["dashboard","users","createUser","statusUser","accountRecovery","accountMgmt","assignManager"].forEach(function(id){
                document.getElementById(id).classList.add("inactive");
            });
            document.getElementById(section).classList.remove("inactive");
        }

        window.addEventListener("load", function() {
            if (window.location.hash) {
                var section = window.location.hash.substring(1);
                if (document.getElementById(section)) { showSection(section); }
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
                        td.colSpan = 8;
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
