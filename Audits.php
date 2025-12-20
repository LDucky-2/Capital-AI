<?php
include 'auth_session.php';
checkLogin();
include 'Database.php';

// Check if update_schema was run? We will assume columns exist or fail gracefully if possible, 
// but in PHP SQL errors are usually fatal or noisy. We'll write the query assuming the column exists 
// as stated in the plan.
// Query: Join Audit_Report_T -> Company_T -> User_T (for company name), And Auditor_T -> User_T (for auditor name)

$user_role = $_SESSION['Permission'];

// Block Company and Fraud Detector access
if ($user_role === 'Company' || $user_role === 'Fraud Detector') {
    echo "<div class='content'><div class='alert'>Access Denied. You do not have permission to view Audit Reports.</div></div>";
    include 'includes/footer.php';
    exit();
}

$where_clause = "1=1"; // Default for Admin, Auditor, Management

if ($user_role === 'Institution') {
    if (isset($_GET['institution_id'])) {
        $target_id = intval($_GET['institution_id']);
        $where_clause = "r.Institution_User_ID = '$target_id'";
    } else {
        // Require a target ID for institutions
        $where_clause = "1=0"; 
        $msg_require_selection = "Please select an institution from the <a href='Institution_Database.php'>Institution Database</a> to view its reports.";
    }
    if (isset($_GET['institution_id'])) {
        $target_id = intval($_GET['institution_id']);
        $where_clause = "r.Institution_User_ID = '$target_id'";
    } elseif (isset($_GET['company_id'])) {
        $target_id = intval($_GET['company_id']);
        $where_clause = "r.Company_User_ID = '$target_id'";
    } elseif (isset($_GET['auditor_id'])) {
        $target_id = intval($_GET['auditor_id']);
        $where_clause = "r.Auditor_User_ID = '$target_id'";
    }
}

$sql = "
    SELECT 
        r.Report_ID, 
        r.Auditing_Firm, 
        r.Report_Date, 
        r.Findings_Summary,
        u_auditor.Name as Auditor_Name,
        u_company.Name as Company_Name,
        u_inst.Name as Institution_Name
    FROM Audit_Report_T r
    LEFT JOIN Auditor_T a ON r.Auditor_User_ID = a.Auditor_User_ID
    LEFT JOIN User_T u_auditor ON a.Auditor_User_ID = u_auditor.User_ID
    LEFT JOIN Company_T c ON r.Company_User_ID = c.Company_User_ID
    LEFT JOIN User_T u_company ON c.Company_User_ID = u_company.User_ID
    LEFT JOIN Institution_T i ON r.Institution_User_ID = i.Institution_User_ID
    LEFT JOIN User_T u_inst ON i.Institution_User_ID = u_inst.User_ID
    WHERE $where_clause
    ORDER BY r.Report_Date DESC
";

$result = $conn->query($sql);

// Handle new audit submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && hasRole('Auditor')) {
    $auditor_id = $_SESSION['User_ID'];
    
    // Fetch Auditor's Firm automatically
    $f_res = $conn->query("SELECT Auditing_Firm FROM Auditor_T WHERE Auditor_User_ID = '$auditor_id'");
    $f_data = $f_res->fetch_assoc();
    $firm = $f_data['Auditing_Firm'] ?? 'Internal';

    $entity_type = $_POST['entity_type']; // 'company' or 'institution'
    $entity_id = intval($_POST['entity_id']);
    $date = $conn->real_escape_string($_POST['report_date']);
    $findings = $conn->real_escape_string($_POST['findings']);

    $company_id = ($entity_type === 'company') ? $entity_id : "NULL";
    $inst_id = ($entity_type === 'institution') ? $entity_id : "NULL";

    $insert_sql = "INSERT INTO Audit_Report_T (Auditor_User_ID, Company_User_ID, Institution_User_ID, Auditing_Firm, Report_Date, Findings_Summary) 
                  VALUES ('$auditor_id', $company_id, $inst_id, '$firm', '$date', '$findings')";
    
    if ($conn->query($insert_sql)) {
        // Log the submission
        $target_res = $conn->query("SELECT Name FROM User_T WHERE User_ID = '$entity_id'");
        $target_name = ($target_res->fetch_assoc())['Name'] ?? 'Unknown';
        $auditor_name = $_SESSION['Name'];

        $activity_type = "Audit Report Submitted";
        $activity_data = $conn->real_escape_string("Auditor '$auditor_name' submitted an audit report for '$target_name' on behalf of '$firm'");
        $timestamp = date('Y-m-d H:i:s');
        
        $log_sql = "INSERT INTO Log_T (Timestamp, Activity_Type, Activity_Data_Detail) VALUES ('$timestamp', '$activity_type', '$activity_data')";
        $conn->query($log_sql);

        header("Location: Audits.php?success=1");
        exit();
    }
}

