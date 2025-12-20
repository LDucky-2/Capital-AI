<?php
include 'auth_session.php';
checkLogin();
// Access Control: Admins, Fraud Detectors, etc.
if (!hasRole(['Administrator', 'Fraud Detector'])) {
    echo "<div class='content'><div class='alert'>Access Denied. You do not have permission to view the Company Database.</div></div>";
    include 'includes/footer.php';
    exit();
}
include 'Database.php';

// Access Control
if (!hasRole(['Administrator', 'Fraud Detector'])) {
    echo "<div class='content'><div class='alert'>Access Denied. You do not have permission to view the Investor Database.</div></div>";
    include 'includes/footer.php';
    exit();
}

// Optimized Query
$sql = "
    SELECT 
        c.Company_User_ID, 
        u.Name, 
        u.Email_Address, 
        c.Registration_Number, 
        c.Sector,
        u.Status,
        s.Stock_ID
    FROM Company_T c 
    JOIN User_T u ON u.User_ID = c.Company_User_ID
    LEFT JOIN Stock_T s ON c.Company_User_ID = s.Company_User_ID
    ORDER BY c.Company_User_ID
";
$Companies = $conn->query($sql);
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="page-header">
    <h2>Company Database</h2>
</div>

<div class="data-table-scroll-wrapper">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Registration Number</th>
                <th>Sector</th>
                <th>Status</th>
                <?php if (hasRole(['Administrator', 'Management', 'Fraud Detector'])) echo "<th>Action</th>"; ?>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($Companies && $Companies->num_rows > 0) {
                while($row = $Companies->fetch_assoc()) {
                    $isFrozen = ($row['Status'] == 'Frozen');
                    $rowStyle = $isFrozen ? "style='opacity: 0.6; background: #f8f9fa;'" : "";
                    echo "<tr $rowStyle>";
                    echo "<td>" . htmlspecialchars($row['Company_User_ID']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Email_Address']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Registration_Number']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Sector']) . "</td>";
                    echo "<td>";
                    $statusBadge = $isFrozen ? "color: #dc3545; border-color: #dc3545;" : "color: #28a745; border-color: #28a745;";
                    echo "<span class='btn-action' style='background:transparent; padding: 2px 8px; font-size: 0.8rem; cursor: default; $statusBadge'>" . htmlspecialchars($row['Status']) . "</span>";
                    echo "</td>";
                    
                    if (hasRole(['Administrator', 'Management', 'Fraud Detector'])) {
                        echo "<td style='display: flex; gap: 8px; align-items: center;'>";
                        


                        // Admins/Management can see Audits and Price History
                        if (hasRole(['Administrator', 'Management'])) {
                            if ($row['Stock_ID']) {
                                echo "<a href='Price_History.php?id=" . $row['Stock_ID'] . "' class='btn-action' style='background:#28a745; color:white; border:none;' title='Price History'>Price History</a> ";
                            }
                            echo "<a href='Audits.php?company_id=" . $row['Company_User_ID'] . "' class='btn-action' style='background:#28a745; color:white; border:none;' title='Audits'>Audits</a>";
                        }

                        if (hasRole(['Administrator', 'Fraud Detector'])) {
                            $newStatus = $isFrozen ? 'Active' : 'Frozen';
                            $btnLabel = $isFrozen ? 'Unfreeze' : 'Freeze';
                            $btnIcon = $isFrozen ? 'fa-unlock' : 'fa-snowflake';
                            $btnColor = $isFrozen ? '#28a745' : '#dc3545';
                            echo "<form action='user_action.php' method='POST' style='margin:0; display:flex; gap:5px; align-items:center;'>";
                            echo "<input type='hidden' name='user_id' value='{$row['Company_User_ID']}'>";
                            echo "<input type='hidden' name='status' value='$newStatus'>";
                            echo "<input type='hidden' name='redirect' value='Company_Database.php'>";
                            echo "<input type='text' name='reason' placeholder='Reason for $btnLabel...' style='padding: 2px 5px; font-size: 0.75rem; border: 1px solid #ccc; border-radius: 4px; width: 150px;' required>";
                            echo "<button type='submit' class='btn-action' style='background:$btnColor; color:white;'>$btnLabel</button>";
                            echo "</form>";
                        }
                        echo "</td>";
                    }
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='7' style='text-align:center;'>No Company records found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
