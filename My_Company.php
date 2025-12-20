<?php
include 'auth_session.php';
checkLogin();
include 'Database.php';

// Access Control
if ($_SESSION['Permission'] !== 'Company') {
    echo "<div class='content'><div class='alert'>Access Denied. Company account required.</div></div>";
    include 'includes/footer.php';
    exit();
}

$user_id = $_SESSION['User_ID'];

// 1. Get Company Details
$sql = "SELECT * FROM Company_T WHERE Company_User_ID = '$user_id'";
$company = $conn->query($sql)->fetch_assoc();

// 2. Get Stock Details
$stock = null;
$investors_result = null;
$shares_left = 0;

if ($company) {
    $sql_stock = "SELECT * FROM Stock_T WHERE Company_User_ID = " . $company['Company_User_ID'];
    $stock = $conn->query($sql_stock)->fetch_assoc();

    if ($stock) {
        $stock_id = $stock['Stock_ID'];
        
        // Calculate Shares Sold
        // Logic: Sum of all current holdings. 
        // Or simpler: Total Shares - (Sum of Buys - Sum of Sells in Transactions)?
        // Let's use Transactions to calculate sold/held shares.
        $sql_sold = "
            SELECT SUM(CASE WHEN Transaction_Type = 'buy' THEN Share_Amount ELSE -Share_Amount END) as Net_Sold
            FROM Stock_Transactions_T
            WHERE Stock_ID = '$stock_id'
        ";
        $sold_data = $conn->query($sql_sold)->fetch_assoc();
        $net_sold = $sold_data['Net_Sold'] ?? 0; // Net shares currently held by investors
        $shares_left = $stock['Total_Shares'] - $net_sold;

        // Get Investors List
        $sql_investors = "
            SELECT 
                u.Name as Investor_Name,
                SUM(CASE WHEN t.Transaction_Type = 'buy' THEN t.Share_Amount ELSE -t.Share_Amount END) as Owned_Shares
            FROM Stock_Transactions_T t
            JOIN Investor_T i ON t.Investor_User_ID = i.Investor_User_ID
            JOIN User_T u ON i.Investor_User_ID = u.User_ID
            WHERE t.Stock_ID = '$stock_id'
            GROUP BY i.Investor_User_ID, u.Name
            HAVING Owned_Shares > 0
            ORDER BY Owned_Shares DESC
        ";
        $investors_result = $conn->query($sql_investors);
    }
}

// 3. Get Audit Reports
$sql_audits = "SELECT * FROM Audit_Report_T WHERE Company_User_ID = '$user_id' ORDER BY Report_Date DESC";
$audits_result = $conn->query($sql_audits);

?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="page-header">
    <h2>Company Dashboard</h2>
</div>

<div class="content-section">
    <h3>Company Profile</h3>
    <p>
        <strong>Registration:</strong> <?php echo htmlspecialchars($company['Registration_Number'] ?? 'N/A'); ?> | 
        <strong>Sector:</strong> <?php echo htmlspecialchars($company['Sector'] ?? 'N/A'); ?>
    </p>
</div>

<!-- STOCK MANAGEMENT SECTION -->
<div class="content-section">
    <?php if (!$stock): ?>
        <!-- IPO FORM -->
        <div class="card" style="border: 2px solid var(--accent-gold);">
            <h3>🚀 Launch IPO (Go Public)</h3>
            <p>Your company does not have a stock listed yet. Create one to allow investors to buy shares.</p>
            <form action="stock_action.php" method="POST">
                <input type="hidden" name="action" value="ipo">
                <div class="input-group">
                    <label>Initial Price per Share ($)</label>
                    <input type="number" step="0.01" name="price" required placeholder="e.g. 10.00">
                </div>
                <div class="input-group">
                    <label>Total Shares to Issue</label>
                    <input type="number" name="total_shares" required placeholder="e.g. 1000000">
                </div>
                <button type="submit" class="btn-primary">Launch Stock</button>
            </form>
        </div>
    <?php else: ?>
        <!-- STOCK DASHBOARD -->
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px;">
            <div>
                <h3>Stock Overview (<?php echo htmlspecialchars($stock['Status'] ?? 'Open'); ?>)</h3>
                <p><strong>Current Price:</strong> $<?php echo number_format($stock['Current_Price'], 2); ?></p>
                <p><strong>Total Shares:</strong> <?php echo number_format($stock['Total_Shares']); ?></p>
                <p><strong>Available Shares:</strong> <?php echo number_format($shares_left); ?></p>
                <a href="Price_History.php?id=<?php echo $stock['Stock_ID']; ?>" class="btn-action" style="background:#28a745; color:white; border:none;">View Price History</a>
            </div>
            
            <!-- OPEN/CLOSE TOGGLE -->
             <form action="stock_action.php" method="POST">
                <input type="hidden" name="action" value="toggle_status">
                <input type="hidden" name="stock_id" value="<?php echo $stock['Stock_ID']; ?>">
                <input type="hidden" name="current_status" value="<?php echo $stock['Status'] ?? 'Open'; ?>">
                <?php if (($stock['Status'] ?? 'Open') == 'Open'): ?>
                    <button type="submit" class="btn-action" style="background-color: #28a745; color:white; border:none;">Limit Trading (Close)</button>
                    <small style="display:block; margin-top:5px; color:gray;">Prevents new buys.</small>
                <?php else: ?>
                    <button type="submit" class="btn-action" style="background-color: #28a745; color:white; border:none;">Open Trading</button>
                <?php endif; ?>
            </form>
        </div>

        <h4>Current Investors</h4>
        <div class="data-table-scroll-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Investor Name</th>
                        <th>Shares Owned</th>
                        <th>Ownership %</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($investors_result && $investors_result->num_rows > 0) {
                        while($inv = $investors_result->fetch_assoc()) {
                            $percent = ($inv['Owned_Shares'] / $stock['Total_Shares']) * 100;
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($inv['Investor_Name']) . "</td>";
                            echo "<td>" . number_format($inv['Owned_Shares']) . "</td>";
                            echo "<td>" . number_format($percent, 4) . "%</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3'>No investors yet.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- AUDIT REPORTS SECTION -->
<div class="content-section">
    <h3>My Audit Reports</h3>
    <div class="data-table-scroll-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Report ID</th>
                    <th>Date</th>
                    <th>Findings</th>
                    <th>Auditing Firm</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($audits_result && $audits_result->num_rows > 0) {
                    while($audit = $audits_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $audit['Report_ID'] . "</td>";
                        echo "<td>" . $audit['Report_Date'] . "</td>";
                        echo "<td>" . htmlspecialchars($audit['Findings_Summary']) . "</td>";
                        echo "<td>" . htmlspecialchars($audit['Auditing_Firm']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No audit reports found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>