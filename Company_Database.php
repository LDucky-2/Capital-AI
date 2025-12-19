<?php
include 'auth_session.php';
checkLogin();
// Access Control: Database Managers, Admins, etc.
// hasRole(['Database Manager', 'Administrator', 'Auditor', 'Maintenance']) ? 

include 'Database.php';

// Optimized Query
$sql = "
    SELECT 
        c.Company_User_ID, 
        u.Name, 
        u.Email_Address, 
        c.Registration_Number, 
        c.Sector,
        u.Status
    FROM Company_T c 
    JOIN User_T u ON u.User_ID = c.Company_User_ID
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
            </tr>
        </thead>
        <tbody>
            <?php
            if ($Companies && $Companies->num_rows > 0) {
                while($row = $Companies->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['Company_User_ID']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Email_Address']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Registration_Number']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Sector']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6' style='text-align:center;'>No Company records found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
