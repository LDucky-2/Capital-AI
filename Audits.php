<?php
include 'auth_session.php';
checkLogin();
include 'Database.php';

// Check if update_schema was run? We will assume columns exist or fail gracefully if possible, 
// but in PHP SQL errors are usually fatal or noisy. We'll write the query assuming the column exists 
// as stated in the plan.
// Query: Join Audit_Report_T -> Company_T -> User_T (for company name), And Auditor_T -> User_T (for auditor name)

$sql = "
    SELECT 
        r.Report_ID, 
        r.Auditing_Firm, 
        r.Report_Date, 
        r.Findings_Summary,
        u_auditor.Name as Auditor_Name,
        u_company.Name as Company_Name
    FROM Audit_Report_T r
    LEFT JOIN Auditor_T a ON r.Auditor_User_ID = a.Auditor_User_ID
    LEFT JOIN User_T u_auditor ON a.Auditor_User_ID = u_auditor.User_ID
    LEFT JOIN Company_T c ON r.Company_User_ID = c.Company_User_ID
    LEFT JOIN User_T u_company ON c.Company_User_ID = u_company.User_ID
    ORDER BY r.Report_Date DESC
";

$result = $conn->query($sql);
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="page-header">
    <h2>Audit Reports</h2>
</div>

<div class="data-table-scroll-wrapper">
    <table class="data-table">
        <thead>
            <tr>
                <th>Report ID</th>
                <th>Company</th>
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
                    echo "<tr>";
                    echo "<td>" . $row['Report_ID'] . "</td>";
                    echo "<td>" . ($row['Company_Name'] ? htmlspecialchars($row['Company_Name']) : 'N/A') . "</td>";
                    echo "<td>" . htmlspecialchars($row['Auditor_Name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Auditing_Firm']) . "</td>";
                    echo "<td>" . $row['Report_Date'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['Findings_Summary']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6' style='text-align:center;'>No audit reports found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>