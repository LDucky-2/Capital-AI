<?php
include 'auth_session.php';
checkLogin();
include 'Database.php';

// Other than admins and fraud detectors no one can see auditor database
if (!hasRole(['Administrator'] || ['Fraud Detector'])) {
    echo "<div class='content'><div class='alert'>Access Denied. You do not have permission to view the Auditor Database.</div></div>";
    include 'includes/footer.php';
    exit();
}

// Fetch Auditors
$sql = "
    SELECT U.User_ID, U.Name, U.Email_Address, U.Status, A.Auditing_Firm 
    FROM User_T U 
    INNER JOIN Auditor_T A 
    ON U.User_ID = A.Auditor_User_ID 
    WHERE U.Permission = 'Auditor' 
    ORDER BY U.Name ASC";

$Auditors = $conn->query($sql);
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="page-header">
    <h2>Auditor Database</h2>
    <p style="color:var(--text-muted);">External auditing partners and firms</p>
</div>

<div class="content-section">
    <div class="data-table-scroll-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Auditing Firm</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($Auditors && $Auditors->num_rows > 0) {
                    while($row = $Auditors->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['User_ID']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Auditing_Firm']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Email_Address']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
                        echo "<td style='display: flex; gap: 8px; align-items: center;'>";
                        echo "<a href='Audits.php?auditor_id=" . $row['User_ID'] . "' class='btn-action' style='background:#28a745; color:white;'>Audits</a>";
                        
                        if (hasRole(['Administrator'] || ['Fraud Detector'])) {
                            $isFrozen = ($row['Status'] === 'Frozen');
                            $newStatus = $isFrozen ? 'Active' : 'Frozen';
                            $btnLabel = $isFrozen ? 'Unfreeze' : 'Freeze';
                            $btnColor = $isFrozen ? '#28a745' : '#dc3545';
                            
                            echo "<form action='user_action.php' method='POST' style='margin:0;'>";
                            echo "<input type='hidden' name='user_id' value='{$row['User_ID']}'>";
                            echo "<input type='hidden' name='status' value='$newStatus'>";
                            echo "<input type='hidden' name='redirect' value='Auditor_Database.php'>";
                            echo "<button type='submit' class='btn-action' style='background:$btnColor; color:white;'>$btnLabel</button>";
                            echo "</form>";
                        }
                        echo "</td>";
                        echo "</tr>";
                    }
                } 
                else {
                    echo "<tr><td colspan='6' style='text-align:center;'>No auditors registered.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
