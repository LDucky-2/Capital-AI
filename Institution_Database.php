<?php include 'Database.php';
$sql = "
SELECT institution_t.Institution_User_ID,user_t.Name,user_t.Password,user_t.Email_Address,institution_t.License_Number,institution_t.Institution_Type,user_t.Status
FROM institution_t,user_t
WHERE institution_t.Institution_User_ID = user_t.User_ID
ORDER BY institution_t.Institution_User_ID
";
$Institutions = $conn->query($sql);
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
            <li><a href="Investor_Database.php">Investor Database</a></li>
            <li><a href="Frauds.php">Fraud Alerts</a></li>
            <li><a href="Logs.php">All Logs</a></li>
            <li><a href="My_Company.php">My Company</a></li>
            <li><a href="My_Institution.php">My Institution</a></li>
            <li><a href="My_Stocks.php">My Stocks</a></li>
            <li><a href="Predictions.php">Stock Prediction</a></li>
            <li><a href="Stock_Transactions_and_Trades.php">Stock Transactions and Trades Database</a></li>
            <li><a href="Stocks.php">All Stocks</a></li>
            <li><a href="Institution_Database.php" class = "active">Institutions</a></li>
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

        <h2 id="summary">Institutions</h2>
        <div class = "data-table-scroll-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Institution ID</th>
                        <th>Name</th>
                        <th>Password</th>
                        <th>Email</th>
                        <th>License Number</th>
                        <th>Institution Type</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    if ($Institutions && $Institutions->num_rows > 0) {
                        while($row = $Institutions->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>$" . htmlspecialchars($row['Institution_User_ID']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Password']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Email']) . "</td>";
                            echo "<td>$" . htmlspecialchars($row['License_Number']) . "</td>";
                            echo "<td>$" . htmlspecialchars($row['Institution_Type']) . "</td>";
                            echo "<td>$" . htmlspecialchars($row['Status']) . "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7'>No Institutions found.</td></tr>";
                    }
                ?>
                </tbody>
            </table>
            <?php $conn->close(); ?>
        </div>
    </div>
</body>
</html>