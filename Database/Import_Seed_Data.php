<?php
/**
 * INTELLIGENT SEED DATA IMPORTER
 * This script processes the 5 Skyrim-themed CSVs and preserves all Foreign Key relationships.
 */

$dbPath = __DIR__ . '/../Database.php';
if (!file_exists($dbPath)) {
    die("<p style='color:red;'>FATAL ERROR: Could not find Database.php at $dbPath</p>");
}
require_once($dbPath);

// Disable foreign key checks for the duration of the import to avoid sequence issues
$conn->query("SET FOREIGN_KEY_CHECKS = 0;");

echo "<h1>🚀 Capital AI - Intelligent Seeding Started</h1>";

// 0. AUTO-PATCH DATABASE (Ensuring Targeted_User_ID exists)
echo "<h2>Step 0: Synchronizing Schema...</h2>";
$conn->query("SET FOREIGN_KEY_CHECKS = 0;");
$checkCol = $conn->query("SHOW COLUMNS FROM Fraud_Alert_T LIKE 'Targeted_User_ID'");
if ($checkCol->num_rows == 0) {
    echo "<p>Found missing column 'Targeted_User_ID'. Patching now...</p>";
    $conn->query("ALTER TABLE Fraud_Alert_T ADD COLUMN Targeted_User_ID INT(9) AFTER Alert_ID");
    $conn->query("ALTER TABLE Fraud_Alert_T ADD CONSTRAINT fk_targeted_user FOREIGN KEY (Targeted_User_ID) REFERENCES User_T(User_ID) ON DELETE SET NULL");
    echo "<p style='color:green;'>✅ Database schema synchronized.</p>";
} else {
    echo "<p style='color:green;'>✅ Database schema is up to date.</p>";
}

// 1. DATA MAINTENANCE (Removing TRUNCATE to prevent repeating existing ones)
echo "<p>ℹ️ Running in Idempotent Mode: Existing core profiles (Users, Stocks) will be preserved.</p>";

// === FRESH START FOR OVERSIGHT DATA ===
$oversightTables = ['Fraud_Action_T', 'Fraud_Alert_T', 'Prediction_T', 'Stock_Transactions_T', 'Log_T'];
foreach ($oversightTables as $tbl) {
    if (!$conn->query("TRUNCATE TABLE $tbl")) {
        echo "<p style='color:orange;'>Warning: Could not clear $tbl: " . $conn->error . "</p>";
    }
}
echo "<p>✅ Activity & Oversight tables cleared for fresh re-seeding.</p>";

// Seed Default Admin if not exists
$adminCheck = $conn->query("SELECT User_ID FROM User_T WHERE Email_Address = 'admin@gmail.com'");
if ($adminCheck->num_rows == 0) {
    $conn->query("INSERT INTO User_T (Name, Password, Email_Address, Status, Permission) VALUES ('Dragonborne', '12345', 'admin@gmail.com', 'Active', 'Administrator')");
}

// Map to store Name -> User_ID for linking
$userMap = []; 
$stockMap = [];

