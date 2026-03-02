<?php
require_once 'config.php';

// Check if admin exists
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
$stmt->execute(['admin', 'admin@kisiisda.org']);
$admin = $stmt->fetch();

if ($admin) {
    // Update password
    $newPassword = 'admin123';
    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$newHash, $admin['id']]);
    echo "Admin password updated to: admin123<br>";
} else {
    // Create admin
    $newPassword = 'admin123';
    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (fullname, username, email, phone, role, password) 
            VALUES ('Church Admin', 'admin', 'admin@kisiisda.org', '0791302316', 'admin', ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$newHash]);
    echo "Admin user created with password: admin123<br>";
}

// Verify
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute(['admin']);
$admin = $stmt->fetch();

if ($admin) {
    echo "Admin user exists.<br>";
    echo "Username: " . $admin['username'] . "<br>";
    echo "Email: " . $admin['email'] . "<br>";
    echo "Role: " . $admin['role'] . "<br>";
    echo "Password hash: " . $admin['password'] . "<br>";

    // Test the password
    if (password_verify('admin123', $admin['password'])) {
        echo "Password verified successfully!";
    } else {
        echo "Password verification failed!";
    }
} else {
    echo "Failed to create admin user.";
}