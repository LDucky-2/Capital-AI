<?php
include 'auth_session.php';
checkLogin();
include 'Database.php';

$where = "1=1";
if (isset($_GET['log_id'])) {
    $log_id = intval($_GET['log_id']);
    $where = "l.Log_ID = '$log_id'";
}

$sql = "
    SELECT 
        l.Log_ID, 
        l.Activity_Type, 
        l.Timestamp, 
        l.Activity_Data_Detail
    FROM Log_T l
    WHERE $where
    ORDER BY l.Timestamp DESC
";
$Logs = $conn->query($sql);
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="page-header">
    <h2>All Logs</h2>
</div>

<div class="data-table-scroll-wrapper">
    <table class="data-table">
        <thead>
            <tr>
                <th>Log ID</th>
                <th>Activity Type</th>
                <th>Timestamp</th>
                <th>Activity Detail</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($Logs && $Logs->num_rows > 0) {
                while($row = $Logs->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['Log_ID']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Activity_Type']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Timestamp']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Activity_Data_Detail']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4' style='text-align:center;'>No Logs found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>