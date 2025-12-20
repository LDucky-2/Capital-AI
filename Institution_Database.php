<?php
include 'auth_session.php';
checkLogin();
include 'Database.php';

// Access Control
if (!hasRole(['Administrator', 'Institution', 'Fraud Detector'])) {
    echo "<div class='content'><div class='alert'>Access Denied. You do not have permission to view the Institutions page.</div></div>";
    include 'includes/footer.php';
    exit();
}

// Optimized Query
$sql = "
    SELECT 
        i.Institution_User_ID,
        u.Name,
        u.Email_Address as Email,
        i.License_Number,
        i.Institution_Type,
        u.Status
    FROM Institution_T i
    JOIN User_T u ON i.Institution_User_ID = u.User_ID
    ORDER BY i.Institution_User_ID
";
$Institutions = $conn->query($sql);
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="page-header">
    <h2>Institution Database</h2>
</div>

<div class="data-table-scroll-wrapper">
    <table class="data-table">
        <thead>
            <tr>
                <th>Institution ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>License Number</th>
                <th>Type</th>
                <th>Status</th>
                <?php if (hasRole(['Institution', 'Administrator', 'Management', 'Fraud Detector'])) echo "<th>Action</th>"; ?>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($Institutions && $Institutions->num_rows > 0) {
                while($row = $Institutions->fetch_assoc()) {
                    $isFrozen = ($row['Status'] == 'Frozen');
                    $rowStyle = $isFrozen ? "style='opacity: 0.6; background: #f8f9fa;'" : "";
                    echo "<tr $rowStyle>";
                    echo "<td>" . htmlspecialchars($row['Institution_User_ID']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['License_Number']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Institution_Type']) . "</td>";
                    echo "<td>";
                    $statusBadge = $isFrozen ? "color: #dc3545; border-color: #dc3545;" : "color: #28a745; border-color: #28a745;";
                    echo "<span class='btn-action' style='background:transparent; padding: 2px 8px; font-size: 0.8rem; cursor: default; $statusBadge'>" . htmlspecialchars($row['Status']) . "</span>";
                    echo "</td>";
                    
                    if (hasRole(['Institution', 'Administrator', 'Management', 'Fraud Detector'])) {
                        echo "<td style='display: flex; gap: 8px; align-items: center;'>";
                        // ONLY peer institutions can initiate a swap/trade, and only if NOT frozen
                        if (hasRole('Institution') && $row['Status'] == 'Active' && $row['Institution_User_ID'] != $_SESSION['User_ID']) {
                            echo "<a href='My_Institution.php?trade_with=" . $row['Institution_User_ID'] . "' class='btn-action' style='background:#28a745; color:white; border:none;' title='Trade'>Trade</a> ";
                        }
                        
                        // Admins/Management/Fraud can see history
                        if (hasRole(['Administrator', 'Management', 'Fraud Detector'])) {
                            echo "<a href='Stock_Transactions_and_Trades.php?institution_id=" . $row['Institution_User_ID'] . "' class='btn-action' style='background:#28a745; color:white; border:none;' title='Trades'>Trades</a> ";
                        }
                        
                        if (hasRole('Administrator')) {
                             echo "<a href='Audits.php?institution_id=" . $row['Institution_User_ID'] . "' class='btn-action' style='background:#28a745; color:white; border:none;' title='Audits'>Audits</a>";
                        }

                        if (hasRole(['Administrator', 'Fraud Detector'])) {
                            $newStatus = $isFrozen ? 'Active' : 'Frozen';
                            $btnLabel = $isFrozen ? 'Unfreeze' : 'Freeze';
                            $btnIcon = $isFrozen ? 'fa-unlock' : 'fa-snowflake';
                            $btnColor = $isFrozen ? '#28a745' : '#dc3545';
                            echo "<form action='user_action.php' method='POST' style='margin:0; display:flex; gap:5px; align-items:center;'>";
                            echo "<input type='hidden' name='user_id' value='{$row['Institution_User_ID']}'>";
                            echo "<input type='hidden' name='status' value='$newStatus'>";
                            echo "<input type='hidden' name='redirect' value='Institution_Database.php'>";
                            echo "<input type='text' name='reason' placeholder='Reason for $btnLabel...' style='padding: 2px 5px; font-size: 0.75rem; border: 1px solid #ccc; border-radius: 4px; width: 150px;' required>";
                            echo "<button type='submit' class='btn-action' style='background:$btnColor; color:white;'>$btnLabel</button>";
                            echo "</form>";
                        }
                        echo "</td>";
                    }
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7' style='text-align:center;'>No Institutions found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