// Fetch Entities and Auditor Firm for UI
$companies = [];
$institutions = [];
$my_firm = "";
if (hasRole('Auditor')) {
    $companies = $conn->query("SELECT u.User_ID, u.Name FROM Company_T c JOIN User_T u ON c.Company_User_ID = u.User_ID");
    $institutions = $conn->query("SELECT u.User_ID, u.Name FROM Institution_T i JOIN User_T u ON i.Institution_User_ID = u.User_ID");
    
    $auditor_id = $_SESSION['User_ID'];
    $f_res = $conn->query("SELECT Auditing_Firm FROM Auditor_T WHERE Auditor_User_ID = '$auditor_id'");
    $my_firm = $f_res->fetch_assoc()['Auditing_Firm'] ?? 'Internal';
}
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="page-header">
    <h2>Audit Reports</h2>
</div>

<div class="content-section">
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Audit report submitted successfully!</div>
    <?php endif; ?>

    <?php if (hasRole('Auditor')): ?>
    <div class="card" style="margin-bottom: 30px; border: 1px solid var(--accent-gold);">
        <h3>Submit New Audit Report</h3>
        <p style="color:var(--text-muted); margin-bottom:15px;">Reporting as: <strong><?php echo htmlspecialchars($my_firm); ?></strong></p>
        <form action="Audits.php" method="POST" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="input-group">
                <label>Entity Type</label>
                <select name="entity_type" id="entity_type" required onchange="updateEntityList()">
                    <option value="company">Company</option>
                    <option value="institution">Institution</option>
                </select>
            </div>
            
            <div class="input-group">
                <label>Select Entity</label>
                <select name="entity_id" id="entity_id" required>
                    <!-- Populated by JS -->
                </select>
            </div>

            <div class="input-group">
                <label>Report Date</label>
                <input type="date" name="report_date" required value="<?php echo date('Y-m-d'); ?>">
            </div>

            <div class="input-group" style="grid-column: span 2;">
                <label>Findings Summary</label>
                <textarea name="findings" required style="width:100%; min-height:100px; padding:12px; border:1px solid var(--border-color); font-family:inherit;"></textarea>
            </div>

            <div style="grid-column: span 2;">
                <button type="submit" class="btn-primary" style="width: auto; padding: 12px 30px;">Submit Report</button>
            </div>
        </form>
    </div>

    <script>
    const entityData = {
        company: [
            <?php while($c = $companies->fetch_assoc()): ?>
                { id: <?php echo $c['User_ID']; ?>, name: "<?php echo addslashes($c['Name']); ?>" },
            <?php endwhile; ?>
        ],
        institution: [
            <?php while($i = $institutions->fetch_assoc()): ?>
                { id: <?php echo $i['User_ID']; ?>, name: "<?php echo addslashes($i['Name']); ?>" },
            <?php endwhile; ?>
        ]
    };

    function updateEntityList() {
        const type = document.getElementById('entity_type').value;
        const select = document.getElementById('entity_id');
        select.innerHTML = '';
        
        entityData[type].forEach(item => {
            const opt = document.createElement('option');
            opt.value = item.id;
            opt.textContent = item.name;
            select.appendChild(opt);
        });
    }
    // Initialize
    updateEntityList();
    </script>
    <?php endif; ?>
</div>

<div class="content-section">
    <h3>Registry of Reports</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>Report ID</th>
                <th>Entity</th>
                <th>Auditor</th>
                <th>Firm</th>
                <th>Date</th>
                <th>Findings</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $entity = $row['Company_Name'] ?: ($row['Institution_Name'] ?: 'Internal/N/A');
                    echo "<tr>";
                    echo "<td>" . $row['Report_ID'] . "</td>";
                    echo "<td>" . htmlspecialchars($entity) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Auditor_Name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Auditing_Firm']) . "</td>";
                    echo "<td>" . $row['Report_Date'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['Findings_Summary']) . "</td>";
                    echo "</tr>";
                }
            } else {
                $empty_msg = isset($msg_require_selection) ? $msg_require_selection : 'No audit reports found.';
                echo "<tr><td colspan='6' style='text-align:center;'>$empty_msg</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>