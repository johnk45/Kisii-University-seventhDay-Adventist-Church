<?php
require_once 'config.php';
requireLogin();

// Get user info
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT * FROM users WHERE id = ?";
$user_stmt = $pdo->prepare($user_sql);
$user_stmt->execute([$user_id]);
$user = $user_stmt->fetch();

// Get stats
$stats_sql = "SELECT 
    COUNT(*) as total_reports,
    SUM(CASE WHEN category = 'financial' THEN 1 ELSE 0 END) as financial_reports,
    SUM(downloads) as total_downloads,
    SUM(views) as total_views
    FROM reports";
$stats_stmt = $pdo->query($stats_sql);
$stats = $stats_stmt->fetch();

// Get recent activities
$activity_sql = "SELECT a.*, u.fullname FROM activity_log a 
                 LEFT JOIN users u ON a.user_id = u.id 
                 ORDER BY a.created_at DESC LIMIT 10";
$activity_stmt = $pdo->query($activity_sql);
$activities = $activity_stmt->fetchAll();

// Get reports for display
$reports_sql = "SELECT * FROM reports ORDER BY created_at DESC LIMIT 20";
$reports_stmt = $pdo->query($reports_sql);
$reports = $reports_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kisii University SDA Church | Dashboard</title>
    <!-- Include all CSS from my original code -->
    <style>
        /*The old CSS styles */
    </style>
