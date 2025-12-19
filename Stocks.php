<?php
include 'auth_session.php';
checkLogin();
include 'Database.php';

// Optimized Query
$sql = "
    SELECT 
        s.Stock_ID, 
        u.Name as Company_Name, 
        s.Total_Shares, 
        s.Current_Price,
        (s.Total_Shares - COALESCE(SUM(CASE WHEN t.Transaction_Type = 'buy' THEN t.Share_Amount ELSE -t.Share_Amount END), 0)) as Available_Shares
    FROM Stock_T s 
    JOIN Company_T c ON s.Company_User_ID = c.Company_User_ID 
    JOIN User_T u ON c.Company_User_ID = u.User_ID
    LEFT JOIN Stock_Transactions_T t ON s.Stock_ID = t.Stock_ID
    GROUP BY s.Stock_ID
    ORDER BY u.Name ASC
";
$Stocks = $conn->query($sql);

$is_investor = hasRole('Investor');
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="page-header">
    <h2>Market - All Stocks</h2>
</div>

<?php
if (isset($_GET['msg'])) {
    if ($_GET['msg'] == 'bought') echo "<div class='alert'>Stock purchase successful!</div>";
    if ($_GET['msg'] == 'error') echo "<div class='alert' style='border-color:red; color:red;'>Transaction failed.</div>";
}
?>

<div class="data-table-scroll-wrapper">
    <table class="data-table">
        <thead>
            <tr>
                <th>Stock ID</th>
                <th>Company Name</th>
                <th>Available Shares</th>
                <th>Current Price</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($Stocks && $Stocks->num_rows > 0) {
                while($row = $Stocks->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['Stock_ID']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Company_Name']) . "</td>";
                    echo "<td>" . number_format($row['Available_Shares']) . "</td>";
                    echo "<td>$" . htmlspecialchars($row['Current_Price']) . "</td>";
                    echo "<td>";
                    echo "<a href='Stock_Details.php?id=" . $row['Stock_ID'] . "' class='btn-action' style='background:transparent; color:var(--text-main); border:1px solid var(--text-main);'>View Info</a> ";
                    if ($is_investor) {
                        // Simple form for buying
                        echo "<form action='stock_action.php' method='POST' style='display:inline-flex; gap:5px;'>";
                        echo "<input type='hidden' name='stock_id' value='" . $row['Stock_ID'] . "'>";
                        echo "<input type='hidden' name='action' value='buy'>";
                        echo "<input type='number' name='amount' placeholder='Qty' min='1' style='width:60px; padding:5px; margin:0; background:#111; border:1px solid #333; color:#fff;' required>";
                        echo "<button type='submit' class='btn-action'>BUY</button>";
                        echo "</form>";
                    }
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5' style='text-align:center;'>No stocks found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
