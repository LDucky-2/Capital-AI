<?php
include 'auth_session.php';
checkLogin();
include 'Database.php';

// Optimized Query
$sql = "
    SELECT 
        i.Investor_User_ID, 
        u.Name, 
        u.Email_Address, 
        u.Status
    FROM Investor_T i
    JOIN User_T u ON i.Investor_User_ID = u.User_ID
    ORDER BY i.Investor_User_ID
";
$Investors = $conn->query($sql);
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="page-header">
    <h2>Investor Database</h2>
</div>

<div class="data-table-scroll-wrapper">
    <table class="data-table">
        <thead>
            <tr>
                <th>Investor ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($Investors && $Investors->num_rows > 0) {
                while($row = $Investors->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['Investor_User_ID']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Email_Address']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4' style='text-align:center;'>No Investor records found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>