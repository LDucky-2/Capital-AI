<?php
include 'auth_session.php';
checkLogin();
include 'Database.php';

// Block Auditor access
if (hasRole('Auditor')) {
    echo "<div class='content'><div class='alert'>Access Denied. Auditors are not permitted to view transaction history.</div></div>";
    include 'includes/footer.php';
    exit();
}

// Fetch Data based on Role
$isAdmin = hasRole('Administrator');
$isManagement = hasRole('Management');
$isInstitution = hasRole('Institution');
$isInvestor = hasRole('Investor');
$isFraudDetector = hasRole('Fraud Detector');

$stock_result = null;
$trade_result = null;

// 1. Stock Transactions Query (Investor / Targeted Admin / Fraud Detector)
if (($isAdmin || $isManagement || $isInvestor || $isFraudDetector) && !isset($_GET['institution_id'])) {
    if (isset($_GET['investor_id'])) {
        $target_id = intval($_GET['investor_id']);
        $where_stock = "t.Investor_User_ID = '$target_id'";
        
        $u_res = $conn->query("SELECT Name FROM User_T WHERE User_ID = '$target_id'");
        $target_name = ($u_res->fetch_assoc())['Name'] ?? 'Unknown';
        $page_title_stock = "Transactions for " . htmlspecialchars($target_name);
    } elseif (isset($_GET['company_id'])) {
        $target_id = intval($_GET['company_id']);
        $where_stock = "s.Company_User_ID = '$target_id'";
        
        $u_res = $conn->query("SELECT Name FROM User_T WHERE User_ID = '$target_id'");
        $target_name = ($u_res->fetch_assoc())['Name'] ?? 'Unknown';
        $page_title_stock = "Transactions for " . htmlspecialchars($target_name);
    } else {
        $where_stock = ($isAdmin || $isFraudDetector) ? "1=1" : "t.Investor_User_ID = '{$_SESSION['User_ID']}'";
        if ($isManagement) {
            $where_stock = "t.Investor_User_ID IN (SELECT User_ID FROM User_T WHERE Permission = 'Management')";
        }
        $page_title_stock = "Stock Transaction History";
    }

    $sql_stock = "
        SELECT 
            t.Transaction_ID,
            t.Transaction_Type,
            t.Share_Amount,
            s.Stock_ID,
            comp_user.Name as Company_Name,
            inv_user.Name as Investor_Name,
            l.Timestamp
        FROM Stock_Transactions_T t
        JOIN Stock_T s ON t.Stock_ID = s.Stock_ID
        JOIN Company_T c ON s.Company_User_ID = c.Company_User_ID
        JOIN User_T comp_user ON c.Company_User_ID = comp_user.User_ID
        JOIN Investor_T i ON t.Investor_User_ID = i.Investor_User_ID
        JOIN User_T inv_user ON i.Investor_User_ID = inv_user.User_ID
        LEFT JOIN Log_T l ON t.Log_ID = l.Log_ID
        WHERE $where_stock
        ORDER BY t.Transaction_ID DESC
    ";
    $stock_result = $conn->query($sql_stock);
}

// 2. Institutional Trades Query (Institution / Targeted Admin / Fraud Detector)
if (($isAdmin || $isInstitution || $isFraudDetector) && !isset($_GET['investor_id']) && !isset($_GET['company_id'])) {
    if (isset($_GET['institution_id'])) {
        $target_id = intval($_GET['institution_id']);
        $where_trade = "(t.Buyer_Institution_ID = '$target_id' OR t.Seller_Institution_ID = '$target_id')";
        
        $u_res = $conn->query("SELECT Name FROM User_T WHERE User_ID = '$target_id'");
        $target_name = ($u_res->fetch_assoc())['Name'] ?? 'Unknown';
        $page_title_trade = "Trades for " . htmlspecialchars($target_name);
    } else {
        $where_trade = ($isAdmin || $isManagement || $isFraudDetector) ? "1=1" : "(t.Buyer_Institution_ID = '{$_SESSION['User_ID']}' OR t.Seller_Institution_ID = '{$_SESSION['User_ID']}')";
        $page_title_trade = "Institutional Trade History";
    }

    $sql_trade = "
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
        WHERE $where_trade
        ORDER BY t.Trade_ID DESC
    ";
    $trade_result = $conn->query($sql_trade);
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="page-header">
    <h2><?php 
        if ($isAdmin || $isManagement) {
            echo "Targeted Audit: " . ($page_title_stock ?? ($page_title_trade ?? "History"));
        } else {
            echo "Transaction & Trade History";
        }
    ?></h2>
</div>

<?php if ($stock_result): ?>
<div class="content-section" style="margin-bottom: 40px;">
    <h3 style="margin-bottom: 15px; color: var(--accent-gold);"><i class="fas fa-user"></i> Stock Transactions (Investors)</h3>
    <div class="data-table-scroll-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Investor</th>
                    <th>Company</th>
                    <th>Stock ID</th>
                    <th>Type</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($stock_result->num_rows > 0) {
                    while($row = $stock_result->fetch_assoc()) {
                        $time = $row['Timestamp'] ?: 'N/A';
                        $typeClass = ($row['Transaction_Type'] == 'buy') ? 'success' : 'danger';
                        echo "<tr>";
                        echo "<td>" . $time . "</td>";
                        echo "<td>" . htmlspecialchars($row['Investor_Name'] ?? 'N/A') . "</td>";
                        echo "<td>" . htmlspecialchars($row['Company_Name']) . "</td>";
                        echo "<td>" . $row['Stock_ID'] . "</td>";
                        echo "<td><span class='badge badge-$typeClass' style='text-transform:uppercase;'>" . $row['Transaction_Type'] . "</span></td>";
                        echo "<td>" . number_format($row['Share_Amount']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align:center;'>No stock transactions found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if ($trade_result): ?>
<div class="content-section">
    <h3 style="margin-bottom: 15px; color: var(--accent-gold);"><i class="fas fa-building"></i> Institutional Trades</h3>
    <div class="data-table-scroll-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Buyer</th>
                    <th>Seller</th>
                    <th>Asset Type</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($trade_result->num_rows > 0) {
                    while($row = $trade_result->fetch_assoc()) {
                        $time = $row['Timestamp'] ?: 'N/A';
                        echo "<tr>";
                        echo "<td>" . $time . "</td>";
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
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
