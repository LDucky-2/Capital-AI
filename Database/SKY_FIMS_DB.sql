SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS User_T;
CREATE TABLE User_T (
  User_ID INT(9) NOT NULL AUTO_INCREMENT,
  Name VARCHAR(100) NULL,
  Password VARCHAR(30) NOT NULL,
  Email_Address VARCHAR(80) UNIQUE NULL,
  Status VARCHAR(10) NOT NULL,
  Permission VARCHAR(30) NOT NULL,
  PRIMARY KEY (User_ID),
  CHECK (Status IN ('Frozen', 'Active', 'Inactive')),
  CHECK (Permission IN ('Database Manager', 'Management', 'Administrator', 'Investor', 'Company', 'Institution', 'Auditor', 'Fraud Detector'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS User_Phone_Number_T;
CREATE TABLE User_Phone_Number_T (
  User_Phone_Number_ID INT(9) NOT NULL,
  Phone_Number VARCHAR(11) NOT NULL,
  PRIMARY KEY (User_Phone_Number_ID, Phone_Number),
  FOREIGN KEY (User_Phone_Number_ID) REFERENCES User_T(User_ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS Log_T;
CREATE TABLE Log_T (
  Log_ID INT(9) NOT NULL AUTO_INCREMENT,
  Timestamp DATETIME NOT NULL,
  Activity_Type VARCHAR(50) NOT NULL,
  Activity_Data_Detail TEXT,
  PRIMARY KEY (Log_ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS Auditor_T;
CREATE TABLE Auditor_T (
  Auditor_User_ID INT(9) NOT NULL,
  Auditing_Firm VARCHAR(100),
  PRIMARY KEY (Auditor_User_ID),
  FOREIGN KEY (Auditor_User_ID) REFERENCES User_T(User_ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS Institution_T;
CREATE TABLE Institution_T (
  Institution_User_ID INT(9) NOT NULL,
  Institution_Type VARCHAR(20),
  License_Number VARCHAR(20),
  PRIMARY KEY (Institution_User_ID),
  FOREIGN KEY (Institution_User_ID) REFERENCES User_T(User_ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS Investor_T;
CREATE TABLE Investor_T (
  Investor_User_ID INT(9) NOT NULL,
  PRIMARY KEY (Investor_User_ID),
  FOREIGN KEY (Investor_User_ID) REFERENCES User_T(User_ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS Company_T;
CREATE TABLE Company_T (
  Company_User_ID INT(9) NOT NULL,
  Registration_Number VARCHAR(30) UNIQUE,
  Sector VARCHAR(40),
  PRIMARY KEY (Company_User_ID),
  FOREIGN KEY (Company_User_ID) REFERENCES User_T(User_ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS Stock_T;
CREATE TABLE Stock_T (
  Stock_ID INT(9) NOT NULL AUTO_INCREMENT,
  Company_User_ID INT(9) NOT NULL,
  Total_Shares INT(9),
  Current_Price DECIMAL(10,2),
  PRIMARY KEY (Stock_ID),
  FOREIGN KEY (Company_User_ID) REFERENCES Company_T(Company_User_ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS Price_History_T;
CREATE TABLE Price_History_T (
  Stock_ID INT(9) NOT NULL,
  Date DATE NOT NULL,
  Open_Price DECIMAL(10,2),  
  High DECIMAL(10,2),
  Low DECIMAL(10,2),
  Close_Price DECIMAL(10,2),
  Volume INT(9),
  PRIMARY KEY (Stock_ID, Date),
  FOREIGN KEY (Stock_ID) REFERENCES Stock_T(Stock_ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS Stock_Transactions_T;
CREATE TABLE Stock_Transactions_T (
  Transaction_ID INT(9) NOT NULL AUTO_INCREMENT,
  Stock_ID INT(9) NOT NULL,
  Investor_User_ID INT(9) NOT NULL,
  Log_ID INT(9),
  Share_Amount INT(9),
  Transaction_Type VARCHAR(5),
  PRIMARY KEY (Transaction_ID),
  FOREIGN KEY (Stock_ID) REFERENCES Stock_T(Stock_ID),
  FOREIGN KEY (Investor_User_ID) REFERENCES Investor_T(Investor_User_ID),
  FOREIGN KEY (Log_ID) REFERENCES Log_T(Log_ID),
  CHECK (Transaction_Type IN ('buy', 'sell'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS Trade_T;
CREATE TABLE Trade_T (
  Trade_ID INT(9) NOT NULL AUTO_INCREMENT,
  Buyer_Institution_ID INT(9) NOT NULL,
  Seller_Institution_ID INT(9) NOT NULL,
  Asset_Type VARCHAR(20),
  Log_ID INT(9),
  Trade_Details TEXT,
  PRIMARY KEY (Trade_ID),
  FOREIGN KEY (Buyer_Institution_ID) REFERENCES Institution_T(Institution_User_ID),
  FOREIGN KEY (Seller_Institution_ID) REFERENCES Institution_T(Institution_User_ID),
  FOREIGN KEY (Log_ID) REFERENCES Log_T(Log_ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS Audit_Report_T;
CREATE TABLE Audit_Report_T (
    Report_ID INT(9) NOT NULL AUTO_INCREMENT,
    Auditor_User_ID INT(9) NOT NULL,
    Company_User_ID INT(9), -- Added link to company
    Auditing_Firm VARCHAR(100),
    Report_Date DATE NOT NULL,
    Findings_Summary TEXT,
    PRIMARY KEY (Report_ID),
    FOREIGN KEY (Auditor_User_ID) REFERENCES Auditor_T(Auditor_User_ID),
    FOREIGN KEY (Company_User_ID) REFERENCES Company_T(Company_User_ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS Audit_Logs_T;
CREATE TABLE Audit_Logs_T (
  Report_ID INT(9) NOT NULL,
  Log_ID INT(9) NOT NULL UNIQUE,
  PRIMARY KEY (Report_ID, Log_ID),
  FOREIGN KEY (Report_ID) REFERENCES Audit_Report_T(Report_ID),
  FOREIGN KEY (Log_ID) REFERENCES Log_T(Log_ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS Fraud_Alert_T;
CREATE TABLE Fraud_Alert_T (
  Alert_ID INT(9) NOT NULL AUTO_INCREMENT,
  Pattern_Detected VARCHAR(100),
  Risk_Score DECIMAL(5,2),
  Is_False_Positive BOOLEAN,
  Log_ID INT(9) NOT NULL,
  PRIMARY KEY (Alert_ID),
  FOREIGN KEY (Log_ID) REFERENCES Log_T(Log_ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS Fraud_Action_T;
CREATE TABLE Fraud_Action_T (
  Alert_ID INT(9) NOT NULL,
  Timestamp DATETIME NOT NULL,
  Action_taken TEXT,
  Log_ID INT(9),
  Notes TEXT,
  PRIMARY KEY (Alert_ID, Timestamp),
  FOREIGN KEY (Alert_ID) REFERENCES Fraud_Alert_T(Alert_ID),
  FOREIGN KEY (Log_ID) REFERENCES Log_T(Log_ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS Prediction_T;
CREATE TABLE Prediction_T (
  Prediction_ID INT(9) NOT NULL AUTO_INCREMENT,
  Stock_ID INT(9) NOT NULL,
  Predicted_Price DECIMAL(10,2),
  Confidence_Score DECIMAL(5,2),
  Model_Version VARCHAR(20),
  Timestamp DATETIME,
  Accuracy_Score DECIMAL(5,2),
  PRIMARY KEY (Prediction_ID),
  FOREIGN KEY (Stock_ID) REFERENCES Stock_T(Stock_ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;