// Helper to read CSV
function readCSV($filename) {
    $rows = [];
    if (file_exists($filename) && ($handle = fopen($filename, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $rows[] = $data;
        }
        fclose($handle);
    }
    return $rows;
}

// 2. IMPORT USERS
echo "<h2>Step 1: Mapping/Importing Users...</h2>";
$userData = readCSV('Users_Seed.csv');
foreach ($userData as $row) {
    if (empty($row[0])) continue;
    $name = $conn->real_escape_string($row[0]);
    $pass = $conn->real_escape_string($row[1]);
    $email = $conn->real_escape_string($row[2]);
    $status = $conn->real_escape_string($row[3]);
    $perm = $conn->real_escape_string($row[4]);
    
    // Check if user already exists
    $check = $conn->query("SELECT User_ID FROM User_T WHERE Email_Address = '$email'");
    if ($check && $check->num_rows > 0) {
        $existing = $check->fetch_assoc();
        $userMap[$row[0]] = $existing['User_ID'];
    } else {
        $sql = "INSERT INTO User_T (Name, Password, Email_Address, Status, Permission) VALUES ('$name', '$pass', '$email', '$status', '$perm')";
        if ($conn->query($sql)) {
            $userMap[$row[0]] = $conn->insert_id;
        }
    }
}
echo "<p>✅ Linked " . count($userMap) . " users.</p>";

// 3. IMPORT COMPANIES
echo "<h2>Step 2: Linking Company Profiles...</h2>";
$compData = readCSV('Companies_Seed.csv');
$compCount = 0;
foreach ($compData as $row) {
    $name = $row[0];
    if (isset($userMap[$name])) {
        $uid = $userMap[$name];
        
        $check = $conn->query("SELECT Company_User_ID FROM Company_T WHERE Company_User_ID = $uid");
        if ($check->num_rows == 0) {
            $reg = $conn->real_escape_string($row[1]);
            $sector = $conn->real_escape_string($row[2]);
            if ($conn->query("INSERT INTO Company_T (Company_User_ID, Registration_Number, Sector) VALUES ($uid, '$reg', '$sector')")) {
                $compCount++;
            }
        }
    }
}
echo "<p>✅ Linked $compCount new company profiles.</p>";

// 4. IMPORT INVESTORS
echo "<h2>Step 3: Linking Investor Profiles...</h2>";
$invData = readCSV('Investors_Seed.csv');
$invCount = 0;
foreach ($invData as $row) {
    $name = $row[0];
    if (isset($userMap[$name])) {
        $uid = $userMap[$name];
        
        $check = $conn->query("SELECT Investor_User_ID FROM Investor_T WHERE Investor_User_ID = $uid");
        if ($check->num_rows == 0) {
            if ($conn->query("INSERT INTO Investor_T (Investor_User_ID) VALUES ($uid)")) {
                $invCount++;
            }
        }
    }
}
echo "<p>✅ Linked $invCount new investor profiles.</p>";

// 5. IMPORT STOCKS
echo "<h2>Step 4: Linking Stocks...</h2>";
$stockData = readCSV('Stocks_Seed.csv');
$stockCount = 0;
foreach ($stockData as $row) {
    $compName = $row[0];
    if (isset($userMap[$compName])) {
        $uid = $userMap[$compName];
        
        $check = $conn->query("SELECT Stock_ID FROM Stock_T WHERE Company_User_ID = $uid");
        if ($check && $check->num_rows > 0) {
            $existing = $check->fetch_assoc();
            $stockMap[$compName] = $existing['Stock_ID'];
        } else {
            $shares = (int)$row[1];
            $price = (float)$row[2];
            $status = $conn->real_escape_string($row[3]);
            
            $sql = "INSERT INTO Stock_T (Company_User_ID, Total_Shares, Current_Price, Status) VALUES ($uid, $shares, $price, '$status')";
            if ($conn->query($sql)) {
                $stockMap[$compName] = $conn->insert_id;
                $stockCount++;
            }
        }
    }
}
echo "<p>✅ Market map established for " . count($stockMap) . " stocks ($stockCount new).</p>";

// 6. IMPORT PRICE HISTORY
echo "<h2>Step 5: Importing Price History...</h2>";
$histData = readCSV('Price_History_Seed.csv');
$histCount = 0;
foreach ($histData as $row) {
    $compName = $row[0];
    if (isset($stockMap[$compName])) {
        $sid = $stockMap[$compName];
        $date = $conn->real_escape_string($row[1]);
        
        $check = $conn->query("SELECT 1 FROM Price_History_T WHERE Stock_ID = $sid AND Date = '$date'");
        if ($check->num_rows == 0) {
            $open = (float)$row[2];
            $high = (float)$row[3];
            $low = (float)$row[4];
            $close = (float)$row[5];
            $vol = (int)$row[6];
            
            if ($conn->query("INSERT INTO Price_History_T (Stock_ID, Date, Open_Price, High, Low, Close_Price, Volume) VALUES ($sid, '$date', $open, $high, $low, $close, $vol)")) {
                $histCount++;
            }
        }
    }
}
echo "<p>✅ Imported $histCount new historical records.</p>";

// 7. SYNC CURRENT PRICE WITH LATEST HISTORY
echo "<h2>Step 6: Syncing Current Prices with Latest History...</h2>";
$syncCount = 0;
foreach ($stockMap as $name => $sid) {
    // Find the latest close price for this stock
    $latestQuery = $conn->query("SELECT Close_Price FROM Price_History_T WHERE Stock_ID = $sid ORDER BY Date DESC LIMIT 1");
    if ($latestQuery && $latestQuery->num_rows > 0) {
        $latest = $latestQuery->fetch_assoc();
        $newPrice = $latest['Close_Price'];
        $conn->query("UPDATE Stock_T SET Current_Price = $newPrice WHERE Stock_ID = $sid");
        $syncCount++;
    }
}
echo "<p>✅ Synchronized $syncCount stocks with their latest previous-day close price.</p>";

// 8. GENERATE STOCK TRANSACTIONS & MATCHING LOGS
echo "<h2>Step 7: Generating Realistic Stock Transactions...</h2>";
$transCount = 0;
$logIds = [];

// Get all investor IDs for random selection
$investorIds = [];
$invQuery = $conn->query("SELECT Investor_User_ID FROM Investor_T");
while($ir = $invQuery->fetch_assoc()) $investorIds[] = $ir['Investor_User_ID'];

foreach ($stockMap as $name => $sid) {
    // Generate 2-5 random transactions per stock
    $count = rand(2, 5);
    for ($i = 0; $i < $count; $i++) {
        $uid = $investorIds[array_rand($investorIds)];
        $type = (rand(0, 1) == 1) ? 'buy' : 'sell';
        $amount = rand(5, 50);
        $time = date('Y-m-d H:i:s', strtotime('-' . rand(1, 72) . ' hours'));
        
        // 1. Create System-Consistent Log
        $activity_type = "Stock " . ucfirst($type);
        $activity_data = "User $uid $type $amount shares of Stock $sid";
        
        $logSql = "INSERT INTO Log_T (Timestamp, Activity_Type, Activity_Data_Detail) VALUES ('$time', '$activity_type', '$activity_data')";
        if ($conn->query($logSql)) {
            $lid = $conn->insert_id;
            $logIds[$uid][] = $lid; // Track for fraud tagging
            
            // 2. Create Matching Transaction Record
            $transSql = "INSERT INTO Stock_Transactions_T (Stock_ID, Investor_User_ID, Log_ID, Share_Amount, Transaction_Type) 
                         VALUES ($sid, $uid, $lid, $amount, '$type')";
            if ($conn->query($transSql)) {
                $transCount++;
            }
        }
    }
}
echo "<p>✅ Generated $transCount synchronized stock transactions and logs.</p>";

// 9. GENERATE FRAUD ALERTS & ACTIONS
echo "<h2>Step 8: Generating Transaction-Linked Fraud Alerts...</h2>";
$fraudCount = 0;
$suspiciousPatterns = ['Rapid High-Value Trading', 'Unusual Volume Spike', 'Inside Information Trigger', 'Thieves Guild Association Flag'];
$suspiciousUsers = ['Vekel the Man', 'Tonilia', 'Mallus Maccius', 'Black-Briar Meadery'];

foreach ($suspiciousUsers as $sname) {
    if (isset($userMap[$sname]) && isset($logIds[$userMap[$sname]])) {
        $uid = $userMap[$sname];
        $lid_trade = $logIds[$uid][array_rand($logIds[$uid])]; // The suspicious log
        $pattern = $suspiciousPatterns[array_rand($suspiciousPatterns)];
        $risk = rand(75, 99) / 10.0;
        
        $sql = "INSERT INTO Fraud_Alert_T (Targeted_User_ID, Pattern_Detected, Risk_Score, Log_ID) 
                VALUES ($uid, '$pattern', $risk, $lid_trade)";
        if ($conn->query($sql)) {
            $aid = $conn->insert_id;
            $fraudCount++;
            
            // Link a System-Consistent Fraud Action log
            if (rand(0, 1) == 1) {
                $time = date('Y-m-d H:i:s');
                
                // 1. Create Log_T entry (Mirroring user_action.php)
                $act_type = "Fraud Action Taken";
                $act_data = "Fraud Detector (Dragonborne) changed User ID $uid status to Frozen";
                $conn->query("INSERT INTO Log_T (Timestamp, Activity_Type, Activity_Data_Detail) VALUES ('$time', '$act_type', '$act_data')");
                $log_id_action = $conn->insert_id;
                
                // 2. Create Fraud_Action_T entry
                $msg = "Account Frozen";
                $notes = "Target: " . addslashes($sname) . " - Permanent freeze for market manipulation.";
                $conn->query("INSERT INTO Fraud_Action_T (Alert_ID, Timestamp, Action_taken, Log_ID, Notes) 
                             VALUES ($aid, '$time', '$msg', $log_id_action, '$notes')");
            }
        }
    }
}
echo "<p>✅ Created $fraudCount reactive fraud alerts and system-consistent actions.</p>";

// 10. GENERATE DUMMY PREDICTIONS
echo "<h2>Step 9: Seeding AI Predictions...</h2>";
$predCount = 0;
foreach ($stockMap as $compName => $sid) {
    // Get current price
    $pQuery = $conn->query("SELECT Current_Price FROM Stock_T WHERE Stock_ID = $sid");
    $pRow = $pQuery->fetch_assoc();
    $currPrice = $pRow['Current_Price'];
    
    // Generate 3 predictions per stock
    for ($i = 1; $i <= 3; $i++) {
        $change = (rand(-80, 120) / 1000.0) + 1; // -8% to +12%
        $predPrice = round($currPrice * $change, 2);
        $conf = rand(65, 98) / 100.0;
        $ver = "Nexus-v" . rand(1, 4);
        $time = date('Y-m-d H:i:s');
        $acc = rand(30, 90) / 100.0;
        
        $sql = "INSERT INTO Prediction_T (Stock_ID, Predicted_Price, Confidence_Score, Model_Version, Timestamp, Accuracy_Score) 
                VALUES ($sid, $predPrice, $conf, '$ver', '$time', $acc)";
        if ($conn->query($sql)) {
            $predCount++;
        }
    }
}
echo "<p>✅ Generated $predCount deterministic AI price predictions.</p>";

$conn->query("SET FOREIGN_KEY_CHECKS = 1;");
echo "<h1>🏁 Market Expansion & Oversight Sync Complete!</h1>";
echo "<p><a href='../index.php'>Return to Dashboard</a></p>";
?>
