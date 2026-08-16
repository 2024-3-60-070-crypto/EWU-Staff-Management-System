CREATE DATABASE IF NOT EXISTS staff_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE staff_management;

CREATE TABLE IF NOT EXISTS department (
  Dept_Name VARCHAR(50) NOT NULL,
  Dept_Type ENUM('Academic', 'Administrative') NOT NULL,
  Location VARCHAR(100) NOT NULL,
  PRIMARY KEY (Dept_Name)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS staff (
  Staff_ID INT AUTO_INCREMENT,
  Staff_Name VARCHAR(100) NOT NULL,
  Gender ENUM('Male', 'Female', 'Other') NOT NULL,
  Date_of_Birth DATE NOT NULL,
  Date_of_Join DATE NOT NULL,
  Phone_Number VARCHAR(20) NOT NULL,
  Email_Address VARCHAR(100) NOT NULL,
  Address VARCHAR(200) NOT NULL,
  Employee_Type ENUM('Faculty', 'Administrative') NOT NULL,
  Is_Admin TINYINT(1) NOT NULL DEFAULT 0,
  Dept_Name VARCHAR(50) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  PRIMARY KEY (Staff_ID),
  UNIQUE KEY uq_phone (Phone_Number),
  UNIQUE KEY uq_email (Email_Address),
  KEY fk_staff_dept (Dept_Name),
  CONSTRAINT fk_staff_dept FOREIGN KEY (Dept_Name) REFERENCES department (Dept_Name) ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS assigned_duties (
  Staff_ID INT NOT NULL,
  Duty_Type ENUM('Teaching', 'Administrative') NOT NULL,
  Course_Name VARCHAR(100) NOT NULL,
  Duty_Description TEXT,
  Assigned_Date DATE NOT NULL,
  Status ENUM('Active', 'Completed', 'Pending') NOT NULL DEFAULT 'Active',
  PRIMARY KEY (Staff_ID, Duty_Type, Course_Name),
  CONSTRAINT fk_duties_staff FOREIGN KEY (Staff_ID) REFERENCES staff (Staff_ID) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS salary (
  Staff_ID INT NOT NULL,
  Basic_Pay DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  DA DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  HRA DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  Medical_Allow DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  Child_Education DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  Total_Salary DECIMAL(12,2) GENERATED ALWAYS AS (Basic_Pay + DA + HRA + Medical_Allow + Child_Education) STORED,
  PRIMARY KEY (Staff_ID),
  CONSTRAINT fk_salary_staff FOREIGN KEY (Staff_ID) REFERENCES staff (Staff_ID) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS leaves (
  Staff_ID INT NOT NULL,
  Leave_Date DATE NOT NULL,
  No_of_Days INT NOT NULL,
  Leave_Type ENUM('Casual', 'Sick', 'Earned', 'Academic') NOT NULL,
  Reason VARCHAR(255),
  PRIMARY KEY (Staff_ID, Leave_Date),
  CONSTRAINT fk_leaves_staff FOREIGN KEY (Staff_ID) REFERENCES staff (Staff_ID) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;
