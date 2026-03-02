<?php
echo "<h1>Server Test</h1>";

// Check PHP
echo "PHP Version: " . phpversion() . "<br>";

// Check MySQL
$conn = new mysqli("localhost", "root", "");
if ($conn->connect_error) {
    echo "MySQL Connection: <span style='color:red'>FAILED</span> - " . $conn->connect_error . "<br>";
} else {
    echo "MySQL Connection: <span style='color:green'>SUCCESS</span><br>";
    $conn->close();
}

// Check directory permissions
$path = __DIR__;
echo "Current Directory: " . $path . "<br>";
echo "Is Writable: " . (is_writable($path) ? "Yes" : "No") . "<br>";

// Check if logs directory exists
$logs_dir = $path . '/logs';
if (!file_exists($logs_dir)) {
    if (mkdir($logs_dir, 0777, true)) {
        echo "Created logs directory<br>";
    } else {
        echo "Failed to create logs directory<br>";
    }
}

// Display all PHP errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>