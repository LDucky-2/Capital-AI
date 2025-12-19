<?php
include 'auth_session.php';
checkLogin();
include 'Database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && hasRole('Investor')) {
    $stock_id = intval($_POST['stock_id']);
    $amount = intval($_POST['amount']);
    $action = $_POST['action']; // 'buy' or 'sell'
    $user_id = $_SESSION['User_ID'];
    
    if ($amount <= 0) {
        header("Location: Stocks.php?msg=error");
        exit();
    }

    $transaction_type = ($action == 'buy') ? 'buy' : 'sell';

    // Insert into Stock_Transactions_T
    // Schema: Transaction_ID, Stock_ID, Investor_User_ID, Log_ID, Share_Amount, Transaction_Type
    // Note: Log_ID is nullable. We won't link to Log_T for now unless required.
    
    $sql = "INSERT INTO Stock_Transactions_T (Stock_ID, Investor_User_ID, Share_Amount, Transaction_Type) 
            VALUES ('$stock_id', '$user_id', '$amount', '$transaction_type')";

    if ($conn->query($sql) === TRUE) {
        // Redirect back
        if ($action == 'buy') {
            header("Location: Stocks.php?msg=bought");
        } else {
            header("Location: My_Stocks.php?msg=sold");
        }
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
} else {
    header("Location: Stocks.php");
}
?>
