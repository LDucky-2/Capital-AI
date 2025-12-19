<?php include 'Database.php';

$sql = "
SELECT user_t.User_ID,user_t.Name,user_t.Password,user_t.Email_Address,user_t.Status
FROM user_t
WHERE Permission = 'Database Manager'
ORDER BY user_t.User_ID
";
$Database_Managers = $conn->query($sql);
$sql = "
SELECT user_t.User_ID,user_t.Name,user_t.Password,user_t.Email_Address,user_t.Status,auditor_t.Auditing_Firm
FROM user_t,auditor_t
WHERE Permission = 'Auditors' AND auditor_t.Auditor_User_ID = user_t.User_ID
ORDER BY user_t.User_ID
";
$Auditors = $conn->query($sql);
$sql = "
SELECT user_t.User_ID,user_t.Name,user_t.Password,user_t.Email_Address,user_t.Status
FROM user_t
WHERE Permission = 'Fraud Detector'
ORDER BY user_t.User_ID
";
$Fraud_Detectors = $conn->query($sql);
$sql = "
SELECT user_t.User_ID,user_t.Name,user_t.Password,user_t.Email_Address,user_t.Status
FROM user_t
WHERE Permission = 'Administrator'
ORDER BY user_t.User_ID
";
$System_Administrators = $conn->query($sql);
$sql = "
SELECT user_t.User_ID,user_t.Name,user_t.Password,user_t.Email_Address,user_t.Status
FROM user_t
WHERE Permission = 'Management'
ORDER BY user_t.User_ID
";
$Management = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unified Admin Dashboard</title>
    <link rel="stylesheet" href="Styles.css">
</head>
<body>

    <div class="navbar">
        <ul>
            <li><a href="Audits.php">Audit Reports</a></li>
            <li><a href="Company_Database.php">Company Database</a></li>
            <li><a href="Employee_Database.php" class="active">Employee Database</a></li>
            <li><a href="Frauds.php">Fraud Alerts</a></li>
            <li><a href="Investor_Database.php">Investor Database</a></li>
            <li><a href="Logs.php">All Logs</a></li>
            <li><a href="My_Company.php">My Company</a></li>
            <li><a href="My_Institution.php">My Institution</a></li>
            <li><a href="My_Stocks.php">My Stocks</a></li>
            <li><a href="Predictions.php">Stock Prediction</a></li>
            <li><a href="Stock_Transactions_and_Trades.php">Stock Transactions and Trades Database</a></li>
            <li><a href="Stocks.php">All Stocks</a></li>
            <li><a href="Institution_Database.php">Institutions</a></li>
            <!-- <li><a href="Employee_Database.php">Employee Database</a></li> -->
            <!-- <li><a href="Employee_Database.php">Employee Database</a></li>  -->
            <!-- <li><a href="Log_in.php">Log In Page</a></li> -->
        </ul>
    </div>

    <div class = "content">
        <header class="top-brand-header">
            <div class="logo-wrap">
                <img src="images/Skyrim_Logo.png" alt="Brand Logo" class="brand-logo"> 
                <div class="brand-text-wrap">
                    <span class="brand-name">Financial Institutions Management</span>
                </div>
            </div>
        </header>

        <h2 id="summary">Database Managers</h2>
        <div class="data-table-scroll-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Database Manager ID</th>
                        <th>Name</th>
                        <th>Password</th>
                        <th>Email</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    if ($Database_Managers && $Database_Managers->num_rows > 0) {
                        while($row = $Database_Managers->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['User_ID']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Password']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Email_Address']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No Database manager found.</td></tr>";
                    }
                ?>
                </tbody>
            </table>
        </div>
        

        <h2 id="summary">Auditors</h2>
        <div class="data-table-scroll-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Auditor ID</th>
                        <th>Name</th>
                        <th>Password</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Auditing Firm</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    if ($Auditors && $Auditors->num_rows > 0) {
                        while($row = $Auditors->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['User_ID']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Password']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Email_Address']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Auditing_Firm']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6'>No Auditors found.</td></tr>";
                    }
                ?>
                </tbody>
            </table>
            
        </div>

        <h2 id="summary">Fraud Detectors</h2>
        <div class="data-table-scroll-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Password</th>
                        <th>Email</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    if ($Fraud_Detectors && $Fraud_Detectors->num_rows > 0) {
                        while($row = $Fraud_Detectors->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['User_ID']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Password']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Email_Address']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No Fraud detectors found.</td></tr>";
                    }
                ?>
                </tbody>
            </table>
            
        </div>

        <h2 id="summary">System Administrators</h2>
        <div class="data-table-scroll-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Password</th>
                        <th>Email</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    if ($System_Administrators && $System_Administrators->num_rows > 0) {
                        while($row = $System_Administrators->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['User_ID']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Password']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Email_Address']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No System admin found.</td></tr>";
                    }
                ?>
                </tbody>
            </table>
            
        </div>

        <h2 id="summary">Management</h2>
        <div class="data-table-scroll-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Password</th>
                        <th>Email</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    if ($Management && $Management->num_rows > 0) {
                        while($row = $Management->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['User_ID']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Password']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Email_Address']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No Management decision maker found.</td></tr>";
                    }
                ?>
                </tbody>
            </table>
            <?php $conn->close(); ?>
        </div>
    </div>

</body>
</html>