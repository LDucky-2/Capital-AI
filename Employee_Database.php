<?php
include 'auth_session.php';
checkLogin();
include 'Database.php';

// Fetch various employee types
// Optimizing: Could combine into one query with Type column, but given schema has different joins for Auditors (Auditing_Firm), keep separate for clarity or use Union if schemas align (they don't fully).
// Keeping separate queries for simplicity and readability as per original logic, but cleaning up HTML.

$sql_db_mgr = "SELECT User_ID, Name, Email_Address, Status FROM User_T WHERE Permission = 'Database Manager' ORDER BY User_ID";
$Database_Managers = $conn->query($sql_db_mgr);

$sql_auditor = "SELECT u.User_ID, u.Name, u.Email_Address, u.Status, a.Auditing_Firm FROM User_T u JOIN Auditor_T a ON u.User_ID = a.Auditor_User_ID WHERE Permission = 'Auditor' ORDER BY u.User_ID"; 
// Note: Original had Permission='Auditors' (plural?) schema has 'Auditor' (singular) or 'Auditor' in check constraint. 
// Schema check constraint: CHECK (Permission IN ('Database Manager', 'Management', 'Administrator', 'Investor', 'Company', 'Institution', 'Auditor', 'Fraud Detector'))
// Original code had 'Auditors' which might be why it worked or failed. I will use 'Auditor' to match schema. 

$Auditors = $conn->query($sql_auditor);

$sql_fraud = "SELECT User_ID, Name, Email_Address, Status FROM User_T WHERE Permission = 'Fraud Detector' ORDER BY User_ID";
$Fraud_Detectors = $conn->query($sql_fraud);

$sql_admin = "SELECT User_ID, Name, Email_Address, Status FROM User_T WHERE Permission = 'Administrator' ORDER BY User_ID";
$System_Administrators = $conn->query($sql_admin);

$sql_mgmt = "SELECT User_ID, Name, Email_Address, Status FROM User_T WHERE Permission = 'Management' ORDER BY User_ID";
$Management = $conn->query($sql_mgmt);
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="page-header">
    <h2>Employee Database</h2>
</div>

<div class="content-section" style="margin-bottom: 2rem;">
    <h3 class="section-title">Database Managers</h3>
    <div class="data-table-scroll-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>ID</th><th>Name</th><th>Email</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php
                if ($Database_Managers && $Database_Managers->num_rows > 0) {
                    while($row = $Database_Managers->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['User_ID']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Email_Address']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
                        echo "</tr>";
                    }
                } else { echo "<tr><td colspan='4'>No records found.</td></tr>"; }
                ?>
            </tbody>
        </table>
    </div>
</div>

<div class="content-section" style="margin-bottom: 2rem;">
    <h3 class="section-title">Auditors</h3>
    <div class="data-table-scroll-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>ID</th><th>Name</th><th>Email</th><th>Firm</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php
                if ($Auditors && $Auditors->num_rows > 0) {
                    while($row = $Auditors->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['User_ID']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Email_Address']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Auditing_Firm']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
                        echo "</tr>";
                    }
                } else { echo "<tr><td colspan='5'>No records found.</td></tr>"; }
                ?>
            </tbody>
        </table>
    </div>
</div>

<div class="content-section" style="margin-bottom: 2rem;">
    <h3 class="section-title">Fraud Detectors</h3>
    <div class="data-table-scroll-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>ID</th><th>Name</th><th>Email</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php
                if ($Fraud_Detectors && $Fraud_Detectors->num_rows > 0) {
                    while($row = $Fraud_Detectors->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['User_ID']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Email_Address']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
                        echo "</tr>";
                    }
                } else { echo "<tr><td colspan='4'>No records found.</td></tr>"; }
                ?>
            </tbody>
        </table>
    </div>
</div>

<div class="content-section" style="margin-bottom: 2rem;">
    <h3 class="section-title">System Administrators</h3>
    <div class="data-table-scroll-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>ID</th><th>Name</th><th>Email</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php
                if ($System_Administrators && $System_Administrators->num_rows > 0) {
                    while($row = $System_Administrators->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['User_ID']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Email_Address']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
                        echo "</tr>";
                    }
                } else { echo "<tr><td colspan='4'>No records found.</td></tr>"; }
                ?>
            </tbody>
        </table>
    </div>
</div>

<div class="content-section">
    <h3 class="section-title">Management</h3>
    <div class="data-table-scroll-wrapper">
        <table class="data-table">
            <thead>
                <tr><th>ID</th><th>Name</th><th>Email</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php
                if ($Management && $Management->num_rows > 0) {
                    while($row = $Management->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['User_ID']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Email_Address']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
                        echo "</tr>";
                    }
                } else { echo "<tr><td colspan='4'>No records found.</td></tr>"; }
                ?>
            </tbody>
        </table>
    </div>
</div>


<?php include 'includes/footer.php'; ?>