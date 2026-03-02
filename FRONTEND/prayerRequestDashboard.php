<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "kisii_sda_church";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if table exists, create it if not
$tableCheck = $conn->query("SHOW TABLES LIKE 'prayer_requests'");
if ($tableCheck->num_rows == 0) {
    // Table doesn't exist, create it
    $createTableSQL = "
    CREATE TABLE prayer_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) DEFAULT NULL,
        contact_info VARCHAR(255) DEFAULT NULL,
        message TEXT NOT NULL,
        is_confidential TINYINT(1) DEFAULT 0,
        status ENUM('pending', 'prayed', 'answered') DEFAULT 'pending',
        ip_address VARCHAR(45) DEFAULT NULL,
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($createTableSQL)) {
        echo "<div style='background:#d4edda;padding:10px;margin:10px;border-radius:5px;'>
                Table created successfully. No prayer requests yet.
              </div>";
        $result = false;
    } else {
        die("Error creating table: " . $conn->error);
    }
} else {
    // Table exists, fetch data
    $sql = "SELECT id, name, contact_info, message, is_confidential, status, 
                   DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') as formatted_date 
            FROM prayer_requests 
            ORDER BY created_at DESC";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        die("Query error: " . $conn->error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KSUSDA Prayer Dashboard</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .header {
            background: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .header h2 {
            margin: 0;
            font-size: 24px;
        }
        
        .stats-bar {
            background: #f8f9fa;
            padding: 15px;
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            border-bottom: 1px solid #dee2e6;
        }
        
        .stat-item {
            text-align: center;
            padding: 10px;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
        
        .stat-label {
            font-size: 14px;
            color: #6c757d;
        }
        
        table {
            border-collapse: collapse;
            width: 100%;
            background: #fff;
        }
        
        th, td {
            border: 1px solid #dee2e6;
            padding: 12px 15px;
            text-align: left;
        }
        
        th {
            background: #4a6fa5;
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        tr:hover {
            background-color: #e9ecef;
            transition: background-color 0.3s;
        }
        
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 500;
        }
        
        .status-prayed {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 500;
        }
        
        .status-answered {
            background-color: #d4edda;
            color: #155724;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 500;
        }
        
        .confidential-yes {
            background-color: #f8d7da;
            color: #721c24;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 500;
        }
        
        .confidential-no {
            background-color: #d4edda;
            color: #155724;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 500;
        }
        
        .message-cell {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            cursor: pointer;
        }
        
        .message-cell.expanded {
            white-space: normal;
            max-width: none;
        }
        
        .controls {
            padding: 15px;
            background: #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #dee2e6;
        }
        
        .search-box {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 300px;
        }
        
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
        }
        
        .btn-refresh {
            background: #17a2b8;
            color: white;
        }
        
        .btn-refresh:hover {
            background: #138496;
        }
        
        .btn-export {
            background: #28a745;
            color: white;
        }
        
        .btn-export:hover {
            background: #218838;
        }
        
        .footer {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            color: #6c757d;
            font-size: 14px;
            border-top: 1px solid #dee2e6;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px;
            color: #6c757d;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            color: #adb5bd;
        }
        
        @media (max-width: 768px) {
            .stats-bar {
                flex-direction: column;
            }
            
            .controls {
                flex-direction: column;
                gap: 10px;
            }
            
            .search-box {
                width: 100%;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="header">
            <h2><i class="fas fa-pray"></i> KSUSDA PRAYER REQUEST DASHBOARD</h2>
            <p style="margin: 5px 0 0 0; opacity: 0.8;">All prayer requests submitted by members</p>
        </div>
        
        <div class="stats-bar">
            <?php
            // Get statistics
            if ($result && $result->num_rows > 0) {
                // Reset pointer to beginning for counting
                $total = $result->num_rows;
                $pending = 0;
                $prayed = 0;
                $answered = 0;
                $confidential = 0;
                
                // Count different statuses
                $result->data_seek(0); // Reset pointer
                while($row = $result->fetch_assoc()) {
                    if ($row['status'] == 'pending') $pending++;
                    if ($row['status'] == 'prayed') $prayed++;
                    if ($row['status'] == 'answered') $answered++;
                    if ($row['is_confidential'] == 1) $confidential++;
                }
                $result->data_seek(0); // Reset pointer for main display
                ?>
                <div class="stat-item">
                    <div class="stat-value"><?php echo $total; ?></div>
                    <div class="stat-label">Total Requests</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" style="color:#856404;"><?php echo $pending; ?></div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" style="color:#0c5460;"><?php echo $prayed; ?></div>
                    <div class="stat-label">Prayed For</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" style="color:#155724;"><?php echo $answered; ?></div>
                    <div class="stat-label">Answered</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value" style="color:#721c24;"><?php echo $confidential; ?></div>
                    <div class="stat-label">Confidential</div>
                </div>
            <?php } else { ?>
                <div class="stat-item">
                    <div class="stat-value">0</div>
                    <div class="stat-label">Total Requests</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">0</div>
                    <div class="stat-label">Pending</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">0</div>
                    <div class="stat-label">Prayed For</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">0</div>
                    <div class="stat-label">Answered</div>
                </div>
            <?php } ?>
        </div>
        
        <div class="controls">
            <div>
                <input type="text" class="search-box" placeholder="Search prayer requests..." id="searchInput">
            </div>
            <div style="display: flex; gap: 10px;">
                <button class="btn btn-refresh" onclick="location.reload()">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
                <button class="btn btn-export" onclick="exportToCSV()">
                    <i class="fas fa-download"></i> Export CSV
                </button>
            </div>
        </div>
        
        <?php if ($result && $result->num_rows > 0): ?>
            <table id="prayerTable">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Contact</th>
                    <th>Message</th>
                    <th>Confidential</th>
                    <th>Status</th>
                    <th>Date Submitted</th>
                </tr>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['name'] ?: 'Anonymous') ?></td>
                        <td><?= htmlspecialchars($row['contact_info'] ?: 'Not provided') ?></td>
                        <td class="message-cell" onclick="toggleMessage(this)" title="Click to expand/collapse">
                            <?= nl2br(htmlspecialchars(substr($row['message'], 0, 100))) ?>
                            <?php if (strlen($row['message']) > 100): ?>
                                ...
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="<?= $row['is_confidential'] ? 'confidential-yes' : 'confidential-no' ?>">
                                <?= $row['is_confidential'] ? 'Yes' : 'No' ?>
                            </span>
                        </td>
                        <td>
                            <span class="status-<?= $row['status'] ?>">
                                <?= ucfirst($row['status']) ?>
                            </span>
                        </td>
                        <td><?= $row['formatted_date'] ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-praying-hands"></i>
                <h3>No Prayer Requests Yet</h3>
                <p>No prayer requests have been submitted yet.</p>
                <p>When members submit requests, they will appear here.</p>
                <p style="margin-top: 20px;">
                    <a href="prayerrequest.php" style="color: #4a6fa5; text-decoration: none;">
                        <i class="fas fa-external-link-alt"></i> Go to Prayer Request Form
                    </a>
                </p>
            </div>
        <?php endif; ?>
        
        <div class="footer">
            <p>© <?php echo date('Y'); ?> Kisii SDA Church • Last updated: <?php echo date('Y-m-d H:i:s'); ?></p>
            <p>Total records displayed: <?php echo ($result && $result->num_rows) ? $result->num_rows : 0; ?></p>
        </div>
    </div>
    
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#prayerTable tr');
            
            rows.forEach((row, index) => {
                if (index === 0) return; // Skip header row
                
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
        
        // Toggle message expansion
        function toggleMessage(cell) {
            cell.classList.toggle('expanded');
        }
        
        // Export to CSV
        function exportToCSV() {
            const rows = document.querySelectorAll('#prayerTable tr');
            let csvContent = "data:text/csv;charset=utf-8,";
            
            rows.forEach(row => {
                const cols = row.querySelectorAll('td, th');
                const rowData = Array.from(cols).map(col => {
                    // Remove HTML and get text content
                    let text = col.textContent.replace(/\n/g, ' ').trim();
                    // Escape quotes and wrap in quotes if contains comma
                    if (text.includes(',') || text.includes('"')) {
                        text = '"' + text.replace(/"/g, '""') + '"';
                    }
                    return text;
                }).join(',');
                csvContent += rowData + "\r\n";
            });
            
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "prayer_requests_" + new Date().toISOString().split('T')[0] + ".csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        
        // Auto-refresh every 60 seconds
        setTimeout(() => {
            location.reload();
        }, 60000);
    </script>
</body>
</html>



