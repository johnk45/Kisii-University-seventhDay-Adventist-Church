<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>📊 Database Setup</h1>";

// Database credentials
$host = "localhost";
$user = "root";
$pass = "";

// Create connection
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("❌ MySQL Connection Failed: " . $conn->connect_error);
}

echo "✅ Connected to MySQL<br>";

// Create database
$dbname = "payment_system";
if ($conn->query("CREATE DATABASE IF NOT EXISTS $dbname")) {
    echo "✅ Database '$dbname' created/selected<br>";
} else {
    echo "❌ Database creation failed: " . $conn->error . "<br>";
}

// Select database
$conn->select_db($dbname);

// Create transactions table
$sql = "CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaction_id VARCHAR(50) UNIQUE,
    order_reference VARCHAR(100) NOT NULL,
    provider VARCHAR(20) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency CHAR(3) DEFAULT 'KES',
    phone_number VARCHAR(20) NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    checkout_request_id VARCHAR(100),
    merchant_request_id VARCHAR(100),
    result_code VARCHAR(50),
    result_description TEXT,
    raw_callback_data TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL
)";

if ($conn->query($sql)) {
    echo "✅ Table 'transactions' created<br>";
} else {
    echo "❌ Table creation failed: " . $conn->error . "<br>";
}

// Create api_logs table (optional for debugging)
$sql2 = "CREATE TABLE IF NOT EXISTS api_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    endpoint VARCHAR(100),
    request TEXT,
    response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql2)) {
    echo "✅ Table 'api_logs' created (for debugging)<br>";
}

// Test insert
$test_sql = "INSERT INTO transactions (transaction_id, order_reference, provider, amount, phone_number, status) 
             VALUES ('TXN-TEST-001', 'ORDER-TEST', 'mpesa', 100.00, '254712345678', 'success')";

if ($conn->query($test_sql)) {
    echo "✅ Test transaction inserted<br>";
} else {
    echo "⚠️ Test insert failed (might already exist): " . $conn->error . "<br>";
}

// Show tables
echo "<h2>📋 Database Tables:</h2>";
$result = $conn->query("SHOW TABLES");
echo "<ul>";
while ($row = $result->fetch_array()) {
    echo "<li>" . $row[0] . "</li>";
}
echo "</ul>";

// Show transactions
echo "<h2>💰 Sample Transactions:</h2>";
$result = $conn->query("SELECT * FROM transactions LIMIT 5");
if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Transaction ID</th><th>Amount</th><th>Phone</th><th>Status</th><th>Created</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['transaction_id'] . "</td>";
        echo "<td>KES " . $row['amount'] . "</td>";
        echo "<td>" . $row['phone_number'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "No transactions found<br>";
}

$conn->close();

echo "<hr>";
echo "<h2>🎉 Setup Complete!</h2>";
echo "<p>Your database is ready. Now you can use the payment system.</p>";
echo "<p><a href='index.php'>Go to Payment System</a></p>";
?>