<?php
require_once 'config.php';

// Create demo admin user
$adminPassword = password_hash('admin123', PASSWORD_DEFAULT);
$sql = "INSERT INTO users (fullname, username, email, phone, role, password) 
        VALUES ('Church Admin', 'admin', 'admin@kisiisda.org', '0712345678', 'admin', ?)";
$pdo->prepare($sql)->execute([$adminPassword]);

// Create demo member
$memberPassword = password_hash('member123', PASSWORD_DEFAULT);
$sql = "INSERT INTO users (fullname, username, email, phone, role, password) 
        VALUES ('John Doe', 'johndoe', 'john@example.com', '0712345679', 'student', ?)";
$pdo->prepare($sql)->execute([$memberPassword]);

echo "Database seeded successfully!";
?>

