<?php
$host = 'localhost';
$dbname = 'kisii_sda_church';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if the prayer_requests table exists, create if not
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'prayer_requests'");
    if ($tableCheck->rowCount() == 0) {
        // Create the table if it doesn't exist
        $createTableSQL = "
        CREATE TABLE IF NOT EXISTS prayer_requests (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) DEFAULT NULL,
            contact_info VARCHAR(255) DEFAULT NULL,
            message TEXT NOT NULL,
            is_confidential TINYINT(1) DEFAULT 0,
            status ENUM('pending', 'prayed', 'answered') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $pdo->exec($createTableSQL);
    }
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle form submission
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = !empty($_POST['name']) ? trim($_POST['name']) : NULL;
    $contact_info = !empty($_POST['email']) ? trim($_POST['email']) : NULL;
    $message = !empty($_POST['message']) ? trim($_POST['message']) : '';
    $is_confidential = isset($_POST['is_confidential']) ? (int)$_POST['is_confidential'] : 0;
    
    // Validate required fields
    if (empty($message)) {
        $error = "Please enter your prayer request message.";
    } else {
        try {
            // Prepare SQL statement
            $sql = "INSERT INTO prayer_requests (name, contact_info, message, is_confidential) 
                    VALUES (:name, :contact_info, :message, :is_confidential)";
            
            $stmt = $pdo->prepare($sql);
            
            // Bind parameters
            $stmt->bindParam(':name', $name, PDO::PARAM_STR);
            $stmt->bindParam(':contact_info', $contact_info, PDO::PARAM_STR);
            $stmt->bindParam(':message', $message, PDO::PARAM_STR);
            $stmt->bindParam(':is_confidential', $is_confidential, PDO::PARAM_INT);
            
            // Execute the statement
            if ($stmt->execute()) {
                $success = " Your prayer request has been submitted successfully!";
                
                // Clear form data
                $_POST['name'] = '';
                $_POST['email'] = '';
                $_POST['message'] = '';
                $_POST['is_confidential'] = 0;
            } else {
                $error = "❌ Failed to submit your prayer request. Please try again.";
            }
        } catch(PDOException $e) {
            $error = "❌ Database error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/jpg" href="image/assetss/kisii sda logo.png">
    <title>Prayer Request</title>
    <style>
        body{
           box-sizing: border-box;
           background-color: #f8fafc;
           margin: 0;
           padding: 20px;
        }
        html{
            scroll-behavior: smooth;
        }
        
        /* Success/Error Messages */
        .alert {
            padding: 15px;
            margin: 20px auto;
            border-radius: 6px;
            max-width: 600px;
            text-align: center;
            font-weight: bold;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .prayer-request-container{
            background: #fff;
            max-width:600px;
            padding:30px;
            font-family:Georgia, 'Times New Roman', Times, serif;
            margin:auto;
            box-shadow:0 2px 25px rgba(0,0,0,0.7);
            border-radius:8px;
            margin-bottom: 5rem;
        }
        
        h2{
            margin-bottom:20px;
            animation:zoomInOut 3s infinite ease-in-out;
            text-align:center;
        }
        
        @keyframes zoomInOut{
            0%,100%{transform:scale(1);color:slateblue;}
            50%{transform:scale(1.05);color:blue;}
        }
        
        input,textarea,select{
            width:100%;
            padding:10px;
            font-size:1rem;
            color:#00bcd4;
            margin-top:5px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        label{
            display:block;
            margin-top:15px;
            font-family: sans-serif;
            color:#00bcd4;
            font-weight: 600;
        }
        
        .input{
            border-top:none;
            border-left:none;
            border-right:none;
        }
        
        button{
            width:100%;
            font-size: 1.2rem;
            margin-top:20px;
            margin-bottom:24px;
            background:#007bff;
            cursor:pointer;
            color:white;
            border:none;
            border-radius:6px;
            padding:15px;
            transition: background 0.3s;
        }
        
        button:hover{
            background: #0053b3;
            font-weight:bold;
        }
        
        .page-title {
            text-align:center;
            margin-bottom:30px;
            color:#0a0a23;
            font-size: 1.8rem;
            font-weight:700;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            padding: 10px;
        }
        
        .view-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .view-link a {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
            padding: 10px 20px;
            border: 2px solid #007bff;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .view-link a:hover {
            background: #007bff;
            color: white;
        }

        @media(max-width:600px){
            h2{
                font-size: 18px;
                margin-bottom:20px;
                animation:zoomInOut 3s infinite ease-in-out;
                text-align:center;
            }
            
            @keyframes zoomInOut{
                0%,100%{transform:scale(1);color:slateblue;}
                50%{transform:scale(1.05);color:blue;}
            }
            
            label,input,button,textarea,select{
                font-size: 16px;
                padding:12px;
            }
            
            .prayer-request-container{
                background: #fff;
                max-width:400px;
                padding:20px;
                font-family:Georgia, 'Times New Roman', Times, serif;
                margin:auto;
                box-shadow:0 2px 15px rgba(0,0,0,0.1);
                border-radius:6px;
                margin-bottom:2rem;
            }
            
            .page-title {
                font-size: 1.4rem;
                margin-bottom: 20px;
            }
        }

        .char-counter {
            text-align: right;
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="page-title">KSUSDA PRAYER REQUEST SUBMISSION PAGE</div>
    
    <!-- Display Success/Error Messages -->
    <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="prayer-request-container">
        <form action="" method="post" onsubmit="return validateForm()">
            <h2>Submit your Prayer Request</h2>
            
            <label for="name">Name (Optional)</label>
            <input type="text" id="name" name="name" placeholder="Enter your name" class="input"
                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">

            <label for="email">Email / Phone (Optional)</label>
            <input type="text" id="email" name="email" placeholder="Enter your contact info" class="input"
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">

            <label for="message">Your Request *</label>
            <textarea id="message" name="message" rows="5" placeholder="Type your Prayer Request..." 
                     required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
            <div id="charCounter" class="char-counter">0 characters</div>
                
            <label>Privacy</label>
            <select name="is_confidential" class="input">
                <option value="0" <?php echo (isset($_POST['is_confidential']) && $_POST['is_confidential'] == 0) ? 'selected' : ''; ?>>
                    Public (Can be shared)
                </option>
                <option value="1" <?php echo (isset($_POST['is_confidential']) && $_POST['is_confidential'] == 1) ? 'selected' : ''; ?>>
                    Confidential (Pastoral team only)
                </option>
            </select>
            
            <button type="submit">Send Request</button> 
        </form>
    </div>
    
    <div class="view-link">
        <a href="prayerRequestDashboard.php" target="_blank">View Prayer Requests Dashboard</a>
    </div>
    
</body>
</html>

<script>
    // Character counter for message field
    const messageField = document.getElementById('message');
    const charCounter = document.getElementById('charCounter');
    
    function updateCharCounter() {
        const length = messageField.value.length;
        charCounter.textContent = length + ' characters';
        
        if (length < 10) {
            charCounter.style.color = '#dc3545';
        } else if (length > 2000) {
            charCounter.style.color = '#dc3545';
        } else {
            charCounter.style.color = '#28a745';
        }
    }
    
    messageField.addEventListener('input', updateCharCounter);
    updateCharCounter();
    
    // Form validation
    function validateForm() {
        const name = document.getElementById('name').value.trim();
        const contact = document.getElementById('email').value.trim();
        const message = document.getElementById('message').value.trim();
        
        // Validate message
        if (message.length < 10) {
            alert('Please provide more details in your prayer request (at least 10 characters).');
            document.getElementById('message').focus();
            return false;
        }
        
        if (message.length > 2000) {
            alert('Prayer request is too long. Please keep it under 2000 characters.');
            document.getElementById('message').focus();
            return false;
        }
        
        // Optional: Validate email format if provided
        if (contact && contact.includes('@')) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(contact)) {
                alert('Please enter a valid email address.');
                document.getElementById('email').focus();
                return false;
            }
        }
        
        // Optional: Validate phone format if provided (basic check)
        if (contact && !contact.includes('@')) {
            const phoneRegex = /^[0-9\s\-\+\(\)]{10,}$/;
            if (!phoneRegex.test(contact.replace(/\s/g, ''))) {
                alert('Please enter a valid phone number (at least 10 digits).');
                document.getElementById('email').focus();
                return false;
            }
        }
        
        return true;
    }
    
    // Remove the problematic setTimeout
    // setTimeout(()=>{
    //     document.style.background="#ff7200";
    // },5000);
</script>