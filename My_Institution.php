<?php
include 'auth_session.php';
checkLogin();
include 'Database.php';

$user_id = $_SESSION['User_ID'];

// Fetch Institution Details
$sql = "
    SELECT 
        u.Name, 
        u.Email_Address, 
        u.Status, 
        i.Institution_Type, 
        i.License_Number
    FROM Institution_T i
    JOIN User_T u ON i.Institution_User_ID = u.User_ID
    WHERE i.Institution_User_ID = '$user_id'
";
$result = $conn->query($sql);
$data = $result->fetch_assoc();

if (!$data) {
    // If not an institution or not found
    $error = "Institution profile not found.";
}
// Handle Trade With logic
$trade_with = isset($_GET['trade_with']) ? intval($_GET['trade_with']) : null;
$seller_name = "";
if ($trade_with) {
    $seller_sql = "SELECT Name FROM User_T WHERE User_ID = '$trade_with'";
    $seller_res = $conn->query($seller_sql);
    if ($seller_res && $seller_res->num_rows > 0) {
        $seller_row = $seller_res->fetch_assoc();
        $seller_name = $seller_row['Name'];
    }
}

// Fetch Recent Trades
$trades_sql = "
    SELECT 
        t.Trade_ID,
        buyer_u.Name as Buyer_Name,
        seller_u.Name as Seller_Name,
        t.Asset_Type,
        t.Trade_Details,
        l.Timestamp
    FROM Trade_T t
    JOIN User_T buyer_u ON t.Buyer_Institution_ID = buyer_u.User_ID
    JOIN User_T seller_u ON t.Seller_Institution_ID = seller_u.User_ID
    LEFT JOIN Log_T l ON t.Log_ID = l.Log_ID
    WHERE t.Buyer_Institution_ID = '$user_id' OR t.Seller_Institution_ID = '$user_id'
    ORDER BY t.Trade_ID DESC
    LIMIT 5
";
$trades_result = $conn->query($trades_sql);

// Fetch My Audits
$audits_sql = "SELECT * FROM Audit_Report_T WHERE Institution_User_ID = '$user_id' ORDER BY Report_Date DESC";
$audits_result = $conn->query($audits_sql);
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="page-header">
    <h2>My Institution Profile</h2>
</div>

<div class="content-section">
    <?php if (isset($error)) { echo "<div class='alert' style='color:red;'>$error</div>"; } else { ?>
    
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
        <div class="alert alert-success">Trade request submitted successfully!</div>
    <?php endif; ?>

    <div class="grid-container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="card">
            <h3>Profile Info</h3>
            <p><strong>Name:</strong> <?php echo htmlspecialchars($data['Name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($data['Email_Address']); ?></p>
            <p><strong>Type:</strong> <?php echo htmlspecialchars($data['Institution_Type']); ?></p>
            <p><strong>License Number:</strong> <?php echo htmlspecialchars($data['License_Number']); ?></p>
            <p><strong>Status:</strong> <span class="badge"><?php echo htmlspecialchars($data['Status']); ?></span></p>
        </div>

        <?php if ($trade_with): ?>
        <div class="card">
            <h3>Initiate Trade</h3>
            <p>Trading with: <strong><?php echo htmlspecialchars($seller_name); ?></strong></p>
            <form action="trade_action.php" method="POST">
                <input type="hidden" name="action" value="create_trade">
                <input type="hidden" name="seller_id" value="<?php echo $trade_with; ?>">
                
                <div class="input-group">
                    <label>Asset Type</label>
                    <input type="text" name="asset_type" required placeholder="e.g. Government Bonds, Corporate Debt">
                </div>
                <div class="input-group">
                    <label>Trade Details</label>
                    <textarea name="trade_details" required placeholder="Quantity, Price, Terms..." style="width:100%; min-height:80px; padding:12px; border:1px solid var(--border-color); color: var(--text-main); font-family: inherit;"></textarea>
                </div>
                <button type="submit" class="btn-primary" style="margin-top:10px;">Execute Trade</button>
                <a href="My_Institution.php" class="btn-action" style="background:#666; margin-top:10px; text-align:center; display:block; margin-right:0;">Cancel</a>
            </form>
        </div>
        <?php else: ?>
        <div class="card">
            <h3>Quick Actions</h3>
            <p>Visit the Institution Database to initiate trades with other institutions.</p>
            <a href="Institution_Database.php" class="btn-action" style="display: inline-block; background:#28a745; color:white; border:none;">Browse Institutions</a>
        </div>
        <?php endif; ?>
    </div>

    <div class="card" style="margin-top: 20px;">
        <h3>Recent Institutional Trades</h3>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Buyer</th>
                    <th>Seller</th>
                    <th>Asset</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($trades_result && $trades_result->num_rows > 0) {
                    while($row = $trades_result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . ($row['Timestamp'] ?: 'N/A') . "</td>";
                        echo "<td>" . htmlspecialchars($row['Buyer_Name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Seller_Name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Asset_Type']) . "</td>";
                        echo "<td><div style='max-width:300px; font-size:0.9em;'>" . nl2br(htmlspecialchars($row['Trade_Details'])) . "</div></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align:center;'>No institutional trades found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <div class="card" style="margin-top: 20px;">
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
                        echo "<tr><td colspan='4' style='text-align:center;'>No audit reports found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php } ?>
</div>

<?php include 'includes/footer.php'; ?>
