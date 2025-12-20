<?php
include 'auth_session.php';
checkLogin();
include 'Database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    $user_id = $_SESSION['User_ID'];
    
    // === IPO (CREATE STOCK) ===
    if ($action == 'ipo' && hasRole('Company')) {
        $price = floatval($_POST['price']);
        $total_shares = intval($_POST['total_shares']);
        
        // Find Company_User_ID
        $conn_sql = "SELECT Company_User_ID FROM Company_T WHERE Company_User_ID = '$user_id'";
        $comp = $conn->query($conn_sql)->fetch_assoc();
        
        if ($comp) {
            $sql = "INSERT INTO Stock_T (Company_User_ID, Total_Shares, Current_Price, Status) 
                    VALUES ('$user_id', '$total_shares', '$price', 'Open')";
            if ($conn->query($sql) === TRUE) {
                // Initialize Price History with starting price
                $stock_id = $conn->insert_id;
                $date = date('Y-m-d');
                $hist_sql = "INSERT INTO Price_History_T (Stock_ID, Date, Open_Price, Close_Price, Volume) 
                             VALUES ('$stock_id', '$date', '$price', '$price', 0)";
                $conn->query($hist_sql);
                header("Location: My_Company.php?msg=ipo_success");
            } else {
                echo "Error: " . $conn->error;
            }
        }
        exit();
    }

    // === TOGGLE STATUS (OPEN/CLOSE) ===
    if ($action == 'toggle_status' && hasRole('Company')) {
        $stock_id = intval($_POST['stock_id']);
        $current_status = $_POST['current_status'];
        $new_status = ($current_status == 'Open') ? 'Closed' : 'Open';
        $user_id = $_SESSION['User_ID'];
        
        // Verify ownership
        $check_sql = "SELECT * FROM Stock_T WHERE Stock_ID = '$stock_id' AND Company_User_ID = '$user_id'";
        if ($conn->query($check_sql)->num_rows > 0) {
            $update_sql = "UPDATE Stock_T SET Status = '$new_status' WHERE Stock_ID = '$stock_id'";
            if ($conn->query($update_sql) === TRUE) {
                // Log the action
                $activity_type = "Stock Status Update";
                $activity_data = "Company User $user_id changed Stock $stock_id status from $current_status to $new_status";
                $timestamp = date('Y-m-d H:i:s');
                
                $log_sql = "INSERT INTO Log_T (Timestamp, Activity_Type, Activity_Data_Detail) 
                            VALUES ('$timestamp', '$activity_type', '$activity_data')";
                $conn->query($log_sql);
                
                header("Location: My_Company.php?msg=updated");
            } else {
                echo "Error updating record: " . $conn->error;
            }
        } else {
            echo "Access Denied.";
        }
        exit();
    }

    // === BUY / SELL STOCK (INVESTOR) ===
    if (($action == 'buy' || $action == 'sell') && hasRole('Investor')) {
        $stock_id = intval($_POST['stock_id']);
        $amount = intval($_POST['amount']);
        
        if ($amount <= 0) {
            header("Location: Stocks.php?msg=error");
            exit();
        }

        // Check Stock Status
        $stock_sql = "SELECT Status FROM Stock_T WHERE Stock_ID = '$stock_id'";
        $stock_res = $conn->query($stock_sql)->fetch_assoc();
        
        if ($action == 'buy' && $stock_res['Status'] == 'Closed') {
            echo "<script>alert('Trading for this stock is currently CLOSED by the company.'); window.history.back();</script>";
            exit();
        }

        // Check Availability if Buying
        if ($action == 'buy') {
            $avail_sql = "
                SELECT 
                    s.Total_Shares,
                    (s.Total_Shares - COALESCE(SUM(CASE WHEN t.Transaction_Type = 'buy' THEN t.Share_Amount ELSE -t.Share_Amount END), 0)) as Available_Shares
                FROM Stock_T s
                LEFT JOIN Stock_Transactions_T t ON s.Stock_ID = t.Stock_ID
                WHERE s.Stock_ID = '$stock_id'
                GROUP BY s.Stock_ID
            ";
            $avail_res = $conn->query($avail_sql)->fetch_assoc();
            
            if ($amount > $avail_res['Available_Shares']) {
                echo "<script>alert('Not enough shares available. Available: " . $avail_res['Available_Shares'] . "'); window.history.back();</script>";
                exit();
            }
        }

        $transaction_type = ($action == 'buy') ? 'buy' : 'sell';

        // 1. Create Log Entry
        $activity_type = "Stock " . ucfirst($transaction_type);
        $activity_data = "User $user_id $transaction_type $amount shares of Stock $stock_id";
        $timestamp = date('Y-m-d H:i:s');
        
        $log_sql = "INSERT INTO Log_T (Timestamp, Activity_Type, Activity_Data_Detail) VALUES ('$timestamp', '$activity_type', '$activity_data')";
        $conn->query($log_sql);
        $log_id = $conn->insert_id;

        // 2. Ensure Investor Exists in Investor_T (Auto-fix for old accounts)
        $check_inv = $conn->query("SELECT Investor_User_ID FROM Investor_T WHERE Investor_User_ID = '$user_id'");
        if ($check_inv->num_rows == 0) {
            $conn->query("INSERT INTO Investor_T (Investor_User_ID) VALUES ('$user_id')");
        }

        // 3. Insert into Stock_Transactions_T with Log_ID
        $sql = "INSERT INTO Stock_Transactions_T (Stock_ID, Investor_User_ID, Share_Amount, Transaction_Type, Log_ID) 
                VALUES ('$stock_id', '$user_id', '$amount', '$transaction_type', '$log_id')";

        if ($conn->query($sql) === TRUE) {
            if ($action == 'buy') {
                header("Location: Stocks.php?msg=bought");
            } else {
                header("Location: My_Stocks.php?msg=sold");
            }
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
        exit();
    }
} else {
    header("Location: Stocks.php");
}
?>
