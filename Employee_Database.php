<?php
include 'auth_session.php';
checkLogin();
include 'Database.php';

// Access Control
if (!hasRole(['Administrator'])) {
    echo "<div class='content'><div class='alert'>Access Denied. You do not have permission to view the Employee Database.</div></div>";
    include 'includes/footer.php';
    exit();
}

$message = "";
// Handle Pre-registration
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'preregister' && hasRole('Administrator')) {
    $name = $conn->real_escape_string($_POST['name']);
    $password = $_POST['password'];
    $role = $conn->real_escape_string($_POST['permission']);
    $status = "Inactive";

    $sql = "INSERT INTO User_T (Name, Password, Status, Permission) VALUES ('$name', '$password', '$status', '$role')";
    if ($conn->query($sql)) {
        $new_id = $conn->insert_id;
        $message = "<div class='alert alert-success'>Staff member '$name' pre-registered successfully! <strong>Assigned ID: $new_id</strong> (Provide this ID to the staff member for activation).</div>";
    } else {
        $message = "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}

// Fetch various employee types
$sql_fraud = "SELECT User_ID, Name, Email_Address, Status, Permission FROM User_T WHERE Permission = 'Fraud Detector' ORDER BY User_ID";
$Fraud_Detectors = $conn->query($sql_fraud);

$sql_admin = "SELECT User_ID, Name, Email_Address, Status, Permission FROM User_T WHERE Permission = 'Administrator' ORDER BY User_ID";
$System_Administrators = $conn->query($sql_admin);

$sql_mgmt = "SELECT User_ID, Name, Email_Address, Status, Permission FROM User_T WHERE Permission = 'Management' ORDER BY User_ID";
$Management = $conn->query($sql_mgmt);
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="page-header">
    <h2>Employee Database</h2>
    <p style="color:var(--text-muted);">Internal staff and department management</p>
</div>

<?php echo $message; ?>

<?php if (hasRole('Administrator')): ?>
<div class="content-section" style="margin-bottom: 2rem; border: 1px dashed var(--primary-color);">
    <h3 class="section-title"><i class="fas fa-user-plus"></i> Pre-register Internal Staff</h3>
    <form action="" method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
        <input type="hidden" name="action" value="preregister">
        <div class="input-group" style="margin-bottom:0;">
            <label>Full Name</label>
            <input type="text" name="name" required placeholder="Staff Name">
        </div>
        <div class="input-group" style="margin-bottom:0;">
            <label>Temp Password</label>
            <input type="text" name="password" required placeholder="Password">
        </div>
        <div class="input-group" style="margin-bottom:0;">
            <label>Permission</label>
            <select name="permission" required>
                <option value="Management">Management</option>
                <option value="Administrator">Administrator</option>
                <option value="Fraud Detector">Fraud Detector</option>
            </select>
        </div>
        <div class="input-group" style="margin-bottom:0;">
            <label>&nbsp;</label>
            <button type="submit" class="btn-primary" style="height: 42px; width: 100%;">Pre-register</button>
        </div>
    </form>
</div>
<?php endif; ?>



<div class="content-section" style="margin-bottom: 2rem;">
    <h3 class="section-title"><i class="fas fa-shield-alt"></i> Fraud Detectors</h3>
    <div class="data-table-scroll-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <?php if (hasRole('Administrator')) echo "<th>Action</th>"; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($Fraud_Detectors && $Fraud_Detectors->num_rows > 0) {
                    while($row = $Fraud_Detectors->fetch_assoc()) {
                        $isFrozen = ($row['Status'] == 'Frozen');
                        $rowStyle = $isFrozen ? "style='opacity: 0.6; background: #f8f9fa;'" : "";
                        echo "<tr $rowStyle>";
                        echo "<td>" . htmlspecialchars($row['User_ID']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Email_Address']) . "</td>";
                        echo "<td>";
                        $statusBadge = $isFrozen ? "color: #dc3545; border-color: #dc3545;" : "color: #28a745; border-color: #28a745;";
                        echo "<span class='btn-action' style='background:transparent; padding: 2px 8px; font-size: 0.8rem; cursor: default; $statusBadge'>" . htmlspecialchars($row['Status']) . "</span>";
                        echo "</td>";
                        if (hasRole('Administrator') && $row['Permission'] !== 'Administrator') {
                            $newStatus = $isFrozen ? 'Active' : 'Frozen';
                            $btnLabel = $isFrozen ? 'Unfreeze' : 'Freeze';
                            $btnIcon = $isFrozen ? 'fa-unlock' : 'fa-snowflake';
                            $btnColor = $isFrozen ? '#28a745' : '#dc3545';
                            echo "<td style='display: flex; gap: 8px; align-items: center;'>";
                            echo "<form action='user_action.php' method='POST' style='margin:0;'>";
                            echo "<input type='hidden' name='user_id' value='{$row['User_ID']}'>";
                            echo "<input type='hidden' name='status' value='$newStatus'>";
                            echo "<input type='hidden' name='redirect' value='Employee_Database.php'>";
                            echo "<button type='submit' class='btn-action' style='background:$btnColor; color:white;'>$btnLabel</button>";
                            echo "</form>";
                            echo "</td>";
                        }
                        echo "</tr>";
                    }
                } else { echo "<tr><td colspan='5'>No records found.</td></tr>"; }
                ?>
            </tbody>
        </table>
    </div>
</div>

<div class="content-section" style="margin-bottom: 2rem;">
    <h3 class="section-title"><i class="fas fa-user-shield"></i> System Administrators</h3>
    <div class="data-table-scroll-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <?php if (hasRole('Administrator')) echo "<th>Action</th>"; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($System_Administrators && $System_Administrators->num_rows > 0) {
                    while($row = $System_Administrators->fetch_assoc()) {
                        $isFrozen = ($row['Status'] == 'Frozen');
                        $rowStyle = $isFrozen ? "style='opacity: 0.6; background: #f8f9fa;'" : "";
                        echo "<tr $rowStyle>";
                        echo "<td>" . htmlspecialchars($row['User_ID']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Email_Address']) . "</td>";
                        echo "<td>";
                        $statusBadge = $isFrozen ? "color: #dc3545; border-color: #dc3545;" : "color: #28a745; border-color: #28a745;";
                        echo "<span class='btn-action' style='background:transparent; padding: 2px 8px; font-size: 0.8rem; cursor: default; $statusBadge'>" . htmlspecialchars($row['Status']) . "</span>";
                        echo "</td>";
                        if (hasRole('Administrator') && $row['Permission'] !== 'Administrator') {
                            $newStatus = $isFrozen ? 'Active' : 'Frozen';
                            $btnLabel = $isFrozen ? 'Unfreeze' : 'Freeze';
                            $btnIcon = $isFrozen ? 'fa-unlock' : 'fa-snowflake';
                            $btnColor = $isFrozen ? '#28a745' : '#dc3545';
                            echo "<td style='display: flex; gap: 8px; align-items: center;'>";
                            echo "<form action='user_action.php' method='POST' style='margin:0;'>";
                            echo "<input type='hidden' name='user_id' value='{$row['User_ID']}'>";
                            echo "<input type='hidden' name='status' value='$newStatus'>";
                            echo "<input type='hidden' name='redirect' value='Employee_Database.php'>";
                            echo "<button type='submit' class='btn-action' style='background:$btnColor; color:white;'>$btnLabel</button>";
                            echo "</form>";
                            echo "</td>";
                        }
                        echo "</tr>";
                    }
                } else { echo "<tr><td colspan='5'>No records found.</td></tr>"; }
                ?>
            </tbody>
        </table>
    </div>
</div>

<div class="content-section">
    <h3 class="section-title"><i class="fas fa-users-cog"></i> Management</h3>
    <div class="data-table-scroll-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Status</th>
                    <?php if (hasRole('Administrator')) echo "<th>Action</th>"; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($Management && $Management->num_rows > 0) {
                    while($row = $Management->fetch_assoc()) {
                        $isFrozen = ($row['Status'] == 'Frozen');
                        $rowStyle = $isFrozen ? "style='opacity: 0.6; background: #f8f9fa;'" : "";
                        echo "<tr $rowStyle>";
                        echo "<td>" . htmlspecialchars($row['User_ID']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['Email_Address']) . "</td>";
                        echo "<td>";
                        $statusBadge = $isFrozen ? "color: #dc3545; border-color: #dc3545;" : "color: #28a745; border-color: #28a745;";
                        echo "<span class='btn-action' style='background:transparent; padding: 2px 8px; font-size: 0.8rem; cursor: default; $statusBadge'>" . htmlspecialchars($row['Status']) . "</span>";
                        echo "</td>";
                        if (hasRole('Administrator') && $row['Permission'] !== 'Administrator') {
                            $newStatus = $isFrozen ? 'Active' : 'Frozen';
                            $btnLabel = $isFrozen ? 'Unfreeze' : 'Freeze';
                            $btnIcon = $isFrozen ? 'fa-unlock' : 'fa-snowflake';
                            $btnColor = $isFrozen ? '#28a745' : '#dc3545';
                            echo "<td style='display: flex; gap: 8px; align-items: center;'>";
                            echo "<form action='user_action.php' method='POST' style='margin:0;'>";
                            echo "<input type='hidden' name='user_id' value='{$row['User_ID']}'>";
                            echo "<input type='hidden' name='status' value='$newStatus'>";
                            echo "<input type='hidden' name='redirect' value='Employee_Database.php'>";
                            echo "<button type='submit' class='btn-action' style='background:$btnColor; color:white;'>$btnLabel</button>";
                            echo "</form>";
                            echo "</td>";
                        }
                        echo "</tr>";
                    }
                } else { echo "<tr><td colspan='5'>No records found.</td></tr>"; }
                ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>