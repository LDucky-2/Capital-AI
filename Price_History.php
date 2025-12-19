<?php
include 'auth_session.php';
checkLogin();
include 'Database.php';

if (!isset($_GET['id'])) {
    header("Location: Stocks.php");
    exit();
}

$stock_id = intval($_GET['id']);

// Fetch Stock & Company details
$stock_sql = "
    SELECT s.Stock_ID, s.Current_Price, u.Name as Company_Name 
    FROM Stock_T s 
    JOIN Company_T c ON s.Company_User_ID = c.Company_User_ID 
    JOIN User_T u ON c.Company_User_ID = u.User_ID
    WHERE s.Stock_ID = '$stock_id'
";
$stock_result = $conn->query($stock_sql);
$stock = $stock_result->fetch_assoc();

if (!$stock) {
    die("Stock not found.");
}

// Fetch History
$history_sql = "SELECT * FROM Price_History_T WHERE Stock_ID = '$stock_id' ORDER BY Date DESC LIMIT 30";
$history_result = $conn->query($history_sql);
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="page-header">
    <a href="Stocks.php" class="btn-action" style="float:right; margin-top:5px;">&larr; Back to Market</a>
    <h2>Price History: <?php echo htmlspecialchars($stock['Company_Name']); ?></h2>
</div>

<div class="card">
    <h3 style="border-left-color: var(--primary-light);">Current Status</h3>
    <p><strong>Current Price:</strong> $<?php echo number_format($stock['Current_Price'], 2); ?></p>
</div>

<div class="data-table-scroll-wrapper">
    <table class="data-table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Open</th>
                <th>High</th>
                <th>Low</th>
                <th>Close</th>
                <th>Volume</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($history_result && $history_result->num_rows > 0) {
                while($row = $history_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['Date'] . "</td>";
                    echo "<td>$" . number_format($row['Open_Price'], 2) . "</td>";
                    echo "<td>$" . number_format($row['High'], 2) . "</td>";
                    echo "<td>$" . number_format($row['Low'], 2) . "</td>";
                    echo "<td>$" . number_format($row['Close_Price'], 2) . "</td>";
                    echo "<td>" . number_format($row['Volume']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6' style='text-align:center;'>No history available.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
