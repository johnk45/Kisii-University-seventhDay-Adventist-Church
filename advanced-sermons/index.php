<?php
// Start session and include config
session_start();
require_once 'config.php';

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit();
}

// Handle login/register form submissions
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'login':
                require_once 'login.php';
                break;
            case 'register':
                require_once 'register.php';
                break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kisii University SDA Church | Complete Portal</title>
   

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Source+Sans+Pro:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf_viewer.min.css" />
    <link rel="icon" type="image/png" href="image/kisii_sda_logo-removebg-preview.png" alt="sda logo">
    <style>
        /* Reset & Variables */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #0c135b; /* SDA Church green */
            --secondary-color: #1c352e;
            --accent-color: #d4a017; /* Gold accent */
            --campus-blue: #1e3a8a; /* University color */
            --light-color: #f5f9f7;
            --dark-color: #1a3c34;
            --text-color: #333;
            --light-text: #777;
            --sidebar-width: 280px;
            --success-color: #586516;
            --warning-color: #f39c12;
            --error-color: #e74c3c;
        }

        body {
            font-family: 'Poppins', sans-serif;
      color: #222;
      line-height: 1.6;
      background: linear-gradient(-45deg, #e3f2fd, #cfd8dc, #bbdefb, #90caf9);
      background-size: 400% 400%;
      transition:background 0.3s ease;
        }

        body.auth-page {
            background: linear-gradient(135deg, rgba(12, 91, 71, 0.05) 0%, rgba(30, 58, 138, 0.05) 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        /* Top Header */
        .top-header {
      background: linear-gradient(135deg, rgba(9, 9, 53, 0.913) 0%, #0f172a 100%);
      color:white;
      padding: 1rem 5%;
      display: flex;
      justify-content: space-between;
      align-items: center;
      position: sticky;
      top: 0;
      z-index: 1000;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
        }

        .church-brand {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-icon {
            background-color: white;
            color: var(--primary-color);
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
        }

        .church-info h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 1.5rem;
            margin-bottom: 3px;
        }

        .church-info .subtitle {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background-color: white;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.2rem;
        }

        .user-name {
            font-weight: 600;
            font-size: 18px;;
        }

        .user-role {
            font-size: 0.7rem;
            opacity: 0.9;
        }

        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 8px 15px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Main Layout */
        .main-container {
            display: flex;
            min-height: calc(100vh - 80px);
        }

        /* Sidebar Navigation */
        .sidebar {
            width: var(--sidebar-width);
            background-color: white;
            border-right: 1px solid #eaeaea;
            padding: 25px 0;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
        }

        .nav-section {
            margin-bottom: 30px;
            padding: 0 20px;
        }

        .nav-section h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
            text-transform: uppercase;
            color: var(--light-text);
            margin-bottom: 15px;
            letter-spacing: 0.5px;
        }

        .nav-links {
            list-style: none;
        }

        .nav-links li {
            margin-bottom: 5px;
        }

        .nav-links a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            color: var(--text-color);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .nav-links a:hover {
            background-color: rgba(12, 91, 71, 0.05);
            color: var(--primary-color);
        }

        .nav-links a.active {
            background-color: rgba(12, 91, 71, 0.1);
            color: var(--primary-color);
            font-weight: 600;
            border-left: 4px solid var(--primary-color);
        }

        .nav-links i {
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
            transition: margin-left 0.3s;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-header h2 {
            font-family: 'Poppins', sans-serif;
            font-size: 1.8rem;
            color: var(--primary-color);
        }

        .page-header p {
            color: var(--light-text);
            margin-top: 5px;
        }

        .header-actions {
            display: flex;
            gap: 15px;
        }

        /* Buttons */
        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(12, 91, 71, 0.2);
        }

        .btn-secondary {
            background-color: white;
            color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }

        .btn-secondary:hover {
            background-color: rgba(12, 91, 71, 0.05);
        }

        .btn-accent {
            background-color: var(--accent-color);
            color: white;
        }

        .btn-accent:hover {
            background-color: #c19115;
            transform: translateY(-2px);
        }

        /* Authentication Pages */
        .auth-container {
            width: 100%;
            max-width: 1100px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 25px;
        }

        .auth-header {
            text-align: center;
            width: 100%;
            margin-bottom: 10px;
        }

        .campus-header {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .university-badge {
            background-color: var(--campus-blue);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .auth-header-tagline {
            font-size: 1.1rem;
            color: var(--dark-color);
            max-width: 80%;
            margin: 0 auto 15px;
            padding: 10px 20px;
            background-color: rgba(12, 91, 71, 0.08);
            border-radius: 10px;
        }

        /* Stats Cards */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
        }

        .stat-icon.financial {
            background-color: rgba(212, 160, 23, 0.1);
            color: var(--accent-color);
        }

        .stat-icon.attendance {
            background-color: rgba(30, 58, 138, 0.1);
            color: var(--campus-blue);
        }

        .stat-icon.events {
            background-color: rgba(12, 91, 71, 0.1);
            color: var(--primary-color);
        }

        .stat-icon.membership {
            background-color: rgba(45, 140, 111, 0.1);
            color: var(--secondary-color);
        }

        .stat-info h3 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 5px;
        }

        .stat-info p {
            color: var(--light-text);
            font-size: 0.9rem;
        }

        /* Reports Grid */
        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .report-card {
            background-color: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
        }

        .report-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .report-header {
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .report-title {
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            color: var(--dark-color);
        }

        .report-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .badge-financial {
            background-color: rgba(212, 160, 23, 0.1);
            color: var(--accent-color);
        }

        .badge-attendance {
            background-color: rgba(30, 58, 138, 0.1);
            color: var(--campus-blue);
        }

        .badge-ministry {
            background-color: rgba(12, 91, 71, 0.1);
            color: var(--primary-color);
        }

        .badge-events {
            background-color: rgba(45, 140, 111, 0.1);
            color: var(--secondary-color);
        }

        .report-content {
            padding: 20px;
        }

        .report-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 0.85rem;
            color: var(--light-text);
        }

        .report-summary {
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .report-stats {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .report-stat {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
        }

        .report-stat i {
            color: var(--primary-color);
        }

        .report-actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            flex: 1;
            padding: 8px;
            border: 1px solid #e0e0e0;
            background-color: white;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .action-btn:hover {
            background-color: rgba(12, 91, 71, 0.05);
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .action-btn.pdf {
            color: #e74c3c;
            border-color: #e74c3c;
        }

        .action-btn.pdf:hover {
            background-color: rgba(231, 76, 60, 0.05);
        }

        /* Recent Activity */
        .recent-activity {
            background-color: white;
            border-radius: 12px;
            padding: 25px;
            margin-top: 40px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .activity-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .activity-header h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 1.3rem;
            color: var(--dark-color);
        }

        .activity-list {
            list-style: none;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1rem;
        }

        .activity-icon.report {
            background-color: var(--primary-color);
        }

        .activity-icon.comment {
            background-color: var(--campus-blue);
        }

        .activity-icon.download {
            background-color: var(--accent-color);
        }

        .activity-icon.update {
            background-color: var(--secondary-color);
        }

        .activity-details h4 {
            font-weight: 600;
            margin-bottom: 3px;
        }

        .activity-details p {
            font-size: 0.85rem;
            color: var(--light-text);
        }

        .activity-time {
            font-size: 0.8rem;
            color: var(--light-text);
            margin-left: auto;
        }

        /* Modals */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background-color: white;
            border-radius: 12px;
            width: 90%;
            max-width: 700px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            padding: 25px 30px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 1.5rem;
            color: var(--primary-color);
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--light-text);
            cursor: pointer;
        }

        .modal-body {
            padding: 30px;
        }

        /* PDF Viewer Modal */
        .pdf-modal {
            width: 90%;
            max-width: 1000px;
        }

        .pdf-controls {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-left: auto;
        }

        #pdfCanvas {
            margin: 0 auto;
            display: block;
        }

        .page-info {
            padding: 5px 15px;
            background-color: rgba(12, 91, 71, 0.1);
            border-radius: 4px;
            font-weight: 600;
        }

        /* Loading indicators */
        .loading {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 200px;
            color: var(--primary-color);
        }

        .loading i {
            font-size: 2rem;
            margin-bottom: 15px;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Message System */
        .message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            z-index: 3000;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            animation: slideIn 0.3s ease;
            max-width: 350px;
        }

        .message.success {
            background-color: var(--success-color);
            border-left: 5px solid #27ae60;
        }

        .message.error {
            background-color: var(--error-color);
            border-left: 5px solid #c0392b;
        }

        .message.warning {
            background-color: var(--warning-color);
            border-left: 5px solid #d35400;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* Login/Register Forms */
        .auth-panel {
            width: 100%;
            display: flex;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(12, 91, 71, 0.1);
            min-height: 600px;
        }

        .form-container {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-container h2 {
            font-family: 'Poppins', sans-serif;
            color: var(--primary-color);
            margin-bottom: 10px;
            font-size: 1.3rem;
            font-weight: 700;
        }

        .form-container p {
            color: var(--light-text);
            margin-bottom: 30px;
        }

        .form-toggle {
            display: flex;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
        }

        .toggle-btn {
            flex: 1;
            padding: 15px;
            background: none;
            border: none;
            font-family: 'Poppins', sans-serif;
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--light-text);
            cursor: pointer;
            transition: all 0.3s;
            position: relative;
        }

        .toggle-btn.active {
            color: var(--primary-color);
        }

        .toggle-btn.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--primary-color);
            border-radius: 3px 3px 0 0;
        }

        .form {
            display: none;
        }

        .form.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .input-group {
            margin-bottom: 20px;
            position: relative;
        }

        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark-color);
            font-size: 0.85rem;
        }

        .input-group input, .input-group select {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
        }

        .input-group input:focus, .input-group select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(12, 91, 71, 0.1);
            outline: none;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 40px;
            color: var(--light-text);
            font-size: 1rem;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 40px;
            cursor: pointer;
            color: var(--light-text);
        }

        /* Dashboard Side Panel */
        .dashboard-panel {
            flex: 1;
            background: linear-gradient(rgba(12, 91, 71, 0.85), rgba(30, 58, 138, 0.85)), url('https://images.unsplash.com/photo-1542718610-a1d656d1884c?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .dashboard-panel h3 {
            font-family: 'Poppins', sans-serif;
            font-size: 1.3rem;
            margin-bottom: 15px;
            text-align: center;
        }

        .church-motto {
            font-style: italic;
            text-align: center;
            font-size: 1.2rem;
            margin-bottom: 30px;
            color: rgba(255, 255, 255, 0.9);
            padding: 10px;
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
        }

        /* Mobile Menu Toggle */
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.3rem;
            cursor: pointer;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .sidebar {
                position: fixed;
                left: 0;
                top: 80px;
                height: calc(100vh - 80px);
                transform: translateX(-100%);
                z-index: 999;
            }
            
            .sidebar.active {
                transform: translateX(0);
            }
            
            .menu-toggle {
                display: block;
            }
            
            .reports-grid {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            }
        }

        @media (max-width: 768px) {
            body{
                font-size: 0.85rem;
            }
            .sidebar{
                font-size:12px;
                width:200px;
                padding:0%;
            }
            .main-content {
                padding: 15px;
            }
            
            .stats-cards {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .reports-grid {
                grid-template-columns: 1fr;
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .auth-panel {
                flex-direction: column;
                max-width: 600px;
            }
            
            .dashboard-panel {
                order: -1;
                padding: 30px;
            }
            .church-brand{gap:5px;}
            .logo-icon{width:20px;height:20px;font-size:0.85rem;box-shadow:none;}
            .church-info h1{font-size: 1rem;}
            .church-info .subtitle{font-size: 0.8rem;}
            .user-avatar{width:20px;height:20px;font-size: 0.85rem;}
            .activity-header{font-size: 1rem;}
            .modal-header h3{font-size: 1rem;}
            .user-name{font-size: 9px;}
            .user-role{font-size: 0.5rem;}
            .logout-btn{padding:4px 7px;}
            .modal-close{font-size: 0.85rem;}
            .nav-section h3{font-size: 0.6rem;}
            .nav-links a{padding:10px 12px;}
            .auth-header-tagline{font-size: 0.85rem;}
            .stat-info h3{font-size: 1rem;}
            .stat-info p{font-size: 0.75rem;}
        }

        @media (max-width: 480px) {
            .stats-cards {
                grid-template-columns: 1fr;
            }
            
            .top-header {
                padding: 15px;
            }
            
            .church-info h1 {
                font-size: 1.2rem;
            }
            
            .user-profile {
                flex-direction: column;
                gap: 10px;
            }
            
            .logout-btn span {
                display: none;
            }
            
            .report-actions {
                flex-wrap: wrap;
            }
            body{line-height: 1.2;}
            .top-header{padding:7px 15px;}
            .church-brand{gap:5px;}
            .logo-icon{width:35pxx;height:35px;font-size:0.7rem;}
            .church-info h1{font-size:1rem;}
            .church-info .subtitle{font-size:0.45rem;}
            .user-profile{gap:10px;}
            .user-avatar{width:40px;height:40px;font-size:0.7rem;}
            .user-role{font-size: 0.6rem;}
            .action-btn { min-width: 120px; }
            .stat-icon{font-size:0.85rem;width:40px;height:40px;}
            .stat-info h3{font-size:1rem;}
            .stat-info p{font-size:0.65rem;}
            .report-header{padding:10px;}
            .report-title{font-size:0.65rem;}
            .report-badge{font-size:0.65rem;}
            .report-content{padding:10px;}
            .report-meta{font-size:0.65rem;}
            .report-stat{font-size:0.9rem;}
            .recent-activity{padding:10px;}
            .modal-header{font-size:1rem;}

            .nav-section{padding:0 10px;}
            .nav-section h3{font-size:0.65rem;}
            .page-header h2{font-size:1rem;}
            .university-badge{font-size: 0.75rem;}
            .auth-header-tagline{font-size: 0.75rem;padding:5px 10px;max-width:480px;}
            .dashboard-panel h3{font-size:1rem;}
            .church-motto{font-size: 1rem;;}

        }

        /* Add Report Modal specific styles */
.modal-body textarea {
    width: 100%;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s;
    font-family: 'Poppins', sans-serif;
    resize: vertical;
}

.modal-body textarea:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(12, 91, 71, 0.1);
    outline: none;
}

/* Admin Controls */
.admin-controls {
    display: flex;
    gap: 10px;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.report-card.admin {
    border: 2px solid var(--primary-color);
}

.admin-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.admin-actions button {
    padding: 8px 12px;
    font-size: 0.85rem;
}

/* Role-based access */
.admin-only {
    display: none;
}

body.admin .admin-only {
    display: block;
}

/* Edit mode */
.report-card.editing {
    background-color: rgba(212, 160, 23, 0.05);
}
    
        body {
            font-family: 'Poppins', sans-serif;
            color: #222;
            line-height: 1.6;
            background: linear-gradient(-45deg, #e3f2fd, #cfd8dc, #bbdefb, #90caf9);
            background-size: 400% 400%;
            transition: background 0.3s ease;
        }

        body.auth-page {
            background: linear-gradient(135deg, rgba(12, 91, 71, 0.05) 0%, rgba(30, 58, 138, 0.05) 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        /* Continue with all your CSS styles exactly as they were in the original */
        /* ... (All CSS from your original file) ... */

        /* Add Report Modal specific styles */
        .modal-body textarea {
            width: 100%;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
            resize: vertical;
        }

        .modal-body textarea:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(12, 91, 71, 0.1);
            outline: none;
        }

        /* Admin Controls */
        .admin-controls {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .report-card.admin {
            border: 2px solid var(--primary-color);
        }

        .admin-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .admin-actions button {
            padding: 8px 12px;
            font-size: 0.85rem;
        }

        /* Role-based access */
        .admin-only {
            display: none;
        }

        body.admin .admin-only {
            display: block;
        }

        /* Edit mode */
        .report-card.editing {
            background-color: rgba(212, 160, 23, 0.05);
        }

        /* Message styles */
        .message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            z-index: 3000;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            animation: slideIn 0.3s ease;
            max-width: 350px;
        }

        .message.success {
            background-color: var(--success-color);
            border-left: 5px solid #27ae60;
        }

        .message.error {
            background-color: var(--error-color);
            border-left: 5px solid #c0392b;
        }

        .message.warning {
            background-color: var(--warning-color);
            border-left: 5px solid #d35400;
        }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>
<body class="<?php echo isset($_SESSION['user_id']) ? (isAdmin() ? 'admin' : '') : 'auth-page'; ?>">
    
    <?php if (!isset($_SESSION['user_id'])): ?>
    <!-- AUTHENTICATION PAGE (When not logged in) -->
    <div class="auth-container">
        <div class="auth-header">
            <div class="campus-header">
                <div class="logo-container">
                    <div class="logo-icon">
                        <img src="image/kisii_sda_logo-removebg-preview.png" height="40" width="40">
                    </div>
                    <div class="university-badge">
                        <i class="fas fa-university"></i> Kisii University
                    </div>
                </div>
                
                <div class="church-info">
                    <h1>Seventh-day Adventist Church</h1>
                    <div class="subtitle">Kisii University Sda Church</div>
                    <div class="location">Main Campus • Sagini Hall</div>
                </div>
            </div>
            
            <p class="auth-header-tagline">
                <i class="fas fa-quote-left" style="color: var(--secondary-color); margin-right: 8px;"></i>
                Serving the spiritual needs of Kisii University community through Christ-centered fellowship and worship
                <i class="fas fa-quote-right" style="color: var(--secondary-color); margin-left: 8px;"></i>
            </p>
        </div>
        
        <!-- Display messages from PHP -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="message error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['success'])): ?>
            <div class="message success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        
        <div class="auth-panel">
            <div class="form-container">
                <div class="form-toggle">
                    <button class="toggle-btn active" id="login-toggle">Sign In</button>
                    <button class="toggle-btn" id="register-toggle">Register</button>
                </div>
                
                <!-- Login Form -->
                <form class="form active" id="login-form" method="POST" action="">
                    <input type="hidden" name="action" value="login">
                    <h2>Campus Portal Access</h2>
                    <p>Sign in to access church reports, events, and ministry resources</p>
                    
                    <div class="input-group">
                        <label for="login-identifier">Username or Email</label>
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" id="login-identifier" name="identifier" placeholder="Enter username or email" required>
                    </div>
                    
                    <div class="input-group">
                        <label for="login-password">Password</label>
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="login-password" name="password" placeholder="Enter your password" required>
                        <span class="password-toggle" id="login-password-toggle">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    
                    <div class="input-group" style="display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <input type="checkbox" id="remember" name="remember">
                            <label for="remember">Remember me</label>
                        </div>
                        <a href="#" style="color: var(--primary-color); text-decoration: none; font-weight: 600;">Forgot password?</a>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 20px;">
                        <i class="fas fa-sign-in-alt"></i> Sign In to Dashboard
                    </button>
                    
                    <div style="text-align: center; margin-top: 25px; color: var(--light-text); font-size: 0.9rem;">
                        By signing in, you agree to our <a href="#" style="color: var(--primary-color);">Church Covenant</a> and <a href="#" style="color: var(--primary-color);">Privacy Policy</a>
                    </div>
                </form>
                
                <!-- Registration Form -->
                <form class="form" id="register-form" method="POST" action="">
                    <input type="hidden" name="action" value="register">
                    <h2>Join Campus Ministry</h2>
                    <p>Register for full access to church reports and ministry tracking</p>
                    
                    <div class="input-group">
                        <label for="fullname">Full Name</label>
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" id="fullname" name="fullname" placeholder="Enter your full name" required>
                    </div>
                    
                    <div class="input-group">
                        <label for="reg-username">Username</label>
                        <i class="fas fa-at input-icon"></i>
                        <input type="text" id="reg-username" name="username" placeholder="Choose a username" required>
                    </div>
                    
                    <div class="input-group">
                        <label for="reg-email">Email Address</label>
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" id="reg-email" name="email" placeholder="Enter your email" required>
                    </div>
                    
                    <div class="input-group">
                        <label for="reg-phone">Phone Number</label>
                        <i class="fas fa-phone input-icon"></i>
                        <input type="tel" id="reg-phone" name="phone" placeholder="Enter phone number" required>
                    </div>
                    
                    <div class="input-group">
                        <label for="reg-role">Campus Role</label>
                        <i class="fas fa-user-tag input-icon"></i>
                        <select id="reg-role" name="role" required>
                            <option value="">Select your role</option>
                            <option value="student">Student</option>
                            <option value="staff">Staff</option>
                            <option value="faculty">Faculty</option>
                            <option value="alumni">Alumni</option>
                        </select>
                    </div>
                    
                    <div class="input-group">
                        <label for="reg-password">Create Password</label>
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="reg-password" name="password" placeholder="Create a strong password" required>
                        <span class="password-toggle" id="reg-password-toggle">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    
                    <div class="input-group">
                        <label for="reg-confirm-password">Confirm Password</label>
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="reg-confirm-password" name="confirm_password" placeholder="Re-enter password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">
                        <i class="fas fa-user-plus"></i> Create Account
                    </button>
                    
                    <div style="text-align: center; margin-top: 25px; color: var(--light-text); font-size: 0.9rem;">
                        By registering, you agree to our <a href="#" style="color: var(--primary-color);">Terms of Service</a> and <a href="#" style="color: var(--primary-color);">Privacy Policy</a>
                    </div>
                </form>
            </div>
            
            <div class="dashboard-panel">
                <h3>Welcome to Our Church Portal</h3>
                <div class="church-motto">"Nurturing faith, building community, serving the campus"</div>
                
                <div style="margin-top: 20px;">
                    <h4 style="margin-bottom: 15px; color: var(--accent-color);">Portal Features:</h4>
                    <ul style="list-style: none; padding-left: 0;">
                        <li style="margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-chart-line" style="color: var(--accent-color);"></i>
                            <span>View detailed church reports and analytics</span>
                        </li>
                        <li style="margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-file-pdf" style="color: var(--accent-color);"></i>
                            <span>Access PDF reports with built-in viewer</span>
                        </li>
                        <li style="margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-calendar-alt" style="color: var(--accent-color);"></i>
                            <span>Track church events and ministry activities</span>
                        </li>
                        <li style="margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-users" style="color: var(--accent-color);"></i>
                            <span>Connect with church community members</span>
                        </li>
                    </ul>
                </div>
                
                <div style="margin-top: 30px; padding: 15px; background-color: rgba(255, 255, 255, 0.1); border-radius: 8px;">
                    <h5 style="margin-bottom: 10px; color: var(--accent-color);">Demo Access:</h5>
                    <p style="font-size: 0.9rem; opacity: 0.9;">
                        For demonstration purposes, you can use:<br>
                        <strong>Username:</strong> admin<br>
                        <strong>Password:</strong> admin123
                    </p>
                </div>
            </div>
        </div>
        
        <div style="color: var(--light-text); text-align: center; font-size: 0.9rem; margin-top: 10px;">
            <i class="fas fa-university"></i> Kisii University SDA Church • Campus Ministry Office • &copy; 2025
        </div>
    </div>
    
    <?php else: ?>
    <!-- DASHBOARD (When logged in) -->
    <?php
    // Get user info from database
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    // Get reports from database
    $reports_sql = "SELECT r.*, u.fullname as uploader_name 
                   FROM reports r 
                   LEFT JOIN users u ON r.uploaded_by = u.id 
                   ORDER BY r.created_at DESC";
    $reports_stmt = $pdo->query($reports_sql);
    $reports = $reports_stmt->fetchAll();
    
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
                     ORDER BY a.created_at DESC LIMIT 5";
    $activity_stmt = $pdo->query($activity_sql);
    $activities = $activity_stmt->fetchAll();
    ?>
    
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
                <div class="user-name"><?php echo htmlspecialchars($user['fullname']); ?></div>
                <div class="user-role"><?php echo ucfirst($user['role']); ?></div>
            </div>
            <a href="?logout=true" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </header>

    <div class="main-container">
        <!-- Sidebar Navigation -->
        <nav class="sidebar" id="sidebar">
            <div class="nav-section">
                <h3>Dashboard</h3>
                <ul class="nav-links">
                    <li><a href="#" class="active" data-page="reports"><i class="fas fa-chart-line"></i> Reports Overview</a></li>
                    <li><a href="#" data-page="events"><i class="fas fa-calendar-alt"></i> Events Calendar</a></li>
                    <li><a href="#" data-page="ministry"><i class="fas fa-users"></i> Ministry Groups</a></li>
                    <li><a href="#" data-page="documents"><i class="fas fa-file-alt"></i> Document Library</a></li>
                </ul>
            </div>
            
            <div class="nav-section">
                <h3>Report Categories</h3>
                <ul class="nav-links">
                    <li><a href="#" class="category-filter" data-category="all"><i class="fas fa-list"></i> All Reports</a></li>
                    <li><a href="#" class="category-filter" data-category="financial"><i class="fas fa-money-bill-wave"></i> Financial Reports</a></li>
                    <li><a href="#" class="category-filter" data-category="attendance"><i class="fas fa-user-check"></i> Attendance Reports</a></li>
                    <li><a href="#" class="category-filter" data-category="ministry"><i class="fas fa-hands-praying"></i> Ministry Reports</a></li>
                    <li><a href="#" class="category-filter" data-category="events"><i class="fas fa-calendar-check"></i> Events Reports</a></li>
                    <li><a href="#" class="category-filter" data-category="youth"><i class="fas fa-child"></i> Youth & Pathfinders</a></li>
                </ul>
            </div>
            
            <div class="nav-section">
                <h3>Tools</h3>
                <ul class="nav-links">
                    <li><a href="#" id="exportReports"><i class="fas fa-download"></i> Export Reports</a></li>
                    <li><a href="#" id="notifications"><i class="fas fa-bell"></i> Notifications <span class="badge" style="background-color: var(--accent-color); color: white; padding: 2px 6px; border-radius: 10px; font-size: 0.7rem;">3</span></a></li>
                    <li><a href="#" data-page="settings"><i class="fas fa-cog"></i> Settings</a></li>
                    <li><a href="#" data-page="help"><i class="fas fa-question-circle"></i> Help & Support</a></li>
                </ul>
            </div>
            
            <div class="nav-section">
                <div class="stat-card" style="background-color: rgba(12, 91, 71, 0.05); border: none; margin: 0 15px;">
                    <div class="stat-icon membership">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_reports']; ?></h3>
                        <p>Active Reports</p>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="main-content" id="mainContent">
            <!-- Page Header -->
            <div class="page-header">
                <div>
                    <h2 id="pageTitle">Church Reports Dashboard</h2>
                    <p id="pageDescription">View, track, and follow up on all church ministry reports and activities</p>
                </div>
                <div class="header-actions">
                    <button class="btn btn-secondary" id="filterReports">
                        <i class="fas fa-filter"></i> Filter Reports
                    </button>
                    <?php if (isAdmin()): ?>
                    <button class="btn btn-primary" id="newReportBtn">
                        <i class="fas fa-plus"></i> New Report
                    </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-cards" id="statsCards">
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
                                <span><?php echo htmlspecialchars($report['total_amount']); ?> Total</span>
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
                            <button class="action-btn pdf view-pdf" 
                                    data-title="<?php echo htmlspecialchars($report['title']); ?>"
                                    data-url="uploads/<?php echo htmlspecialchars($report['pdf_filename']); ?>">
                                <i class="fas fa-file-pdf"></i> View PDF
                            </button>
                            <a href="download_report.php?id=<?php echo $report['id']; ?>" class="action-btn download-pdf">
                                <i class="fas fa-download"></i> Download
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php if (isAdmin()): ?>
                        <div class="admin-actions">
                            <button class="btn btn-secondary edit-report" data-id="<?php echo $report['id']; ?>" style="padding: 5px 10px; font-size: 0.8rem;">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button class="btn btn-secondary delete-report" data-id="<?php echo $report['id']; ?>" style="padding: 5px 10px; font-size: 0.8rem; background-color: var(--error-color);">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Recent Activity -->
            <div class="recent-activity">
                <div class="activity-header">
                    <h3>Recent Report Activity</h3>
                    <button class="btn btn-secondary" style="padding: 8px 15px;">
                        <i class="fas fa-history"></i> View All
                    </button>
                </div>
                <ul class="activity-list" id="activityList">
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

    <!-- PDF Viewer Modal -->
    <div class="modal" id="pdfModal">
        <div class="modal-content pdf-modal">
            <div class="modal-header">
                <h3 id="pdfTitle">PDF Viewer</h3>
                <div class="pdf-controls">
                    <button class="btn btn-secondary" id="prevPage">
                        <i class="fas fa-chevron-left"></i> Previous
                    </button>
                    <span class="page-info">Page: <span id="currentPage">1</span> of <span id="totalPages">1</span></span>
                    <button class="btn btn-secondary" id="nextPage">
                        Next <i class="fas fa-chevron-right"></i>
                    </button>
                    <button class="btn btn-secondary" id="zoomIn">
                        <i class="fas fa-search-plus"></i>
                    </button>
                    <button class="btn btn-secondary" id="zoomOut">
                        <i class="fas fa-search-minus"></i>
                    </button>
                    <button class="btn btn-primary" id="downloadPdf">
                        <i class="fas fa-download"></i> Download
                    </button>
                </div>
                <button class="modal-close" id="closePdfModal">&times;</button>
            </div>
            <div class="modal-body" style="padding: 0;">
                <div id="pdfContainer" style="position: relative; overflow: auto; height: 70vh;">
                    <div class="loading" id="pdfLoading">
                        <i class="fas fa-spinner"></i>
                        <p>Loading PDF document...</p>
                    </div>
                    <canvas id="pdfCanvas" style="display: none;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Detail Modal -->
    <div class="modal" id="reportModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Report Details</h3>
                <button class="modal-close" id="closeModal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="loading" id="reportLoading">
                    <i class="fas fa-spinner"></i>
                    <p>Loading report details...</p>
                </div>
                <div id="reportDetails" style="display: none;">
                    <!-- Filled by JavaScript -->
                </div>
            </div>
        </div>
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

    <?php endif; ?>

    <!-- Success/Error Messages Container -->
    <div id="messageContainer"></div>

    <script>
        // ========== APPLICATION STATE ==========
        let pdfDoc = null;
        let currentPdfPage = 1;
        let pageRendering = false;
        let pageNumPending = null;
        let pdfScale = 1.2;

        // ========== DOM ELEMENTS ==========
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const reportsGrid = document.getElementById('reportsGrid');
        const activityList = document.getElementById('activityList');
        const pdfModal = document.getElementById('pdfModal');
        const pdfCanvas = document.getElementById('pdfCanvas');
        const pdfTitle = document.getElementById('pdfTitle');
        const pdfLoading = document.getElementById('pdfLoading');
        const reportModal = document.getElementById('reportModal');
        const reportDetails = document.getElementById('reportDetails');
        const reportLoading = document.getElementById('reportLoading');
        const addReportModal = document.getElementById('addReportModal');
        const newReportBtn = document.getElementById('newReportBtn');
        const closeAddModal = document.getElementById('closeAddModal');
        const cancelAddReport = document.getElementById('cancelAddReport');
        const reportForm = document.getElementById('reportForm');

        // ========== FUNCTIONS ==========
        function showMessage(text, type = 'success') {
            const message = document.createElement('div');
            message.className = `message ${type}`;
            message.textContent = text;
            document.getElementById('messageContainer').appendChild(message);
            
            setTimeout(() => {
                message.style.animation = 'slideIn 0.3s ease reverse';
                setTimeout(() => message.remove(), 300);
            }, 5000);
        }

        // ========== PDF VIEWER FUNCTIONS ==========
        function viewPdf(title, url) {
            pdfTitle.textContent = title;
            pdfModal.classList.add('active');
            pdfLoading.style.display = 'flex';
            pdfCanvas.style.display = 'none';

            // Set download button
            document.getElementById('downloadPdf').onclick = () => {
                window.open(url, '_blank');
            };

            // Load PDF
            pdfjsLib.getDocument(url).promise.then(function(pdfDoc_) {
                pdfDoc = pdfDoc_;
                document.getElementById('totalPages').textContent = pdfDoc.numPages;
                currentPdfPage = 1;
                renderPdfPage(currentPdfPage);
            }).catch(function(error) {
                console.error('Error loading PDF:', error);
                pdfLoading.innerHTML = '<i class="fas fa-exclamation-triangle"></i><p>Error loading PDF. Please try downloading instead.</p>';
                showMessage('Could not load PDF. The file may be temporarily unavailable.', 'error');
            });
        }

        function renderPdfPage(num) {
            pageRendering = true;
            
            pdfDoc.getPage(num).then(function(page) {
                const viewport = page.getViewport({ scale: pdfScale });
                pdfCanvas.height = viewport.height;
                pdfCanvas.width = viewport.width;
                
                const renderContext = {
                    canvasContext: pdfCanvas.getContext('2d'),
                    viewport: viewport
                };
                
                const renderTask = page.render(renderContext);
                
                renderTask.promise.then(function() {
                    pdfLoading.style.display = 'none';
                    pdfCanvas.style.display = 'block';
                    pageRendering = false;
                    
                    if (pageNumPending !== null) {
                        renderPdfPage(pageNumPending);
                        pageNumPending = null;
                    }
                });
            });
            
            document.getElementById('currentPage').textContent = num;
        }

        function queueRenderPdfPage(num) {
            if (pageRendering) {
                pageNumPending = num;
            } else {
                renderPdfPage(num);
            }
        }

        // ========== REPORT DETAILS FUNCTIONS ==========
        function viewReportDetails(reportId) {
            // Get report details from the page
            const reportCard = document.querySelector(`.report-card[data-id="${reportId}"]`);
            if (!reportCard) return;

            const title = reportCard.querySelector('.report-title').textContent;
            const author = reportCard.querySelector('.report-author').textContent.replace(' ', '');
            const date = reportCard.querySelector('.report-meta div:nth-child(2)').textContent;
            const category = reportCard.dataset.category;
            const summary = reportCard.querySelector('.report-summary').textContent;
            const views = reportCard.querySelector('.report-stat:nth-child(3) span').textContent.split(' ')[0];
            const downloads = reportCard.querySelector('.report-stat:nth-child(4) span').textContent.split(' ')[0];
            
            reportModal.classList.add('active');
            reportLoading.style.display = 'flex';
            reportDetails.style.display = 'none';

            setTimeout(() => {
                reportLoading.style.display = 'none';
                reportDetails.style.display = 'block';
                
                document.getElementById('modalTitle').textContent = title;
                
                let detailsHtml = `
                    <div class="report-detail">
                        <h4>Report Summary</h4>
                        <p>${summary}</p>
                    </div>
                    
                    <div class="report-detail">
                        <h4>Report Information</h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-top: 10px;">
                            <div style="padding: 10px; background-color: rgba(12, 91, 71, 0.05); border-radius: 6px;">
                                <strong>Author:</strong><br>${author}
                            </div>
                            <div style="padding: 10px; background-color: rgba(12, 91, 71, 0.05); border-radius: 6px;">
                                <strong>Date:</strong><br>${date}
                            </div>
                            <div style="padding: 10px; background-color: rgba(12, 91, 71, 0.05); border-radius: 6px;">
                                <strong>Category:</strong><br>${category.charAt(0).toUpperCase() + category.slice(1)}
                            </div>
                            <div style="padding: 10px; background-color: rgba(12, 91, 71, 0.05); border-radius: 6px;">
                                <strong>Views:</strong><br>${views}
                            </div>
                            <div style="padding: 10px; background-color: rgba(12, 91, 71, 0.05); border-radius: 6px;">
                                <strong>Downloads:</strong><br>${downloads}
                            </div>
                        </div>
                    </div>`;

                const pdfUrl = reportCard.querySelector('.view-pdf')?.dataset.url;
                if (pdfUrl) {
                    detailsHtml += `
                        <div style="display: flex; gap: 15px; margin-top: 30px;">
                            <button class="btn btn-primary" style="flex: 1;" onclick="viewPdf('${title}', '${pdfUrl}')">
                                <i class="fas fa-file-pdf"></i> View PDF Report
                            </button>
                            <a href="${pdfUrl.replace('uploads/', 'download_report.php?id=' + reportId)}" class="btn btn-secondary" style="flex: 1;">
                                <i class="fas fa-download"></i> Download PDF
                            </a>
                        </div>`;
                }

                reportDetails.innerHTML = detailsHtml;
            }, 500);
        }

        // ========== AJAX REPORT UPLOAD ==========
        if (reportForm) {
            reportForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                
                // Show loading
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
                submitBtn.disabled = true;
                
                try {
                    const response = await fetch('upload_report.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        showMessage(result.message, 'success');
                        addReportModal.classList.remove('active');
                        reportForm.reset();
                        
                        // Reload page after 1.5 seconds to show new report
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showMessage(result.message, 'error');
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }
                } catch (error) {
                    showMessage('Network error: ' + error.message, 'error');
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            });
        }

        // ========== EVENT LISTENERS ==========
        <?php if (!isset($_SESSION['user_id'])): ?>
        // Login/Register Toggle
        const loginToggle = document.getElementById('login-toggle');
        const registerToggle = document.getElementById('register-toggle');
        const loginForm = document.getElementById('login-form');
        const registerForm = document.getElementById('register-form');

        if (loginToggle && registerToggle) {
            loginToggle.addEventListener('click', () => {
                loginToggle.classList.add('active');
                registerToggle.classList.remove('active');
                loginForm.classList.add('active');
                registerForm.classList.remove('active');
            });

            registerToggle.addEventListener('click', () => {
                registerToggle.classList.add('active');
                loginToggle.classList.remove('active');
                registerForm.classList.add('active');
                loginForm.classList.remove('active');
            });
        }

        // Password toggle visibility
        document.querySelectorAll('.password-toggle').forEach(toggle => {
            toggle.addEventListener('click', function() {
                const input = this.previousElementSibling;
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });
        <?php else: ?>
        // Dashboard functionality
        
        // Menu toggle for mobile
        if (menuToggle) {
            menuToggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
            });
        }

        // Close modals
        document.getElementById('closePdfModal').addEventListener('click', () => {
            pdfModal.classList.remove('active');
        });

        document.getElementById('closeModal').addEventListener('click', () => {
            reportModal.classList.remove('active');
        });

        if (closeAddModal) {
            closeAddModal.addEventListener('click', () => {
                addReportModal.classList.remove('active');
                reportForm.reset();
            });
        }

        if (cancelAddReport) {
            cancelAddReport.addEventListener('click', () => {
                addReportModal.classList.remove('active');
                reportForm.reset();
            });
        }

        // Close modal when clicking outside
        addReportModal.addEventListener('click', (e) => {
            if (e.target === addReportModal) {
                addReportModal.classList.remove('active');
                reportForm.reset();
            }
        });

        // PDF navigation
        document.getElementById('prevPage').addEventListener('click', () => {
            if (currentPdfPage <= 1) return;
            currentPdfPage--;
            queueRenderPdfPage(currentPdfPage);
        });

        document.getElementById('nextPage').addEventListener('click', () => {
            if (currentPdfPage >= pdfDoc.numPages) return;
            currentPdfPage++;
            queueRenderPdfPage(currentPdfPage);
        });

        document.getElementById('zoomIn').addEventListener('click', () => {
            pdfScale += 0.1;
            renderPdfPage(currentPdfPage);
        });

        document.getElementById('zoomOut').addEventListener('click', () => {
            if (pdfScale <= 0.5) return;
            pdfScale -= 0.1;
            renderPdfPage(currentPdfPage);
        });

        // Close modals when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('active');
                }
            });
        });

        // Filter reports by category
        document.querySelectorAll('.category-filter').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const category = link.dataset.category;
                
                // Update active nav
                document.querySelectorAll('.nav-links a').forEach(a => a.classList.remove('active'));
                link.classList.add('active');
                
                // Filter reports
                if (category === 'all') {
                    document.querySelectorAll('.report-card').forEach(card => {
                        card.style.display = 'block';
                    });
                } else {
                    document.querySelectorAll('.report-card').forEach(card => {
                        if (card.dataset.category === category) {
                            card.style.display = 'block';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                }
            });
        });

        // New Report button
        if (newReportBtn) {
            newReportBtn.addEventListener('click', () => {
                document.getElementById('reportDate').valueAsDate = new Date();
                addReportModal.classList.add('active');
            });
        }

        // View report details
        document.querySelectorAll('.view-report').forEach(btn => {
            btn.addEventListener('click', () => viewReportDetails(btn.dataset.id));
        });

        // View PDF
        document.querySelectorAll('.view-pdf').forEach(btn => {
            btn.addEventListener('click', () => viewPdf(btn.dataset.title, btn.dataset.url));
        });

        // Edit report (admin only)
        document.querySelectorAll('.edit-report').forEach(btn => {
            btn.addEventListener('click', function() {
                showMessage('Edit functionality coming soon!', 'warning');
            });
        });

        // Delete report (admin only)
        document.querySelectorAll('.delete-report').forEach(btn => {
            btn.addEventListener('click', function() {
                const reportId = this.dataset.id;
                if (confirm('Are you sure you want to delete this report? This action cannot be undone.')) {
                    fetch('delete_report.php?id=' + reportId)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                showMessage(data.message, 'success');
                                // Remove report from DOM
                                document.querySelector(`.report-card[data-id="${reportId}"]`).remove();
                            } else {
                                showMessage(data.message, 'error');
                            }
                        })
                        .catch(error => {
                            showMessage('Network error: ' + error.message, 'error');
                        });
                }
            });
        });

        // Export Reports
        document.getElementById('exportReports').addEventListener('click', () => {
            showMessage('Exporting all reports as PDF...', 'success');
        });

        // Filter Reports button
        document.getElementById('filterReports').addEventListener('click', () => {
            showMessage('Filter functionality would open filtering options.', 'warning');
        });

        <?php endif; ?>
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