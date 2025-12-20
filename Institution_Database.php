<?php
include 'auth_session.php';
checkLogin();
include 'Database.php';

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
                <?php if (hasRole('Institution')) echo "<th>Action</th>"; ?>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($Institutions && $Institutions->num_rows > 0) {
                while($row = $Institutions->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['Institution_User_ID']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Email']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['License_Number']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Institution_Type']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Status']) . "</td>";
                    
                    if (hasRole('Institution')) {
                         echo "<td>";
                         if ($row['Status'] == 'Active' && $row['Institution_User_ID'] != $_SESSION['User_ID']) {
                             echo "<a href='My_Institution.php?trade_with=" . $row['Institution_User_ID'] . "' class='btn-action'>Trade</a>";
                         } else {
                             echo "-";
                         }
                         echo "</td>";
                    }
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6' style='text-align:center;'>No Institutions found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>