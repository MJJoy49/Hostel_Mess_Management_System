-- =============================================
-- Database Name: messContro
-- Created for: Ultimate Mess Management System
-- Total Tables: 13
-- All Demo Data Included (fixed & consistent)
-- =============================================

DROP DATABASE IF EXISTS messContro;
CREATE DATABASE IF NOT EXISTS messContro
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE messContro;

-- =============================================
-- 1. Users Table - Members + Admins
--   - gender added
--   - photo as LONGBLOB (binary image)
-- =============================================
CREATE TABLE Users (
    user_id         VARCHAR(50) PRIMARY KEY,     -- Unique User ID (e.g., USER001)
    full_name       VARCHAR(100) NOT NULL,       -- Full name
    gender          VARCHAR(10),                 -- Male/Female/Other
    contact_number  VARCHAR(20) NOT NULL,        -- Phone number
    email_id        VARCHAR(100) UNIQUE NOT NULL,-- Personal email
    blood_group     VARCHAR(10),                 -- Blood group
    role            ENUM('admin','member') NOT NULL DEFAULT 'member', -- Role
    photo           LONGBLOB,                    -- Profile photo BINARY
    address         VARCHAR(255),                -- Permanent address
    religion        VARCHAR(50),
    profession      VARCHAR(100),                -- Job / Student
    password        VARCHAR(255) NOT NULL,       -- Hashed password
    mess_id         VARCHAR(50),                 -- Which mess he belongs to
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    status          ENUM('active','inactive','on_leave') NOT NULL DEFAULT 'active'
    -- later: FK to Mess
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Demo Users (admins + members)
INSERT INTO Users (user_id, full_name, gender, contact_number, email_id, blood_group, role, photo, address, religion, profession, password, mess_id, created_at, status) VALUES
('USER001','Ahmed Raju','Male','01711111111','raju@gmail.com','O+','admin',NULL,'Chittagong','Islam','Business','$2y$10$hashedpass123','MESS001',NOW(),'active'),
('USER002','Sohag Hossain','Male','01722222222','sohag@bhai.com','A+','admin',NULL,'Khulna','Islam','Job','$2y$10$hashedpass123','MESS002',NOW(),'active'),
('USER003','Md Faruk','Male','01733333333','faruk@palace.com','B+','admin',NULL,'Dhaka','Islam','Business','$2y$10$hashedpass123','MESS003',NOW(),'active'),
('USER004','Masud Rana','Male','01744444444','masud@hope.com','O+','admin',NULL,'Uttara','Islam','Job','$2y$10$hashedpass123','MESS004',NOW(),'active'),
('USER005','Rakib Hasan','Male','01755555555','rakib@bondhu.com','A+','admin',NULL,'Mohammadpur','Islam','Business','$2y$10$hashedpass123','MESS005',NOW(),'active'),
('USER101','Rakibul Islam','Male','01911111111','rakib@gmail.com','AB+','member',NULL,'Sylhet','Islam','BUET Student','$2y$10$hashedpass123','MESS001',NOW(),'active'),
('USER102','Tanvir Ahmed','Male','01822222222','tanvir@gmail.com','O-','member',NULL,'Cumilla','Islam','Brac Bank','$2y$10$hashedpass123','MESS001',NOW(),'active'),
('USER103','Arif Hossain','Male','01833333333','arif@gmail.com','B+','member',NULL,'Rajshahi','Islam','Govt Officer','$2y$10$hashedpass123','MESS002',NOW(),'active');

-- =============================================
-- 2. Mess Table - All Mess Information
--   - admin_id FK -> Users(user_id), nullable (ON DELETE SET NULL)
-- =============================================
CREATE TABLE Mess (
    mess_id         VARCHAR(50) PRIMARY KEY,     -- Unique Mess ID (e.g., MESS001)
    mess_name       VARCHAR(100) NOT NULL,       -- Name of the mess
    address         VARCHAR(255) NOT NULL,       -- Full address
    capacity        INT NOT NULL,                -- Total seats in mess
    admin_name      VARCHAR(100) NOT NULL,       -- Admin full name
    admin_email     VARCHAR(100) NOT NULL,       -- Admin personal email
    admin_id        VARCHAR(50),                 -- User ID of admin (from Users table)
    email_id        VARCHAR(100) UNIQUE,         -- Official mess email (optional)
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    mess_description TEXT,                       -- Details about mess, facilities etc.
    FOREIGN KEY (admin_id) REFERENCES Users(user_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO Mess (mess_id,mess_name,address,capacity,admin_name,admin_email,admin_id,email_id,created_at,mess_description) VALUES
('MESS001','Royal Palace Mess','House 12/A, Mirpur-10, Dhaka',24,'Ahmed Raju','raju@gmail.com','USER001','royalpalace@gmail.com',NOW(),'6-floor building with lift & rooftop garden'),
('MESS002','Bhai Bhai Mess','55 Road-8 Block-C, Banani',16,'Sohag Hossain','sohag@bhai.com','USER002',NULL,NOW(),'Family environment, home cooked food'),
('MESS003','Student Palace','Eskaton Garden, Ramna',30,'Md Faruk','faruk@palace.com','USER003','studentpalace@gmail.com',NOW(),'Near coaching centers'),
('MESS004','Hope House','Sector-13, Uttara',20,'Masud Rana','masud@hope.com','USER004',NULL,NOW(),'Brand new mess, everything new'),
('MESS005','Bondhu Mohal','Mohammadpur Bus Stand',18,'Rakib Hasan','rakib@bondhu.com','USER005','bondhumohal@gmail.com',NOW(),'All members are like friends');

-- এখন Users -> Mess FK add করা হচ্ছে
ALTER TABLE Users
  ADD CONSTRAINT fk_users_mess
  FOREIGN KEY (mess_id) REFERENCES Mess(mess_id) ON DELETE SET NULL;

-- =============================================
-- 3. Rooms Table
-- =============================================
CREATE TABLE rooms (
    room_id           INT PRIMARY KEY AUTO_INCREMENT,
    mess_id           VARCHAR(50) NOT NULL,
    room_number       VARCHAR(20) NOT NULL,
    capacity          INT NOT NULL DEFAULT 4,
    current_occupancy INT NOT NULL DEFAULT 0,
    rent_per_seat     DECIMAL(8,2) DEFAULT 3500.00,
    facilities        TEXT,
    is_active         TINYINT(1) DEFAULT 1,
    UNIQUE KEY uniq_room (mess_id, room_number),
    FOREIGN KEY (mess_id) REFERENCES Mess(mess_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO rooms (mess_id, room_number, capacity, current_occupancy, rent_per_seat, facilities) VALUES
('MESS001','101',4,3,4000.00,'AC, Balcony, Wardrobe, Study Table'),
('MESS001','201',6,5,3200.00,'Fan, Attached Bath, WiFi'),
('MESS001','302',4,2,3800.00,'AC, Rooftop Access'),
('MESS002','A-1',4,4,4500.00,'Full AC, Fridge, High-speed WiFi'),
('MESS003','501',8,7,2800.00,'Large room, Common bath, Fan');

-- =============================================
-- 4. Meals Table
-- =============================================
CREATE TABLE Meals (
    meal_id     INT PRIMARY KEY AUTO_INCREMENT,
    mess_id     VARCHAR(50) NOT NULL,
    meal_date   DATE NOT NULL,
    meal_type   VARCHAR(30) NOT NULL,
    menu        TEXT,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_meal_per_day_type (mess_id, meal_date, meal_type),
    FOREIGN KEY (mess_id) REFERENCES Mess(mess_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO Meals (mess_id, meal_date, meal_type, menu) VALUES
('MESS001','2025-07-10','dinner','Beef Rezala, Polao, Borhani, Firni'),
('MESS001','2025-07-10','lunch','Chicken Roast, Khichuri, Egg, Salad'),
('MESS001','2025-07-11','breakfast','Porota, Dal, Egg Bhaji, Tea'),
('MESS002','2025-07-10','dinner','Hilsha Fish, Rice, Dal, Potato Smash'),
('MESS003','2025-07-10','sehri','Dates, Roti, Chicken Curry, Yogurt');

-- =============================================
-- 5. meal_attendances Table
-- =============================================
CREATE TABLE meal_attendances (
    attendance_id   INT PRIMARY KEY AUTO_INCREMENT,
    meal_id         INT NOT NULL,
    user_id         VARCHAR(50) NOT NULL,
    attended        BOOLEAN NOT NULL DEFAULT FALSE,
    attended_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_attendance_per_user_meal (meal_id, user_id),
    FOREIGN KEY (meal_id) REFERENCES Meals(meal_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO meal_attendances (meal_id, user_id, attended) VALUES
(1,'USER101',TRUE),
(1,'USER102',TRUE),
(2,'USER101',TRUE),
(3,'USER101',TRUE),
(4,'USER103',TRUE);

-- =============================================
-- 6. monthly_bills Table
-- (নাম একটু অদ্ভুত: bill_month YEAR, bill_year INT, কিন্তু তোমার demo data অনুযায়ী একই রেখেছি)
-- =============================================
CREATE TABLE monthly_bills (
    bill_id       INT PRIMARY KEY AUTO_INCREMENT,
    mess_id       VARCHAR(50) NOT NULL,
    user_id       VARCHAR(50) NOT NULL,
    bill_month    YEAR NOT NULL,
    bill_year     INT NOT NULL,
    total_meals   DECIMAL(5,2) DEFAULT 0.00,
    meal_rate     DECIMAL(6,2) NOT NULL DEFAULT 95.00,
    total_amount  DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    paid_amount   DECIMAL(8,2) DEFAULT 0.00,
    due_amount    DECIMAL(8,2) DEFAULT 0.00,
    status        ENUM('pending','paid','partial') DEFAULT 'pending',
    generated_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_month (mess_id, user_id, bill_month, bill_year),
    FOREIGN KEY (mess_id) REFERENCES Mess(mess_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO monthly_bills (mess_id,user_id,bill_month,bill_year,total_meals,meal_rate,total_amount,paid_amount,due_amount,status) VALUES
('MESS001','USER101',2025,7,58.50,95.00,5557.50,5000.00,557.50,'partial'),
('MESS001','USER102',2025,7,62.00,95.00,5890.00,5890.00,0.00,'paid'),
('MESS002','USER103',2025,7,60.00,90.00,5400.00,5400.00,0.00,'paid'),
('MESS001','USER101',2025,6,55.00,92.00,5060.00,5060.00,0.00,'paid'),
('MESS003','USER003',2025,7,20.00,100.00,2000.00,1000.00,1000.00,'partial');

-- =============================================
-- 7. payments Table
-- =============================================
CREATE TABLE payments (
    payment_id      INT PRIMARY KEY AUTO_INCREMENT,
    mess_id         VARCHAR(50) NOT NULL,
    user_id         VARCHAR(50) NOT NULL,
    amount          DECIMAL(8,2) NOT NULL,
    payment_for     ENUM('meal_bill','seat_rent','both') DEFAULT 'meal_bill',
    payment_month   YEAR NOT NULL,
    payment_year    INT NOT NULL,
    payment_method  ENUM('cash','bkash','nagad','bank') NOT NULL,
    transaction_id  VARCHAR(100),
    paid_at         DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mess_id) REFERENCES Mess(mess_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO payments (mess_id,user_id,amount,payment_for,payment_month,payment_year,payment_method,transaction_id) VALUES
('MESS001','USER101',5000.00,'meal_bill',2025,7,'bkash','BK123456789'),
('MESS001','USER102',5890.00,'both',2025,7,'nagad','NG987654321'),
('MESS002','USER103',4500.00,'seat_rent',2025,7,'cash',NULL),
('MESS001','USER101',4000.00,'seat_rent',2025,7,'bank','IBBL111222'),
('MESS003','USER003',1000.00,'meal_bill',2025,7,'bkash','BK555666');

-- =============================================
-- 8. meal_rates Table
-- =============================================
CREATE TABLE meal_rates (
    rate_id          INT PRIMARY KEY AUTO_INCREMENT,
    mess_id          VARCHAR(50) NOT NULL,
    meal_rate        DECIMAL(6,2) NOT NULL,
    applicable_from  DATE NOT NULL,
    created_at       DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_rate_period (mess_id, applicable_from),
    FOREIGN KEY (mess_id) REFERENCES Mess(mess_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO meal_rates (mess_id, meal_rate, applicable_from) VALUES
('MESS001',95.00,'2025-07-01'),
('MESS001',92.00,'2025-06-01'),
('MESS002',90.00,'2025-07-01'),
('MESS003',100.00,'2025-07-01'),
('MESS001',98.00,'2025-08-01');

-- =============================================
-- 9. expenses Table
-- =============================================
CREATE TABLE expenses (
    expense_id   INT PRIMARY KEY AUTO_INCREMENT,
    mess_id      VARCHAR(50) NOT NULL,
    expense_date DATE NOT NULL,
    category     VARCHAR(50) NOT NULL,
    description  TEXT,
    amount       DECIMAL(8,2) NOT NULL,
    added_by     VARCHAR(50),
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mess_id) REFERENCES Mess(mess_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO expenses (mess_id, expense_date, category, description, amount, added_by) VALUES
('MESS001','2025-07-05','Bazar','Chicken, Rice, Oil, Spices',12500.00,'USER001'),
('MESS001','2025-07-01','Gas','Monthly Gas Bill',3200.00,'USER001'),
('MESS001','2025-07-10','Electricity','Monthly Bill',6800.00,'USER001'),
('MESS002','2025-07-08','Maid Salary','July Salary',8000.00,'USER002'),
('MESS003','2025-07-15','Manager Salary','July Salary',15000.00,'USER003');

-- =============================================
-- 10. daily_bazar Table
-- =============================================
CREATE TABLE daily_bazar (
    bazar_id     INT PRIMARY KEY AUTO_INCREMENT,
    mess_id      VARCHAR(50) NOT NULL,
    bazar_date   DATE NOT NULL,
    items        TEXT NOT NULL,
    total_amount DECIMAL(8,2) NOT NULL,
    bazaar_by    VARCHAR(100),
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mess_id) REFERENCES Mess(mess_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO daily_bazar (mess_id, bazar_date, items, total_amount, bazaar_by) VALUES
('MESS001','2025-07-10','Chicken 10kg, Rice 30kg, Oil 5L, Dal 5kg',14200.00,'Rakib'),
('MESS001','2025-07-09','Mutton 5kg, Yogurt 5kg, Onion 10kg',18500.00,'Tanvir'),
('MESS002','2025-07-10','Fish, Vegetables, Spices',9800.00,'Arif'),
('MESS003','2025-07-11','Egg 200pcs, Milk 10L, Bread',4500.00,'Faruk Sir'),
('MESS001','2025-07-11','Beef 8kg, Potato 20kg',16800.00,'Raju Bhai');

-- =============================================
-- 11. announcements Table
-- =============================================
CREATE TABLE announcements (
    announce_id  INT PRIMARY KEY AUTO_INCREMENT,
    mess_id      VARCHAR(50) NOT NULL,
    title        VARCHAR(200) NOT NULL,
    message      TEXT NOT NULL,
    posted_by    VARCHAR(50),
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mess_id) REFERENCES Mess(mess_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO announcements (mess_id, title, message, posted_by) VALUES
('MESS001','Beef Tomorrow Night 🔥','Tomorrow dinner: Beef Rezala + Firni. Mark attendance!', 'USER001'),
('MESS001','Submit July Bill','Last date to pay July bill: 15th July', 'USER001'),
('MESS002','Welcome New Member','Arif bhai joined today. Welcome him!', 'USER002'),
('MESS001','Rooftop BBQ Party','Friday night BBQ on rooftop. 500 tk per person', 'USER101'),
('MESS003','Ramadan Schedule','Sehri: 4:30 AM, Iftar: 6:15 PM', 'USER003');

-- =============================================
-- 12. seat_ads Table
-- =============================================
CREATE TABLE seat_ads (
    ad_id           INT PRIMARY KEY AUTO_INCREMENT,
    mess_id         VARCHAR(50) NOT NULL,
    room_id         INT DEFAULT NULL,
    vacant_seats    INT NOT NULL DEFAULT 1,
    rent_per_seat   DECIMAL(8,2) NOT NULL,
    contact_person  VARCHAR(100) NOT NULL,
    contact_number  VARCHAR(20) NOT NULL,
    ad_title        VARCHAR(200) DEFAULT 'Seat Available - Contact Now',
    ad_description  TEXT,
    is_active       TINYINT(1) DEFAULT 1,
    posted_at       DATETIME DEFAULT CURRENT_TIMESTAMP,
    expires_at      DATE DEFAULT (DATE_ADD(CURRENT_DATE, INTERVAL 30 DAY)),
    FOREIGN KEY (mess_id) REFERENCES Mess(mess_id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO seat_ads (mess_id, room_id, vacant_seats, rent_per_seat, contact_person, contact_number, ad_description) VALUES
('MESS001',3,2,3800.00,'Raju Bhai','01711111111','AC room with balcony & rooftop access. Only serious people contact.'),
('MESS002',4,1,4500.00,'Sohag Bhai','01722222222','Fully AC + Fridge + High speed WiFi. Best for job holders.'),
('MESS003',NULL,3,2800.00,'Faruk Sir','01733333333','8-bed large room. Special discount for students.'),
('MESS001',1,1,4000.00,'Rakib','01911111111','One seat vacant from August. AC + Study table.'),
('MESS004',NULL,4,3500.00,'Masud','01844444444','Brand new mess. 4 seats available immediately.');

-- =============================================
-- 13. room_members Table
-- =============================================
CREATE TABLE room_members (
    id           INT PRIMARY KEY AUTO_INCREMENT,
    room_id      INT NOT NULL,
    user_id      VARCHAR(50) NOT NULL,
    joined_date  DATE NOT NULL,
    is_current   TINYINT(1) DEFAULT 1,
    UNIQUE KEY uniq_current_member (room_id, user_id),
    FOREIGN KEY (room_id) REFERENCES rooms(room_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES Users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO room_members (room_id, user_id, joined_date, is_current) VALUES
(1,'USER101','2025-01-15',1),
(1,'USER102','2025-02-01',1),
(2,'USER103','2025-03-10',1),
(3,'USER101','2025-06-01',0),
(4,'USER002','2025-01-01',1);

-- =============================================
-- SUCCESS MESSAGE
-- =============================================
SELECT '🎉 Congratulations! Database messContro created successfully with all 13 tables & consistent demo data!' AS Status;
SELECT '🚀 Your Mess Management System is now READY TO LAUNCH with gender + binary photo support!' AS Message;