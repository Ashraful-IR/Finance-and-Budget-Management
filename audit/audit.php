<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Auditor</title>
    <link rel="stylesheet" href="../Audit/audit.css" type="">
</head>

<body>
    <div class="container">

        <div class="menu">
            <div class="menu-header">
                <h2>Auditor</h2>
                <div class="menu-toggle" onclick="toggleMenu()">
                    <ion-icon name="menu-outline"></ion-icon>
                </div>
            </div>
            <a href="#" onclick="showSection('dashboard')">
                <ion-icon name="home-outline"></ion-icon> Dashboard
            </a>
            <a href="#" onclick="showSection('Add')">
                <ion-icon name="add-circle-outline"></ion-icon> Add
            </a>
            <a href="#" onclick="showSection('Authorize')">
                <ion-icon name="checkmark-done-outline"></ion-icon> Authorize
            </a>
            <a href="#" onclick="showSection('Balance')">
                <ion-icon name="wallet-outline"></ion-icon> Balance
            </a>
            <a href="#" onclick="showSection('Transactions')">
                <ion-icon name="list-outline"></ion-icon> Transactions
            </a>
            <a href="#" onclick="showSection('Generate_reports')">
                <ion-icon name="document-text-outline"></ion-icon> Reports
            </a>
            <a href="#">
                <ion-icon name="person-outline"></ion-icon> Account
            </a>
        </div>
        

    </div>
    
    <div class="content"> 
        <h1>Welcome to the Auditor Dashboard</h1>
        <p>Select an option from the menu to get started.</p>
    </div>


    




    <script>
    function toggleMenu() {
        const menu = document.querySelector('.menu');
        menu.classList.toggle('active');
    }
    </script>

    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>

</html>