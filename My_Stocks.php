<?php
include 'auth_session.php';
checkLogin();
include 'Database.php';

// Ensure user is an investor or management
if (!hasRole(['Investor', 'Management'])) {
    echo "<div class='content'><div class='alert'>Access Denied. You do not have access to this portfolio view.</div></div>";
    include 'includes/footer.php';
    exit();
}

$user_id = $_SESSION['User_ID'];
$role = $_SESSION['Permission'];

// Logic: If Management, show collective portfolio of all managers. If Investor, show personal.
$where_clause = ($role === 'Management') 
    ? "t.Investor_User_ID IN (SELECT User_ID FROM User_T WHERE Permission = 'Management')" 
    : "t.Investor_User_ID = '$user_id'";

// Calculate holdings from transactions
// Logic: Sum of 'buy' - Sum of 'sell' for each stock
$sql = "
    SELECT 
        s.Stock_ID,
        u.Name as Company_Name,
        s.Current_Price,
        SUM(CASE WHEN t.Transaction_Type = 'buy' THEN t.Share_Amount ELSE 0 END) as Total_Bought,
        SUM(CASE WHEN t.Transaction_Type = 'sell' THEN t.Share_Amount ELSE 0 END) as Total_Sold
    FROM Stock_Transactions_T t
    JOIN Stock_T s ON t.Stock_ID = s.Stock_ID
    JOIN Company_T c ON s.Company_User_ID = c.Company_User_ID
    JOIN User_T u ON c.Company_User_ID = u.User_ID
    WHERE $where_clause
    GROUP BY s.Stock_ID, u.Name, s.Current_Price
    HAVING (Total_Bought - Total_Sold) > 0
";

$result = $conn->query($sql);
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="page-header">
    <h2><?php echo ($role === 'Management' ? 'Company Investments' : 'My Portfolio'); ?></h2>
</div>

<div class="content-section">
    <p>Here are the stocks you currently own.</p>
</div>

<div class="data-table-scroll-wrapper">
    <table class="data-table">
        <thead>
            <tr>
                <th>Stock ID</th>
                <th>Company</th>
                <th>Shares Owned</th>
                <th>Current Price</th>
                <th>Total Value</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $shares_owned = $row['Total_Bought'] - $row['Total_Sold'];
                    $total_value = $shares_owned * $row['Current_Price'];
                    
                    echo "<tr>";
                    echo "<td>" . $row['Stock_ID'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['Company_Name']) . "</td>";
                    echo "<td>" . number_format($shares_owned) . "</td>";
                    echo "<td>$" . number_format($row['Current_Price'], 2) . "</td>";
                    echo "<td>$" . number_format($total_value, 2) . "</td>";
                    echo "<td style='display: flex; gap: 8px; align-items: center;'>";
                    echo "<a href='Stock_Details.php?id=" . $row['Stock_ID'] . "' class='btn-action' style='background:#28a745; color:white; border:none;'>View</a>";
                    // Sell Button Form
                    echo "<form action='stock_action.php' method='POST' style='display:contents;'>";
                    echo "<input type='hidden' name='stock_id' value='" . $row['Stock_ID'] . "'>";
                    echo "<input type='hidden' name='action' value='sell'>";
                    echo "<input type='number' name='amount' min='1' max='" . $shares_owned . "' placeholder='Qty' style='width:60px; padding:5px;' required>";
                    echo "<button type='submit' class='btn-action' style='background:#28a745; color:white; border:none;'>Sell</button>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6' style='text-align:center;'>You do not own any stocks yet. <a href='Stocks.php'>Buy some here</a>.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