</head>
<body>
    <!-- Top Header -->
    <header class="top-header">
        <div style="display: flex; align-items: center; gap: 15px;">
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            <div class="church-brand">
                <div class="logo-icon">
                    <i class="fas fa-church"></i>
                </div>
                <div class="church-info">
                    <h1>Kisii University SDA Church</h1>
                    <div class="subtitle">Reports & Ministry Tracking Portal</div>
                </div>
            </div>
        </div>
        
        <div class="user-profile">
            <div class="user-avatar" style="background-color: <?php echo $_SESSION['avatar_color']; ?>;">
                <?php echo $_SESSION['user_initials']; ?>
            </div>
            <div>
                <div class="user-name"><?php echo htmlspecialchars($_SESSION['user_name']); ?></div>
                <div class="user-role"><?php echo ucfirst($_SESSION['user_role']); ?></div>
            </div>
            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </header>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Sidebar Navigation -->
        <nav class="sidebar" id="sidebar">
            <!-- 8 existing sidebar HTML -->
        </nav>

        <!-- Main Content -->
        <main class="main-content" id="mainContent">
            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h2>Church Reports Dashboard</h2>
                    <p>View, track, and follow up on all church ministry reports and activities</p>
                </div>
                <div class="header-actions">
                    <?php if (isAdmin()): ?>
                    <button class="btn btn-primary" id="newReportBtn">
                        <i class="fas fa-plus"></i> New Report
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-icon financial">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['financial_reports']; ?></h3>
                        <p>Financial Reports</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon attendance">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_reports']; ?></h3>
                        <p>Total Reports</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon events">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_views']; ?></h3>
                        <p>Total Views</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon membership">
                        <i class="fas fa-download"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_downloads']; ?></h3>
                        <p>Total Downloads</p>
                    </div>
                </div>
            </div>

            <!-- Reports Grid -->
            <div class="reports-grid" id="reportsGrid">
                <?php foreach ($reports as $report): ?>
                <div class="report-card" data-category="<?php echo $report['category']; ?>" data-id="<?php echo $report['id']; ?>">
                    <div class="report-header">
                        <h3 class="report-title"><?php echo htmlspecialchars($report['title']); ?></h3>
                        <span class="report-badge badge-<?php echo $report['category']; ?>">
                            <?php echo ucfirst($report['category']); ?>
                        </span>
                    </div>
                    <div class="report-content">
                        <div class="report-meta">
                            <div class="report-author">
                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($report['author']); ?>
                            </div>
                            <div><?php echo date('F j, Y', strtotime($report['report_date'])); ?></div>
                        </div>
                        <p class="report-summary"><?php echo htmlspecialchars($report['summary']); ?></p>
                        <div class="report-stats">
                            <?php if ($report['total_amount']): ?>
                            <div class="report-stat">
                                <i class="fas fa-money-bill"></i>
                                <span><?php echo $report['total_amount']; ?> Total</span>
                            </div>
                            <?php endif; ?>
                            <?php if ($report['attendance']): ?>
                            <div class="report-stat">
                                <i class="fas fa-users"></i>
                                <span><?php echo $report['attendance']; ?> Average</span>
                            </div>
                            <?php endif; ?>
                            <div class="report-stat">
                                <i class="fas fa-eye"></i>
                                <span><?php echo $report['views']; ?> views</span>
                            </div>
                            <div class="report-stat">
                                <i class="fas fa-download"></i>
                                <span><?php echo $report['downloads']; ?> downloads</span>
                            </div>
                        </div>
                        <div class="report-actions">
                            <button class="action-btn view-report" data-id="<?php echo $report['id']; ?>">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                            <?php if ($report['pdf_filename']): ?>
                            <a href="download_report.php?id=<?php echo $report['id']; ?>" class="action-btn download-pdf">
                                <i class="fas fa-download"></i> Download
                            </a>
                            <button class="action-btn pdf view-pdf" 
                                    data-title="<?php echo htmlspecialchars($report['title']); ?>"
                                    data-url="uploads/<?php echo $report['pdf_filename']; ?>">
                                <i class="fas fa-file-pdf"></i> View PDF
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Recent Activity -->
            <div class="recent-activity">
                <div class="activity-header">
                    <h3>Recent Activity</h3>
                </div>
                <ul class="activity-list">
                    <?php foreach ($activities as $activity): ?>
                    <li class="activity-item">
                        <div class="activity-icon <?php echo $activity['activity_type']; ?>">
                            <?php
                            $icons = [
                                'login' => 'fa-sign-in-alt',
                                'registration' => 'fa-user-plus',
                                'report_upload' => 'fa-file-upload',
                                'download' => 'fa-download',
                                'view' => 'fa-eye'
                            ];
                            $icon = $icons[$activity['activity_type']] ?? 'fa-history';
                            ?>
                            <i class="fas <?php echo $icon; ?>"></i>
                        </div>
                        <div class="activity-details">
                            <h4><?php echo htmlspecialchars($activity['description']); ?></h4>
                            <p>By: <?php echo htmlspecialchars($activity['fullname'] ?? 'System'); ?></p>
                        </div>
                        <div class="activity-time">
                            <?php echo time_ago($activity['created_at']); ?>
                        </div>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </main>
    </div>

    <!-- Add Report Modal -->
    <div class="modal" id="addReportModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Upload New Report</h3>
                <button class="modal-close" id="closeAddModal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="reportForm" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="upload_report">
                    
                    <div class="input-group">
                        <label for="reportTitle">Report Title*</label>
                        <i class="fas fa-heading input-icon"></i>
                        <input type="text" id="reportTitle" name="title" placeholder="Enter report title" required>
                    </div>
                    
                    <div class="input-group">
                        <label for="reportCategory">Category*</label>
                        <i class="fas fa-tag input-icon"></i>
                        <select id="reportCategory" name="category" required>
                            <option value="">Select category</option>
                            <option value="financial">Financial</option>
                            <option value="attendance">Attendance</option>
                            <option value="ministry">Ministry</option>
                            <option value="events">Events</option>
                            <option value="youth">Youth & Pathfinders</option>
                        </select>
                    </div>
                    
                    <div class="input-group">
                        <label for="reportAuthor">Author/Department*</label>
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" id="reportAuthor" name="author" placeholder="e.g., Finance Committee" required>
                    </div>
                    
                    <div class="input-group">
                        <label for="reportDate">Report Date*</label>
                        <i class="fas fa-calendar input-icon"></i>
                        <input type="date" id="reportDate" name="date" required>
                    </div>
                    
                    <div class="input-group">
                        <label for="reportSummary">Summary/Description*</label>
                        <i class="fas fa-align-left input-icon"></i>
                        <textarea id="reportSummary" name="summary" rows="3" placeholder="Enter report summary" required></textarea>
                    </div>
                    
                    <div class="input-group">
                        <label for="reportAmount">Total Amount (if financial)</label>
                        <i class="fas fa-money-bill input-icon"></i>
                        <input type="text" id="reportAmount" name="amount" placeholder="e.g., KSh 120,000">
                    </div>
                    
                    <div class="input-group">
                        <label for="reportAttendance">Attendance (if applicable)</label>
                        <i class="fas fa-users input-icon"></i>
                        <input type="number" id="reportAttendance" name="attendance" placeholder="Number of attendees">
                    </div>
                    
                    <div class="input-group">
                        <label for="pdfFile">PDF File*</label>
                        <i class="fas fa-file-pdf input-icon"></i>
                        <input type="file" id="pdfFile" name="pdf" accept=".pdf" required>
                        <small style="color: var(--light-text); font-size: 0.8rem; margin-top: 5px;">
                            Maximum file size: 10MB
                        </small>
                    </div>

                    <div style="display: flex; gap: 15px; margin-top: 30px;">
                        <button type="button" class="btn btn-secondary" id="cancelAddReport" style="flex: 1;">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary" style="flex: 2;">
                            <i class="fas fa-upload"></i> Upload Report
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript for AJAX upload -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const reportForm = document.getElementById('reportForm');
            
            if (reportForm) {
                reportForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    
                    try {
                        const response = await fetch('upload_report.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            alert(result.message);
                            location.reload();
                        } else {
                            alert('Error: ' + result.message);
                        }
                    } catch (error) {
                        alert('Network error: ' + error.message);
                    }
                });
            }
        });
    </script>
</body>
</html>

<?php
// Helper function to display time ago
function time_ago($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        return floor($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
        return floor($diff / 3600) . ' hours ago';
    } elseif ($diff < 604800) {
        return floor($diff / 86400) . ' days ago';
    } else {
        return date('F j, Y', $time);
    }
}
?>