<?php
include 'auth_session.php';
checkLogin();
include 'Database.php';

if (!isset($_GET['id'])) {
    header("Location: Stocks.php");
    exit();
}

$stock_id = intval($_GET['id']);

// 1. Fetch Stock & Company Details
$stock_sql = "
    SELECT 
        s.Stock_ID, 
        s.Current_Price, 
        s.Total_Shares,
        c.Company_User_ID,
        c.Registration_Number, 
        c.Sector,
        u.Name as Company_Name,
        u.Email_Address
    FROM Stock_T s 
    JOIN Company_T c ON s.Company_User_ID = c.Company_User_ID 
    JOIN User_T u ON c.Company_User_ID = u.User_ID
    WHERE s.Stock_ID = '$stock_id'
";
$stock_result = $conn->query($stock_sql);
$stock = $stock_result->fetch_assoc();

if (!$stock) {
    die("Stock not found.");
}

// 2. Fetch Price History
$history_sql = "SELECT * FROM Price_History_T WHERE Stock_ID = '$stock_id' ORDER BY Date DESC LIMIT 20";
$history_result = $conn->query($history_sql);

// 3. Fetch Audit Reports for this Company
$company_id = $stock['Company_User_ID'];
$audit_sql = "
    SELECT 
        r.Report_ID, 
        r.Report_Date, 
        r.Findings_Summary, 
        r.Auditing_Firm,
        u_auditor.Name as Auditor_Name
    FROM Audit_Report_T r
    JOIN Auditor_T a ON r.Auditor_User_ID = a.Auditor_User_ID
    JOIN User_T u_auditor ON a.Auditor_User_ID = u_auditor.User_ID
    WHERE r.Company_User_ID = '$company_id'
    ORDER BY r.Report_Date DESC
";
$audit_result = $conn->query($audit_sql);
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="page-header">
    <a href="Stocks.php" class="btn-action" style="float:right; background:#28a745; color:white; border:none;">&larr; Back to Browse</a>
    <h2>Stock Details: <?php echo htmlspecialchars($stock['Company_Name']); ?></h2>
</div>

<!-- Grid Layout -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">

    <!-- Company Info Card -->
    <div class="card">
        <h3>Company Information</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($stock['Company_Name']); ?></p>
        <p><strong>Sector:</strong> <?php echo htmlspecialchars($stock['Sector']); ?></p>
        <p><strong>Registration:</strong> <?php echo htmlspecialchars($stock['Registration_Number']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($stock['Email_Address']); ?></p>
    </div>

    <!-- Stock Status Card -->
    <div class="card">
        <h3>Market Status</h3>
        <p><strong>Current Price:</strong> <span style="font-size: 1.2rem; color: var(--primary-main); font-weight: bold;">$<?php echo number_format($stock['Current_Price'], 2); ?></span></p>
        <p><strong>Total Shares:</strong> <?php echo number_format($stock['Total_Shares']); ?></p>
        <p><strong>Market Cap:</strong> $<?php echo number_format($stock['Total_Shares'] * $stock['Current_Price'], 2); ?></p>
    </div>

</div>

<!-- Price History Section -->
<div class="content-section">
    <h3>Price History</h3>
    <div class="data-table-scroll-wrapper" style="max-height: 300px;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Open</th>
                    <th>Close</th>
                    <th>High</th>
                    <th>Low</th>
                    <th>Volume</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($history_result && $history_result->num_rows > 0) {
                    while($row = $history_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['Date'] . "</td>";
                        echo "<td>$" . number_format($row['Open_Price'], 2) . "</td>";
                        echo "<td>$" . number_format($row['Close_Price'], 2) . "</td>";
                        echo "<td>$" . number_format($row['High'], 2) . "</td>";
                        echo "<td>$" . number_format($row['Low'], 2) . "</td>";
                        echo "<td>" . number_format($row['Volume']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align:center;'>No history data available.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Audit Reports Section -->
<div class="content-section">
    <h3>Company Audit Reports</h3>
    <div class="data-table-scroll-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Report ID</th>
                    <th>Date</th>
                    <th>Auditor</th>
                    <th>Firm</th>
                    <th>Findings</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($audit_result && $audit_result->num_rows > 0) {
                    while($row = $audit_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['Report_ID'] . "</td>";
                        echo "<td>" . $row['Report_Date'] . "</td>";
                        echo "<td>" . htmlspecialchars($row['Auditor_Name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Auditing_Firm']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Findings_Summary']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align:center;'>No audit reports filed for this company.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
