<?php
include 'auth_session.php';
checkLogin();
include 'Database.php';

// Fetch Transactions
// Joining with Stock_T -> Company -> User (for company name) and Investor -> User (for investor name)
$sql = "
    SELECT 
        t.Transaction_ID,
        t.Transaction_Type,
        t.Share_Amount,
        s.Stock_ID,
        comp_user.Name as Company_Name,
        inv_user.Name as Investor_Name
    FROM Stock_Transactions_T t
    JOIN Stock_T s ON t.Stock_ID = s.Stock_ID
    JOIN Company_T c ON s.Company_User_ID = c.Company_User_ID
    JOIN User_T comp_user ON c.Company_User_ID = comp_user.User_ID
    JOIN Investor_T i ON t.Investor_User_ID = i.Investor_User_ID
    JOIN User_T inv_user ON i.Investor_User_ID = inv_user.User_ID
    LEFT JOIN Log_T l ON t.Log_ID = l.Log_ID
    WHERE t.Investor_User_ID = '{$_SESSION['User_ID']}'
    ORDER BY t.Transaction_ID DESC
";
$result = $conn->query($sql);
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="page-header">
    <h2>Transaction History</h2>
</div>

<div class="data-table-scroll-wrapper">
    <table class="data-table">
        <thead>
            <tr>
                <th>Time</th>
                <th>Company Name</th>
                <th>Stock ID</th>
                <th>Transaction Type</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $typeClass = ($row['Transaction_Type'] == 'buy') ? 'success' : 'danger';
                    // Fallback if Log_ID is null (old transactions)
                    $time = $row['Timestamp'] ? $row['Timestamp'] : 'N/A';
                    
                    echo "<tr>";
                    echo "<td>" . $time . "</td>";
                    echo "<td>" . htmlspecialchars($row['Company_Name']) . "</td>";
                    echo "<td>" . $row['Stock_ID'] . "</td>";
                    echo "<td><span class='badge badge-$typeClass' style='text-transform:uppercase;'>" . $row['Transaction_Type'] . "</span></td>";
                    echo "<td>" . number_format($row['Share_Amount']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5' style='text-align:center;'>No transactions found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
