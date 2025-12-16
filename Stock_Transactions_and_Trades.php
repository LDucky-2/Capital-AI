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
            <li><a href="Logs.php">All Logs</a></li>
            <li><a href="My_Company.php">My Company</a></li>
            <li><a href="My_Institution.php">My Institution</a></li>
            <li><a href="My_Stocks.php">My Stocks</a></li>
            <li><a href="Predictions.php">Stock Prediction</a></li>
            <li><a href="Stock_Transactions_and_Trades.php" class="active">Stock Transactions and Trades Database</a></li>
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

        <h2 id="summary">📊 Data Summary</h2>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Metric</th>
                    <th>Q1 Target</th>
                    <th>Q1 Actual</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>New Users</td>
                    <td>500</td>
                    <td>550</td>
                    <td>Achieved</td>
                </tr>
                <tr>
                    <td>Revenue (k)</td>
                    <td>$250</td>
                    <td>$245</td>
                    <td>Near Target</td>
                </tr>
                <tr>
                    <td>Conversion Rate</td>
                    <td>3.5%</td>
                    <td>3.8%</td>
                    <td>Achieved</td>
                </tr>
                <tr>
                    <td>Support Tickets</td>
                    <td>50</td>
                    <td>65</td>
                    <td>Over Target</td>
                </tr>
            </tbody>
        </table>

        <h2 id="services">Services Section</h2>
        <p>Details about services offered.</p>
        <h2 id="contact">Contact Section</h2>
        <p>How to get in touch.</p>
    </div>

</body>
</html>