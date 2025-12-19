<?php
include 'auth_session.php';
checkLogin();
include 'Database.php';

// Fetch Predictions
$sql = "
    SELECT 
        p.Prediction_ID,
        p.Predicted_Price,
        p.Confidence_Score,
        p.Date_Predicted,
        p.Accuracy_Score,
        s.Stock_ID,
        u.Name as Company_Name
    FROM Prediction_T p
    JOIN Stock_T s ON p.Stock_ID = s.Stock_ID
    JOIN Company_T c ON s.Company_User_ID = c.Company_User_ID
    JOIN User_T u ON c.Company_User_ID = u.User_ID
    ORDER BY p.Date_Predicted DESC
";
// Note: schema showed 'Timestamp' or 'Date' - let's check schema used in Database.php creation step?
// Checking schema from memory/artifacts: Prediction_T has 'Timestamp'. I used 'Date_Predicted' in query but schema check needed.
// Schema says: Timestamp DATETIME.
// Retrying query with correct column name 'Timestamp'.

$sql = "
    SELECT 
        p.Prediction_ID,
        p.Predicted_Price,
        p.Confidence_Score,
        p.Timestamp as Prediction_Date,
        p.Accuracy_Score,
        s.Stock_ID,
        u.Name as Company_Name
    FROM Prediction_T p
    JOIN Stock_T s ON p.Stock_ID = s.Stock_ID
    JOIN Company_T c ON s.Company_User_ID = c.Company_User_ID
    JOIN User_T u ON c.Company_User_ID = u.User_ID
    ORDER BY p.Timestamp DESC
";

$result = $conn->query($sql);
?>

<?php include 'includes/header.php'; ?>
<?php include 'includes/sidebar.php'; ?>

<div class="page-header">
    <h2>AI Stock Predictions</h2>
</div>

<div class="data-table-scroll-wrapper">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Company</th>
                <th>Predicted Price</th>
                <th>Confidence</th>
                <th>Date</th>
                <th>Accuracy</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['Prediction_ID'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['Company_Name']) . "</td>";
                    echo "<td>$" . number_format($row['Predicted_Price'], 2) . "</td>";
                    echo "<td>" . $row['Confidence_Score'] . "%</td>";
                    echo "<td>" . $row['Prediction_Date'] . "</td>";
                    echo "<td>" . ($row['Accuracy_Score'] ? $row['Accuracy_Score'] . "%" : "Pending") . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6' style='text-align:center;'>No predictions available.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>
