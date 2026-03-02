<?php
// === ENABLE ERROR DISPLAY ===
error_reporting(E_ALL);
ini_set('display_errors', 1);

// === DATABASE CONNECTION ===
$host = 'localhost';
$dbname = 'kisii_sda_church';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

// === HANDLE FORM SUBMISSION ===
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $story = htmlspecialchars($_POST['story']);
    $year = htmlspecialchars($_POST['year']);
    $ministry = htmlspecialchars($_POST['ministry']);
    
    $photo_name = null;
    
    // HANDLE FILE UPLOAD - FIXED
    if(isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $original_name = $_FILES['photo']['name'];
        $tmp_name = $_FILES['photo']['tmp_name'];
        $file_size = $_FILES['photo']['size'];
        
        // Allowed file types
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = mime_content_type($tmp_name);
        
        if(in_array($file_type, $allowed_types) && $file_size < 5 * 1024 * 1024) { // 5MB max
            
            // Create uploads folder if not exists
            $upload_dir = 'uploads/';
            if(!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate safe filename
            $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
            $safe_name = preg_replace('/[^a-zA-Z0-9]/', '_', pathinfo($original_name, PATHINFO_FILENAME));
            $photo_name = time() . '_' . $safe_name . '.' . $extension;
            
            // Move uploaded file
            $destination = $upload_dir . $photo_name;
            
            if(move_uploaded_file($tmp_name, $destination)) {
                // Make file readable
                chmod($destination, 0644);
            } else {
                $photo_name = null;
            }
        }
    }
    
    // CHECK AND LIMIT TO 30 RECORDS
    $count_stmt = $pdo->query("SELECT COUNT(*) FROM testimonies");
    $count = $count_stmt->fetchColumn();
    
    if($count >= 30) {
        // Delete oldest
        $oldest = $pdo->query("SELECT id, photo FROM testimonies ORDER BY created_at ASC LIMIT 1")->fetch();
        $pdo->prepare("DELETE FROM testimonies WHERE id = ?")->execute([$oldest['id']]);
        
        // Delete old photo file
        if(!empty($oldest['photo']) && file_exists('uploads/' . $oldest['photo'])) {
            unlink('uploads/' . $oldest['photo']);
        }
    }
    
    // INSERT NEW TESTIMONY
    $sql = "INSERT INTO testimonies (name, story, year, ministry, photo) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$name, $story, $year, $ministry, $photo_name]);
    
    header("Location: testimony.php?success=1");
    exit();
}

