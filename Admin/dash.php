<?php
include "configdb.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['createUser'])) {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];
    $designation = $_POST['designation'];
    $department = $_POST['department'];

    if ($password !== $cpassword) {
        $error = "Passwords do not match!";
    } else {
        $sql_insert = "INSERT INTO Users (fname, lname, email, phone, pass, desi, dept) 
                        VALUES ('$fname', '$lname', '$email', '$phone', '$password', '$designation', '$department')";

        if ($conn->query($sql_insert) === TRUE) {
            header("Location: dash.php");
            exit();
        } else {
            $error = "Error: " . $sql_insert . "<br>" . $conn->error;
        }
    }
}

$sql = "SELECT * FROM Users"; 
$result = $conn->query($sql);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - User Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashstyle.css" type="text/css">
    <style>
        .active {
            display: block;
        }
        .inactive {
            display: none;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <h2>Admin</h2>
        <a href="#" id="dashboardLink" onclick="showSection('dashboard')">Dashboard</a>
        <a href="#" id="usersLink" onclick="showSection('users')">Users</a>
        <a href="#" id="createUserLink" onclick="showSection('createUser')">Create User</a>
        <a href="#">Departments</a>
        <a href="#">Roles</a>
        <a href="#">Settings</a>
        <a href="#">Account Management</a>
    </div>

    <div class="main-content">
        <header>
            <h1>Admin Dashboard</h1>
        </header>

        <div class="container">
            <section id="dashboard" class="active">
                <h2>Welcome to the Dashboard</h2>
                <p>Here is your general dashboard content...</p>
            </section>

            <section id="users" class="inactive">
                <h2>All Users</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Designation</th>
                            <th>Department</th>
                            <th>Password</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row['id'] . "</td>";
                                echo "<td>" . $row['fname'] . "</td>";
                                echo "<td>" . $row['lname'] . "</td>";
                                echo "<td>" . $row['email'] . "</td>";
                                echo "<td>" . $row['phone'] . "</td>";
                                echo "<td>" . $row['desi'] . "</td>";
                                echo "<td>" . $row['dept'] . "</td>";
                                echo "<td>" . $row['pass'] . "</td>";
                                echo "<td>
                                        <button class='danger'>Deactivate</button>
                                        <button class='danger'>Delete</button>
                                    </td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='9'>No users found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </section>

            <section id="createUser" class="inactive">
                <h2>Create New User</h2>

                <?php if (isset($success)): ?>
                    <p class="success"><?php echo $success; ?></p>
                <?php endif; ?>

                <?php if (isset($error)): ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endif; ?>

                <form method="POST" action="">
                    <label>First Name:</label>
                    <input type="text" name="fname" placeholder="First Name" required>

                    <label>Last Name:</label>
                    <input type="text" name="lname" placeholder="Last Name" required>

                    <label>Email:</label>
                    <input type="email" name="email" placeholder="Email" required>

                    <label>Phone Number:</label>
                    <input type="tel" name="phone" placeholder="Phone Number" required>

                    <label>Password:</label>
                    <input type="password" name="password" placeholder="Password" required>

                    <label>Confirm Password:</label>
                    <input type="password" name="cpassword" placeholder="Confirm Password" required>

                    <label>Designation:</label>
                    <select name="designation" required>
                        <option value="admin">Admin</option>
                        <option value="manager">Manager</option>
                        <option value="employee">Employee</option>
                        <option value="auditor">Auditor</option>
                    </select>

                    <label>Department:</label>
                    <select name="department" required>
                        <option value="sales">Sales</option>
                        <option value="finance">Finance</option>
                        <option value="engineering">Engineering</option>
                        <option value="hr">HR</option>
                    </select>

                    <button class="primary" type="submit" name="createUser">Create User</button>
                </form>
            </section>

        </div>
    </div>

    <script>
        function showSection(section) {
            document.getElementById("dashboard").classList.add("inactive");
            document.getElementById("users").classList.add("inactive");
            document.getElementById("createUser").classList.add("inactive");

            document.getElementById(section).classList.remove("inactive");
        }
    </script>

</body>

</html>

<?php
$conn->close();
?>
