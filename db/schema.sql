-- Library Management System Database Schema
-- Run this file once to create all tables

CREATE DATABASE IF NOT EXISTS library_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE library_db;

-- ─────────────────────────────────────────
-- Authentication System
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS authentication_system (
    login_id    VARCHAR(50) PRIMARY KEY,
    password    VARCHAR(255) NOT NULL,  -- bcrypt hashed
    role        ENUM('staff', 'reader') NOT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ─────────────────────────────────────────
-- Staff
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS staff (
    staff_id    VARCHAR(20) PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    login_id    VARCHAR(50) UNIQUE,
    FOREIGN KEY (login_id) REFERENCES authentication_system(login_id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────
-- Publisher
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS publisher (
    publisher_id        VARCHAR(20) PRIMARY KEY,
    name                VARCHAR(150) NOT NULL,
    year_of_publication YEAR
);

-- ─────────────────────────────────────────
-- Books
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS books (
    isbn            VARCHAR(20) PRIMARY KEY,
    title           VARCHAR(200) NOT NULL,
    auth_no         VARCHAR(50),
    category        VARCHAR(80),
    edition         VARCHAR(20),
    price           DECIMAL(10,2),
    total_copies    INT UNSIGNED DEFAULT 1,
    available_copies INT UNSIGNED DEFAULT 1,
    publisher_id    VARCHAR(20),
    FOREIGN KEY (publisher_id) REFERENCES publisher(publisher_id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────
-- Readers
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS readers (
    user_id     VARCHAR(20) PRIMARY KEY,
    reg_no      VARCHAR(20) UNIQUE NOT NULL,
    firstname   VARCHAR(80) NOT NULL,
    lastname    VARCHAR(80) NOT NULL,
    name        VARCHAR(160) GENERATED ALWAYS AS (CONCAT(firstname, ' ', lastname)) STORED,
    email       VARCHAR(150) UNIQUE,
    phone_no    VARCHAR(20),
    address     TEXT,
    login_id    VARCHAR(50) UNIQUE,
    FOREIGN KEY (login_id) REFERENCES authentication_system(login_id) ON DELETE SET NULL
);

-- ─────────────────────────────────────────
-- Reports (managed by staff)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS reports (
    report_id       INT AUTO_INCREMENT PRIMARY KEY,
    user_id         VARCHAR(20) NOT NULL,           -- staff user_id (via Login)
    reg_no          VARCHAR(20),                    -- reader reg_no
    book_no         VARCHAR(20),
    issue_return    ENUM('issue', 'return') NOT NULL,
    report_date     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reg_no) REFERENCES readers(reg_no) ON DELETE SET NULL
);

-- ─────────────────────────────────────────
-- Issue / Return (reserve and track)
-- ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS issue_return (
    book_no             VARCHAR(20) PRIMARY KEY,
    isbn                VARCHAR(20) NOT NULL,
    reader_reg_no       VARCHAR(20) NOT NULL,
    reserve_date        DATE,
    issue_date          DATE,
    due_date            DATE,
    return_date         DATE,
    status              ENUM('reserved', 'issued', 'returned', 'overdue') DEFAULT 'issued',
    FOREIGN KEY (isbn) REFERENCES books(isbn) ON DELETE CASCADE,
    FOREIGN KEY (reader_reg_no) REFERENCES readers(reg_no) ON DELETE CASCADE
);

-- ─────────────────────────────────────────
-- Seed: default admin account
-- Password: admin123 (bcrypt)
-- ─────────────────────────────────────────
INSERT IGNORE INTO authentication_system (login_id, password, role)
VALUES ('admin', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff');

INSERT IGNORE INTO staff (staff_id, name, login_id)
VALUES ('ST001', 'System Administrator', 'admin');
