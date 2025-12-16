<?php include 'Database.php';

$sql = "
SELECT investor_t.Investor_User_ID, user_t.Name, user_t.Password, user_t.Email_Address, user_t.Status
FROM user_t,investor_t
WHERE Investor_User_ID = user_t.User_ID
ORDER BY investor_t.Investor_User_ID
";
$result = $conn->query($sql);
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
            <li><a href="Employee_Database.php">Employee Database</a></li>
            <li><a href="Frauds.php">Fraud Alerts</a></li>
            <li><a href="Investor_Database.php" class="active">Investor Database</a></li>
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

         <h2 id="summary">Investors</h2>
         <div class="data-table-scroll-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Investor ID</th>
                        <th>Name</th>
                        <th>Password</th>
                        <th>Email</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if ($result && $result->num_rows > 0) {
                        // Loop through each row of the result set
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['Investor_User_ID']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Password']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Email_Address']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
                                echo "</tr>";
                            }
                        }
                        else {
                        // Display this if no results are found
                        echo "<tr><td colspan='5'>No Investors found.</td></tr>";
                        }
                    ?>
                </tbody>
            </table>
        </div>
        <?php $conn->close(); ?>
    </div>
</body>
</html>