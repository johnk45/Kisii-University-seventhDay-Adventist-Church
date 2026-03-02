<?php
// No need to include config.php here since it's already included in index.php
// The functions will be available from the parent scope

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $identifier = sanitize($_POST['identifier']);
    $password = $_POST['password'];
    
    // Check if user exists by email or username
    $sql = "SELECT * FROM users WHERE email = ? OR username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['fullname'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_initials'] = strtoupper(substr($user['fullname'], 0, 1) . substr(explode(' ', $user['fullname'])[1] ?? '', 0, 1));
        $_SESSION['avatar_color'] = $user['avatar_color'] ?? generateAvatarColor($user['fullname']);
        
        // Update user's avatar color if not set
        if (!$user['avatar_color']) {
            $color = generateAvatarColor($user['fullname']);
            $updateStmt = $pdo->prepare("UPDATE users SET avatar_color = ? WHERE id = ?");
            $updateStmt->execute([$color, $user['id']]);
        }
        
        // Log activity
        $activityStmt = $pdo->prepare("INSERT INTO activity_log (user_id, activity_type, description) VALUES (?, ?, ?)");
        $activityStmt->execute([$user['id'], 'login', 'User logged in']);
        
        // Redirect to dashboard
        header('Location: index.php');
        exit();
    } else {
        $_SESSION['error'] = 'Invalid credentials';
        header('Location: index.php');
        exit();
    }
}
?>