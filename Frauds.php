<?php
include 'auth_session.php';
checkLogin();

// RESTRICT ACCESS: Only allow specific roles
$allowed_roles = ['Fraud Detector', 'Administrator'];
if (!hasRole($allowed_roles)) {
    // Option 1: Redirect to a 'Forbidden' page or Dashboard
    // header("Location: Audits.php?error=access_denied");
    // exit();
    
    // Option 2: Show inline error (for demo purposes, clearer to see)
    include 'includes/header.php';
    include 'includes/sidebar.php';
    echo '<div style="padding: 2rem; color: #ef4444;">
            <h2><i class="fas fa-lock"></i> Access Denied</h2>
            <p>You do not have permission to view this page. Required Roles: ' . implode(', ', $allowed_roles) . '</p>
            <a href="Audits.php" class="btn-primary" style="display:inline-block; margin-top:1rem; text-decoration:none;">Return to Dashboard</a>
          </div>';
    include 'includes/footer.php';
    exit();
}

include 'Database.php';

$sql_alerts = "
    SELECT a.*, u.Name as User_Name, u.Permission as User_Role 
    FROM fraud_alert_t a 
    LEFT JOIN User_T u ON a.Targeted_User_ID = u.User_ID 
    ORDER BY a.Alert_ID DESC
";
$Fraud_Alerts = $conn->query($sql_alerts);

$sql_actions = "SELECT * FROM fraud_action_t ORDER BY Timestamp DESC";
$Fraud_Actions = $conn->query($sql_actions);
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="page-header">
    <h2><i class="fas fa-exclamation-triangle"></i> Fraud Management</h2>
</div>

<div class="content-section" style="margin-bottom: 3rem;">
    <h3 style="margin-bottom: 1rem; color: var(--text-muted); font-size: 1.1rem;">Active Alerts</h3>
    <div class="data-table-scroll-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Alert ID</th>
                    <th>Targeted User</th>
                    <th>Pattern Detected</th>
                    <th>Risk Score</th>
                    <th>False Positive?</th>
                    <th>Log ID</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($Fraud_Alerts && $Fraud_Alerts->num_rows > 0) {
                    while($row = $Fraud_Alerts->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['Alert_ID']) . "</td>";
                        echo "<td>";
                        if ($row['User_Name']) {
                            $target_link = "#";
                            if ($row['User_Role'] == 'Investor') $target_link = "Stock_Transactions_and_Trades.php?investor_id=" . $row['Targeted_User_ID'];
                            if ($row['User_Role'] == 'Company') $target_link = "Price_History.php?id=" . $row['Targeted_User_ID'];
                            if ($row['User_Role'] == 'Institution') $target_link = "Stock_Transactions_and_Trades.php?institution_id=" . $row['Targeted_User_ID'];
                            
                            echo "<a href='$target_link' style='color: var(--primary); font-weight: 500; text-decoration: none;' title='View Profile'>" . htmlspecialchars($row['User_Name']) . "</a>";
                        } else {
                            echo "Unknown";
                        }
                        echo "</td>";
                        echo "<td>" . htmlspecialchars($row['Pattern_Detected']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Risk_Score']) . "</td>";
                        echo "<td>";
                        $fpStatus = $row['Is_False_Positive'];
                        $btnLabel = $fpStatus ? "Undo False Positive" : "Mark False Positive";
                        $btnColor = $fpStatus ? "#6c757d" : "#dc3545";
                        $newVal = $fpStatus ? 0 : 1;
                        
                        echo "<div style='display:flex; align-items:center; gap:10px;'>";
                        echo "<span>" . ($fpStatus ? 'Yes' : 'No') . "</span>";
                        echo "<form action='fraud_update_status.php' method='POST' style='margin:0;'>";
                        echo "<input type='hidden' name='alert_id' value='{$row['Alert_ID']}'>";
                        echo "<input type='hidden' name='status' value='$newVal'>";
                        echo "<button type='submit' class='btn-action' style='background:$btnColor; color:white; padding: 2px 8px; font-size: 0.7rem; min-width: auto;'>$btnLabel</button>";
                        echo "</form>";
                        echo "</div>";
                        echo "</td>";
                        echo "<td>";
                        echo "<div style='display:flex; gap:5px; align-items:center;'>";
                        echo "<span>" . htmlspecialchars($row['Log_ID']) . "</span>";
                        echo "<a href='Logs.php?log_id=" . $row['Log_ID'] . "' class='btn-action' style='background:#28a745; color:white; padding: 2px 8px; font-size: 0.75rem; min-width: auto;' title='View Investigation Log'>Investigate</a>";
                        echo "</div>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align:center;'>No Fraud records found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<div class="content-section">
    <h3 style="margin-bottom: 1rem; color: var(--text-muted); font-size: 1.1rem;">Action History</h3>
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
                        echo "<td>";
                        echo "<form action='fraud_edit_note.php' method='POST' style='display:flex; gap:5px;'>";
                        echo "<input type='hidden' name='alert_id' value='{$row['Alert_ID']}'>";
                        echo "<input type='hidden' name='timestamp' value='{$row['Timestamp']}'>";
                        echo "<input type='text' name='notes' value='" . htmlspecialchars($row['Notes']) . "' style='flex:1; padding: 2px 5px; font-size: 0.85rem; border: 1px solid #ddd; border-radius: 4px;'>";
                        echo "<button type='submit' class='btn-action' style='background:transparent; color: var(--primary); padding: 2px; min-width: auto; border:none;' title='Save Note'><i class='fas fa-save'></i> Update</button>";
                        echo "</form>";
                        echo "</td>";
                        echo "<td>" . htmlspecialchars($row['Timestamp']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4' style='text-align:center;'>No fraud action records found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
