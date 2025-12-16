<?php include 'Database.php';

$sql = "
SELECT *
FROM fraud_alert_t
ORDER BY fraud_alert_t.Alert_ID
";
$Fraud_Alerts = $conn->query($sql);
$sql = "
SELECT fraud_action_t.Alert_ID,fraud_action_t.Action_taken,fraud_action_t.Notes,fraud_action_t.Timestamp
FROM fraud_action_t
ORDER BY fraud_action_t.Timestamp
";
$Fraud_Actions = $conn->query($sql);
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
            <li><a href="Frauds.php" class="active">Fraud Alerts</a></li>
            <li><a href="Investor_Database.php">Investor Database</a></li>
            <li><a href="Logs.php">All Logs</a></li>
            <li><a href="My_Company.php">My Company</a></li>
            <li><a href="My_Institution.php">My Institution</a></li>
            <li><a href="My_Stocks.php">My Stocks</a></li>
            <li><a href="Predictions.php">Stock Prediction</a></li>
            <li><a href="Stock_Transactions_and_Trades.php">Stock Transactions and Trades Database</a></li>
            <li><a href="Stocks.php">All Stocks</a></li>
            <li><a href="Institution_Database.php">Institutions</a></li>
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

        <h2 id="summary">Fraud Alerts</h2>
        <div class="data-table-scroll-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Alert ID</th>
                        <th>Pattern Detected</th>
                        <th>Risk Score</th>
                        <th>Is False Positive</th>
                        <th>Target Log</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if ($Fraud_Alerts && $Fraud_Alerts->num_rows > 0) {
                            while($row = $Fraud_Alerts->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['Alert_ID']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Pattern_Detected']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Risk_Score']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Is_False_Positive']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Log_ID']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No Fraud records found.</td></tr>";
                        }
                    ?>
                </tbody>
            </table>
        </div>
        <h2 id="summary">Fraud Actions</h2>
        <div class="data-table-scroll-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Alert ID</th>
                        <th>Action Taken</th>
                        <th>Notes</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if ($Fraud_Actions && $Fraud_Actions->num_rows > 0) {
                            while($row = $Fraud_Actions->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['Alert_ID']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Action_taken']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Notes']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Timestamp']) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4'>No fraud action records found.</td></tr>";
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>