// === FETCH TESTIMONIES ===
$sql = "SELECT * FROM testimonies ORDER BY created_at DESC LIMIT 30";
$stmt = $pdo->query($sql);
$testimonies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KSUSDA Testimonies</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background: #f8f9fa;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);
            color: white;
            text-align: center;
            padding: 40px 20px;
            border-radius: 10px;
            margin-bottom: 40px;
        }
        
        h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        
        .scripture {
            font-style: italic;
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .success {
            background: #28a745;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 5px;
            margin: 20px 0;
        }
        
        /* TESTIMONIES GRID */
        .testimonies-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(2500px, 1fr));
            gap: 25px;
            margin: 40px 0;
        }
        
        .testimony-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .testimony-card:hover {
            transform: translateY(-5px);
        }
        
        .student-img {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #f0f0f0;
            margin-bottom: 20px;
            background: #f8f9fa;
        }
        
        .quote {
            font-style: italic;
            color: #555;
            margin: 15px 0;
            font-size: 1rem;
        }
        
        .name {
            font-weight: bold;
            color: #333;
            font-size: 1.2rem;
            margin: 10px 0 5px;
        }
        
        .details {
            color: #666;
            margin: 5px 0;
        }
        
        .date {
            color: #888;
            font-size: 0.9rem;
            margin-top: 15px;
        }
        
        /* FORM */
        .form-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin: 60px 0;
        }
        
        .form-title {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }
        
        textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .submit-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 1.1rem;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            transition: background 0.3s;
        }
        
        .submit-btn:hover {
            background: #2980b9;
        }
        
        /* FOOTER */
        footer {
            background: #2c3e50;
            color: white;
            padding: 40px 20px;
            margin-top: 60px;
        }
        
        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 40px;
        }
        
        .footer-section h3 {
            color: #3498db;
            margin-bottom: 20px;
        }
        
        .footer-section ul {
            list-style: none;
        }
        
        .footer-section li {
            margin: 10px 0;
        }
        
        .footer-section a {
            color: #ecf0f1;
            text-decoration: none;
        }
        
        .footer-section a:hover {
            color: #3498db;
        }
        
        @media (max-width: 768px) {
            .testimonies-grid {
                grid-template-columns: 1fr;
            }
            
            .footer-content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Student Testimonials</h1>
            <p class="scripture">"And they overcame him by the blood of the Lamb, and by the word of their testimony..."</p>
        </header>
        
        <?php if(isset($_GET['success'])): ?>
        <div class="success">
             Thank you for sharing your testimony!
        </div>
        <?php endif; ?>
        
        <div class="testimonies-grid">
            <?php foreach($testimonies as $test): ?>
            <div class="testimony-card">
                <?php
                // FIXED IMAGE DISPLAY CODE
                $image_path = 'uploads/1769427607_Aloe Vera Gel.jpeg'; // Default
                
                if(!empty($test['photo'])) {
                    $uploaded_path = 'uploads/' . $test['photo'];
                    
                    // Check if file exists and is readable
                    if(file_exists($uploaded_path) && is_readable($uploaded_path)) {
                        $image_path = $uploaded_path;
                    }
                }
                ?>
                
                <img src="<?php echo $image_path; ?>" 
                     alt="<?php echo htmlspecialchars($test['name']); ?>"
                     class="student-img"
                     onerror="this.src='uploads/visit.jpeg';">
                
                <p class="quote">"<?php echo htmlspecialchars($test['story']); ?></p>
                <p class="name"><?php echo htmlspecialchars($test['name']); ?></p>
                <p class="details"><?php echo htmlspecialchars($test['ministry']); ?></p>
                <p class="details"><?php echo htmlspecialchars($test['year']); ?></p>
                <p class="date"><?php echo date('F j, Y', strtotime($test['created_at'])); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        
        <div class="form-container">
            <h2 class="form-title">Share Your Story</h2>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" required>
                </div>
                
                <div class="form-group">
                    <label>Year of Study *</label>
                    <select name="year" required>
                        <option value="">Select Year</option>
                        <option>1st Year</option>
                        <option>2nd Year</option>
                        <option>3rd Year</option>
                        <option>4th Year</option>
                        <option>Associate</option>
                        <option>Alumni</option>
                        <option>Staff Member</option>
                        <option>Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Ministry / Role *</label>
                    <input type="text" name="ministry" placeholder="e.g. Choir, Prayer Band" required>
                </div>
                
                <div class="form-group">
                    <label>Your Testimony *</label>
                    <textarea name="story" placeholder="Share your experience..." required></textarea>
                </div>
                
                <div class="form-group">
                    <label>Upload Photo (Optional)</label>
                    <input type="file" name="photo" accept="image/*">
                    <small style="color:#666; display:block; margin-top:5px;">Max 5MB. JPG, PNG, GIF allowed.</small>
                </div>
                
                <button type="submit" class="submit-btn">Submit Testimony</button>
            </form>
        </div>
    </div>
    
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>KSUSDA</h3>
                <p>Sharing God's faithfulness through testimonies.</p>
            </div>
            
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="index45.html">Home</a></li>
                    <li><a href="testimony.php">Testimonies</a></li>
                    <li><a href="abouttt.html">About</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Connect</h3>
                <ul>
                    <li><a href="#">Facebook</a></li>
                    <li><a href="#">WhatsApp</a></li>
                    <li><a href="#">Email</a></li>
                </ul>
            </div>
        </div>
    </footer>
    
    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const name = document.querySelector('input[name="name"]').value.trim();
            const story = document.querySelector('textarea[name="story"]').value.trim();
            
            if(name.length < 2) {
                e.preventDefault();
                alert('Please enter a valid name (at least 2 characters)');
                return false;
            }
            
            if(story.length < 20) {
                e.preventDefault();
                alert('Please share a more detailed testimony (at least 20 characters)');
                return false;
            }
            
            return true;
        });
        
        // Auto-hide success message
        <?php if(isset($_GET['success'])): ?>
        setTimeout(function() {
            const msg = document.querySelector('.success');
            if(msg) msg.style.display = 'none';
        }, 5000);
        <?php endif; ?>
    </script>
</body>
</html>