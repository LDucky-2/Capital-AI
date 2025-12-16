-- --------------------------------------------------------
-- Database: `finance_system_db`
-- Complete Schema for DBMS Project
-- --------------------------------------------------------

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0; -- Temporarily disable FK checks for clean drop/create

--
-- Table structure for table `User_T` (Supertype)
--
DROP TABLE IF EXISTS `User_T`;
CREATE TABLE `User_T` (
  `User_ID` INT(9) NOT NULL AUTO_INCREMENT COMMENT 'PK. Unique identifier for a system user.',
  `Name` VARCHAR(100) NOT NULL COMMENT 'Full legal Name of the user.',
  `Password` VARCHAR(30) NOT NULL COMMENT 'password string.',
  `Email_Address` VARCHAR(80) UNIQUE NOT NULL COMMENT 'Unique email address.',
  `Status` VARCHAR(10) NOT NULL COMMENT 'shows the status of the account. Can be (Frozen, Active)',
  `Permission` VARCHAR(30) NOT NULL COMMENT 'user''s power/role',
  PRIMARY KEY (`User_ID`),
  CHECK (`Status` IN ('Frozen', 'Active')),
  CHECK (`Permission` IN ('Database Manager', 'Management', 'Administrator', 'Investor', 'Company', 'Institution', 'Auditor', 'Fraud Detector'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `User_Phone_Number_T`
--
DROP TABLE IF EXISTS `User_Phone_Number_T`;
CREATE TABLE `User_Phone_Number_T` (
  `User_Phone_Number_ID` INT(9) NOT NULL COMMENT 'PK, FK to User_T(User_ID). Links to the system user',
  `Phone_Number` VARCHAR(11) NOT NULL COMMENT 'PK, User''s contact phone number. Users can have multiple phone numbers[cite: 3].',
  PRIMARY KEY (`User_Phone_Number_ID`, `Phone_Number`),
  FOREIGN KEY (`User_Phone_Number_ID`) REFERENCES `User_T`(`User_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `Log_T`
--
DROP TABLE IF EXISTS `Log_T`;
CREATE TABLE `Log_T` (
  `Log_ID` INT(9) NOT NULL AUTO_INCREMENT COMMENT 'PK. Unique ID for the activity log entry.',
  `Timestamp` DATETIME NOT NULL COMMENT 'Time of activity.',
  `Activity_Type` VARCHAR(50) NOT NULL COMMENT 'Category of user/system activity.',
  `Activity_Data_Detail` TEXT COMMENT 'Detailed event description. Changed from VARCHAR(255) to TEXT for unlimited logging.',
  PRIMARY KEY (`Log_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Sub-tables (Inheritance from User_T)
--

-- 4A. Auditor_T
DROP TABLE IF EXISTS `Auditor_T`;
CREATE TABLE `Auditor_T` (
  `Auditor_User_ID` INT(9) NOT NULL COMMENT 'PK, FK to User_T(User_ID). Links to the system user [cite: 28]',
  `Auditing_Firm` VARCHAR(100) COMMENT 'Name of the auditing firm[cite: 28].',
  PRIMARY KEY (`Auditor_User_ID`),
  FOREIGN KEY (`Auditor_User_ID`) REFERENCES `User_T`(`User_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4B. Institution_T
DROP TABLE IF EXISTS `Institution_T`;
CREATE TABLE `Institution_T` (
  `Institution_User_ID` INT(9) NOT NULL COMMENT 'PK, FK to User_T(User_ID). Links to the system user',
  `Institution_Type` VARCHAR(20) COMMENT 'Category of institution.',
  `License_Number` VARCHAR(20) COMMENT 'Government/business license number[cite: 5].',
  PRIMARY KEY (`Institution_User_ID`),
  FOREIGN KEY (`Institution_User_ID`) REFERENCES `User_T`(`User_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4C. Investor_T
DROP TABLE IF EXISTS `Investor_T`;
CREATE TABLE `Investor_T` (
  `Investor_User_ID` INT(9) NOT NULL COMMENT 'PK, FK to User_T(User_ID). Links to the system user [cite: 16]',
  PRIMARY KEY (`Investor_User_ID`),
  FOREIGN KEY (`Investor_User_ID`) REFERENCES `User_T`(`User_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4D. Company_T
DROP TABLE IF EXISTS `Company_T`;
CREATE TABLE `Company_T` (
  `Company_User_ID` INT(9) NOT NULL COMMENT 'PK, FK to User_T(User_ID). Links to the system user [cite: 8]',
  `Registration_Number` VARCHAR(30) UNIQUE COMMENT 'Government/company registration ID[cite: 8].',
  `Sector` VARCHAR(40) COMMENT 'Industry classification[cite: 8].',
  PRIMARY KEY (`Company_User_ID`),
  FOREIGN KEY (`Company_User_ID`) REFERENCES `User_T`(`User_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `Stock_T`
--
DROP TABLE IF EXISTS `Stock_T`;
CREATE TABLE `Stock_T` (
  `Stock_ID` INT(9) NOT NULL AUTO_INCREMENT COMMENT 'PK. Unique ID for the stock asset[cite: 10].',
  `Company_User_ID` INT(9) NOT NULL COMMENT 'FK to Company_T. Every stock is linked to exactly one company[cite: 12].',
  `Total_Shares` INT(9) COMMENT 'Total number of issued shares[cite: 11].',
  `Current_Price` DECIMAL(10,2) COMMENT 'Latest recorded stock price[cite: 11].',
  PRIMARY KEY (`Stock_ID`),
  FOREIGN KEY (`Company_User_ID`) REFERENCES `Company_T`(`Company_User_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `Price_History_T`
--
DROP TABLE IF EXISTS `Price_History_T`;
CREATE TABLE `Price_History_T` (
  `Stock_ID` INT(9) NOT NULL COMMENT 'PK, FK to Stock_T(Stock_ID).',
  `Date` DATE NOT NULL COMMENT 'PK. Date of the price record[cite: 18].',
  `Open_Price` DECIMAL(10,2) COMMENT 'Day''s starting price [cite: 18].',  
  `High` DECIMAL(10,2) COMMENT 'Day''s highest price[cite: 18].',
  `Low` DECIMAL(10,2) COMMENT 'Day''s lowest price[cite: 18].',
  `Close_Price` DECIMAL(10,2) COMMENT 'Final trading price[cite: 18].',
  `Volume` INT(9) COMMENT 'Shares traded that day[cite: 18].',
  PRIMARY KEY (`Stock_ID`, `Date`),
  FOREIGN KEY (`Stock_ID`) REFERENCES `Stock_T`(`Stock_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `Stock_Transactions_T`
--
DROP TABLE IF EXISTS `Stock_Transactions_T`;
CREATE TABLE `Stock_Transactions_T` (
  `Transaction_ID` INT(9) NOT NULL AUTO_INCREMENT COMMENT 'PK. Unique ID for a stock transaction[cite: 14].',
  `Stock_ID` INT(9) NOT NULL COMMENT 'FK to Stock_T. A stock transaction must involve a stock[cite: 17].',
  `Investor_User_ID` INT(9) NOT NULL COMMENT 'FK to Investor_T. A stock transaction must involve a user[cite: 17].',
  `Log_ID` INT(9) COMMENT 'FK to Log_T. Every stock transaction must generate a log entry[cite: 15].',
  `Share_Amount` INT(9) COMMENT 'Number of shares transacted[cite: 14].',
  `Transaction_Type` VARCHAR(5) COMMENT 'Indicates if the transaction was a buy or a sell[cite: 14].',
  PRIMARY KEY (`Transaction_ID`),
  FOREIGN KEY (`Stock_ID`) REFERENCES `Stock_T`(`Stock_ID`),
  FOREIGN KEY (`Investor_User_ID`) REFERENCES `Investor_T`(`Investor_User_ID`),
  FOREIGN KEY (`Log_ID`) REFERENCES `Log_T`(`Log_ID`),
  CHECK (`Transaction_Type` IN ('buy', 'sell'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `Trade_T` (Institution-to-Institution Trades)
--
DROP TABLE IF EXISTS `Trade_T`;
CREATE TABLE `Trade_T` (
  `Trade_ID` INT(9) NOT NULL AUTO_INCREMENT COMMENT 'PK. Unique trade ID[cite: 7].',
  `Buyer_Institution_ID` INT(9) NOT NULL COMMENT 'FK to Institution_T. One participant in the trade.',
  `Seller_Institution_ID` INT(9) NOT NULL COMMENT 'FK to Institution_T. The other participant in the trade.',
  `Asset_Type` VARCHAR(20) COMMENT 'Asset type such as stock/bond[cite: 7].',
  `Log_ID` INT(9) COMMENT 'FK to Log_T. Each trade also has a log[cite: 7].',
  `Trade_Details` TEXT COMMENT 'Extra trade details. Changed to TEXT for detailed documentation.',
  PRIMARY KEY (`Trade_ID`),
  FOREIGN KEY (`Buyer_Institution_ID`) REFERENCES `Institution_T`(`Institution_User_ID`),
  FOREIGN KEY (`Seller_Institution_ID`) REFERENCES `Institution_T`(`Institution_User_ID`),
  FOREIGN KEY (`Log_ID`) REFERENCES `Log_T`(`Log_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `Audit_Report_T` (Updated per Business Rules)
--
DROP TABLE IF EXISTS `Audit_Report_T`;
CREATE TABLE `Audit_Report_T` (
    `Report_ID` INT(9) NOT NULL AUTO_INCREMENT COMMENT 'PK. Unique ID for the audit report.',
    `Auditor_User_ID` INT(9) NOT NULL COMMENT 'FK to Auditor_T. Each report is created by exactly one auditor[cite: 31].',
    `Auditing_Firm` VARCHAR(100) COMMENT 'Name of the auditing firm.',
    `Report_Date` DATE NOT NULL COMMENT 'Date the audit report was issued.',
    `Findings_Summary` TEXT COMMENT 'Detailed findings summary of the audit.',
    PRIMARY KEY (`Report_ID`),
    FOREIGN KEY (`Auditor_User_ID`) REFERENCES `Auditor_T`(`Auditor_User_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `Audit_Logs_T` (Many-to-Many link between Reports and Logs)
--
DROP TABLE IF EXISTS `Audit_Logs_T`;
CREATE TABLE `Audit_Logs_T` (
  `Report_ID` INT(9) NOT NULL COMMENT 'PK, FK to Audit_Report_T. A single audit report may reference multiple logs[cite: 30].',
  `Log_ID` INT(9) NOT NULL UNIQUE COMMENT 'PK, FK to Log_T. A log can only be in one report[cite: 30].',
  PRIMARY KEY (`Report_ID`, `Log_ID`),
  FOREIGN KEY (`Report_ID`) REFERENCES `Audit_Report_T`(`Report_ID`),
  FOREIGN KEY (`Log_ID`) REFERENCES `Log_T`(`Log_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `Fraud_Alert_T`
--
DROP TABLE IF EXISTS `Fraud_Alert_T`;
CREATE TABLE `Fraud_Alert_T` (
  `Alert_ID` INT(9) NOT NULL AUTO_INCREMENT COMMENT 'PK. Unique ID for the fraud alert[cite: 23, 24].',
  `Pattern_Detected` VARCHAR(100) COMMENT 'Description of the fraud pattern identified[cite: 24].',
  `Risk_Score` DECIMAL(5,2) COMMENT 'Calculated risk score (0-100)[cite: 24].',
  `Is_False_Positive` BOOLEAN COMMENT 'Indicates false positive[cite: 24].',
  `Log_ID` INT(9) NOT NULL COMMENT 'FK to Log_T. Every fraud alert must link back to one log[cite: 24].',
  PRIMARY KEY (`Alert_ID`),
  FOREIGN KEY (`Log_ID`) REFERENCES `Log_T`(`Log_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `Fraud_Action_T`
--
DROP TABLE IF EXISTS `Fraud_Action_T`;
CREATE TABLE `Fraud_Action_T` (
  `Alert_ID` INT(9) NOT NULL COMMENT 'PK, FK to Fraud_Alert_T. Every fraud action belongs to exactly one alert[cite: 27].',
  `Timestamp` DATETIME NOT NULL COMMENT 'PK, Time when the action was taken[cite: 26].',
  `Action_taken` TEXT COMMENT 'Response applied to the fraud alert. Changed to TEXT for detailed explanation.',
  `Log_ID` INT(9) COMMENT 'FK to Log_T.',
  `Notes` TEXT COMMENT 'Additional comments. Changed to TEXT for detailed documentation[cite: 26].',
  PRIMARY KEY (`Alert_ID`, `Timestamp`),
  FOREIGN KEY (`Alert_ID`) REFERENCES `Fraud_Alert_T`(`Alert_ID`),
  FOREIGN KEY (`Log_ID`) REFERENCES `Log_T`(`Log_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Table structure for table `Prediction_T`
--
DROP TABLE IF EXISTS `Prediction_T`;
CREATE TABLE `Prediction_T` (
  `Prediction_ID` INT(9) NOT NULL AUTO_INCREMENT COMMENT 'PK. Unique ID for the Al-generated prediction[cite: 32].',
  `Stock_ID` INT(9) NOT NULL COMMENT 'FK to Stock_T. Each prediction belongs to exactly one stock[cite: 33].',
  `Predicted_Price` DECIMAL(10,2) COMMENT 'Model-predicted future price[cite: 32].',
  `Confidence_Score` DECIMAL(5,2) COMMENT 'Confidence level (0-1)[cite: 32].',
  `Model_Version` VARCHAR(20) COMMENT 'Al model identifier[cite: 32].',
  `Timestamp` DATETIME COMMENT 'Time of prediction[cite: 32].',
  `Accuracy_Score` DECIMAL(5,2) COMMENT 'Accuracy of the model/prediction[cite: 32].',
  PRIMARY KEY (`Prediction_ID`),
  FOREIGN KEY (`Stock_ID`) REFERENCES `Stock_T`(`Stock_ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;