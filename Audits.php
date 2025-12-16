<?php include 'Database.php';
$sql = "
SELECT audit_report_t.Auditing_Firm, audit_report_t.Auditor_User_ID, user_t.Name, audit_report_t.Report_ID, audit_report_t.Report_Date, audit_report_t.Findings_Summary
FROM audit_report_t,user_t
WHERE audit_report_t.Auditor_User_ID = user_t.User_ID
ORDER BY audit_report_t.Report_Date
";
$Audit_Report = $conn->query($sql);
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
            <li><a href="Audits.php" class="active">Audit Reports</a></li>
            <li><a href="Company_Database.php">Company Database</a></li>
            <li><a href="Employee_Database.php">Employee Database</a></li>
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

        <h2 id="summary">Audit Reports</h2>
        <div class = "data-table-scroll-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Auditing Firm</th>
                        <th>Auditor ID</th>
                        <th>Auditor Name</th>
                        <th>Audit ID</th>
                        <th>Report Date</th>
                        <th>Findings Summary</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if ($Audit_Report && $Audit_Report->num_rows > 0) {
                        // Loop through each row of the result set
                            while($row = $Audit_Report->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['Auditing_Firm']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Auditor_User_ID']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Report_ID']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Report_Date']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['Findings_Summary']) . "</td>";
                                echo "</tr>";
                            }
                        }
                        else {
                        // Display this if no results are found
                        echo "<tr><td colspan='6'>No audit records found.</td></tr>";
                        }
                    ?>
                </tbody>
            </table>
        </div>
        <?php $conn->close(); ?>
    </div>

</body>
</html>