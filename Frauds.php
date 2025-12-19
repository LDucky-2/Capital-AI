<?php
include 'auth_session.php';
checkLogin();

// RESTRICT ACCESS: Only allow specific roles
$allowed_roles = ['Fraud Detector', 'Administrator', 'Database Manager'];
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

$sql_alerts = "SELECT * FROM fraud_alert_t ORDER BY Alert_ID DESC";
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
                        echo "<td>" . htmlspecialchars($row['Pattern_Detected']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Risk_Score']) . "</td>";
                        echo "<td>" . ($row['Is_False_Positive'] ? 'Yes' : 'No') . "</td>";
                        echo "<td>" . htmlspecialchars($row['Log_ID']) . "</td>";
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
                        echo "<td>" . htmlspecialchars($row['Notes']) . "</td>";
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