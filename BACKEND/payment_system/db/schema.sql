-- Create database
CREATE DATABASE IF NOT EXISTS payment_system;
USE payment_system;

-- Transactions table
CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    transaction_id VARCHAR(50) UNIQUE,
    order_reference VARCHAR(100) NOT NULL,
    provider ENUM('mpesa', 'airtel') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency CHAR(3) DEFAULT 'KES',
    phone_number VARCHAR(20) NOT NULL,
    status ENUM('pending', 'initiated', 'success', 'failed', 'cancelled') DEFAULT 'pending',
    checkout_request_id VARCHAR(100),
    merchant_request_id VARCHAR(100),
    result_code VARCHAR(50),
    result_description TEXT,
    raw_callback_data JSON,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    INDEX idx_status (status),
    INDEX idx_checkout_id (checkout_request_id),
    INDEX idx_merchant_id (merchant_request_id)
);

-- Optional: Users table if you need accounts
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- API logs for debugging
CREATE TABLE api_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    endpoint VARCHAR(100),
    request TEXT,
    response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);