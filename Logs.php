<?php include 'Database.php'; 
$sql = "
SELECT log_t.Log_ID,log_t.Activity_Type,log_t.Timestamp,log_t.Activity_Data_Detail
FROM log_t
ORDER BY log_t.Timestamp
";
$Logs = $conn->query($sql);
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
            <li><a href="Investor_Database.php">Investor Database</a></li>
            <li><a href="Logs.php" class="active">All Logs</a></li>
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

        <h2 id="summary">All Logs</h2>
        <div class="data-table-scroll-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Log ID</th>
                        <th>Activity Type</th>
                        <th>Timestamp</th>
                        <th>Activity Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if ($Logs && $Logs->num_rows > 0) {
                            while($row = $Logs->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>$" . htmlspecialchars($row['Log_ID']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Activity_Type']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Timestamp']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Activity_Data_Detail']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>No Logs found.</td></tr>";
                        }
                    ?>
                </tbody>
            </table>
            <?php $conn->close(); ?>
        </div>
    </div>
</body>
</html>