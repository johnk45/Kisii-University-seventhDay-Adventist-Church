<?php
// No need to include config.php here since it's already included in index.php
// The functions will be available from the parent scope

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $fullname = sanitize($_POST['fullname']);
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $role = sanitize($_POST['role']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // Check if user already exists
    $checkSql = "SELECT id FROM users WHERE email = ? OR username = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$email, $username]);
    
    if ($checkStmt->rowCount() > 0) {
        $_SESSION['error'] = 'Username or email already exists';
        header('Location: index.php');
        exit();
    }
    
    // Insert new user
    $sql = "INSERT INTO users (fullname, username, email, phone, role, password, avatar_color) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $avatarColor = generateAvatarColor($fullname);
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$fullname, $username, $email, $phone, $role, $password, $avatarColor]);
        
        $userId = $pdo->lastInsertId();
        
        // Log activity
        $activityStmt = $pdo->prepare("INSERT INTO activity_log (user_id, activity_type, description) VALUES (?, ?, ?)");
        $activityStmt->execute([$userId, 'registration', 'New user registered']);
        
        // Auto-login after registration
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_name'] = $fullname;
        $_SESSION['user_role'] = $role;
        $_SESSION['user_initials'] = strtoupper(substr($fullname, 0, 1) . substr(explode(' ', $fullname)[1] ?? '', 0, 1));
        $_SESSION['avatar_color'] = $avatarColor;
        
        // Redirect to dashboard
        header('Location: index.php');
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Registration failed: ' . $e->getMessage();
        header('Location: index.php');
        exit();
    }
}
?>