<?php
// test-uploads.php - Run this first!
echo "<h1>Uploads Folder Test</h1>";

$upload_dir = 'uploads/';

// Check if folder exists
echo "<h2>1. Checking Uploads Folder</h2>";
if (is_dir($upload_dir)) {
    echo "Uploads folder EXISTS<br>";
    echo "Path: " . realpath($upload_dir) . "<br>";
    
    // Check permissions
    $permissions = substr(sprintf('%o', fileperms($upload_dir)), -4);
    echo "Permissions: " . $permissions . " (should be 0777)<br>";
    
    // List all files
    $files = scandir($upload_dir);
    echo "<h2>2. Files in Uploads Folder:</h2>";
    
    if (count($files) <= 2) {
        echo " Folder is EMPTY!<br>";
    } else {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>Filename</th><th>Size</th><th>Readable</th><th>Web URL</th><th>Preview</th></tr>";
        
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $file_path = $upload_dir . $file;
                $file_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/' . $upload_dir . rawurlencode($file);
                
                echo "<tr>";
                echo "<td>" . htmlspecialchars($file) . "</td>";
                echo "<td>" . filesize($file_path) . " bytes</td>";
                echo "<td>" . (is_readable($file_path) ? " YES" : " NO") . "</td>";
                echo "<td><a href='$file_url' target='_blank'>$file_url</a></td>";
                echo "<td>";
                
                // Try to display image
                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                    echo "<img src='$upload_dir" . rawurlencode($file) . "' style='width:100px; height:100px;' onerror='this.style.display=\"none\"'>";
                } else {
                    echo "Not an image";
                }
                
                echo "</td>";
                echo "</tr>";
            }
        }
        echo "</table>";
    }
} else {
    echo "❌ Uploads folder DOES NOT EXIST!<br>";
    
    // Try to create it
    if (mkdir($upload_dir, 0777, true)) {
        echo "✔️ Created uploads folder!<br>";
    } else {
        echo "❌ FAILED to create uploads folder!<br>";
    }
}

echo "<h2>3. Database Photos Check</h2>";

// Check database connection
try {
    $pdo = new PDO("mysql:host=localhost;dbname=kisii_sda_church", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT id, name, photo, created_at FROM testimonies ORDER BY created_at DESC");
    $testimonies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($testimonies) > 0) {
        echo "<table border='1' cellpadding='10'>";
        echo "<tr><th>ID</th><th>Name</th><th>Photo in DB</th><th>File Exists</th><th>Full Path</th></tr>";
        
        foreach ($testimonies as $test) {
            $file_exists = "❌ NO";
            $file_path = "";
            
            if (!empty($test['photo'])) {
                $file_path = realpath($upload_dir . $test['photo']);
                $file_exists = file_exists($upload_dir . $test['photo']) ? "✅ YES" : "❌ NO";
            }
            
            echo "<tr>";
            echo "<td>" . $test['id'] . "</td>";
            echo "<td>" . htmlspecialchars($test['name']) . "</td>";
            echo "<td>" . htmlspecialchars($test['photo']) . "</td>";
            echo "<td>" . $file_exists . "</td>";
            echo "<td>" . $file_path . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "No testimonies in database!";
    }
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage();
}
?>