<?php
// Start the session
session_start(); 

// Include the database connection file. Use '..' to go up one level from 'admin/'
include '../config/db_connect.php'; 

// --- ACCESS CONTROL AND AUTHORIZATION ---
// Check if user is logged in AND is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    // If not authorized, redirect to the login page
    header('Location: ../login.php'); 
    exit;
}
// Note: $page_title must be set by the page including this template (e.g., manage_books.php)
// Use $page_title ?? 'Dashboard' to provide a fallback title
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?php echo $page_title ?? 'Dashboard'; ?></title>
    <link rel="stylesheet" 
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        /* 1. Wrapper to push content down from the fixed Navbar */
        .main-content-wrapper {
            padding-top: 56px; /* Space for the fixed-top Navbar */
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: 250px;
            /* FIX: Re-adding padding to push the sidebar links down from the Navbar */
            padding-top: 56px; 
            background-color: #343a40; 
            color: #f8f9fa;
        }
        .content {
            margin-left: 250px; 
            padding: 20px;
        }
        .sidebar a {
            color: #adb5bd;
            padding: 10px 15px;
            display: block;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: #495057;
            color: #ffffff;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand me-auto" href="index.php">Library Admin Panel</a>
        <span class="navbar-text ms-auto me-2">
            Welcome, <?php echo $_SESSION['username']; ?>
        </span>
        <a class="btn btn-danger btn-sm" href="../logout.php">Logout</a>
    </div>
</nav>

<div class="sidebar">
    <ul class="nav flex-column mt-3">
        <li class="nav-item">
            <a class="nav-link <?php echo ($page_title == 'Dashboard' ? 'active' : ''); ?>" href="index.php">Dashboard</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($page_title == 'Manage Books' ? 'active' : ''); ?>" href="manage_books.php">Manage Books</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($page_title == 'Manage Loans' ? 'active' : ''); ?>" href="manage_loans.php">Manage Loans</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo ($page_title == 'Manage Users' ? 'active' : ''); ?>" href="manage_users.php">Manage Users</a>
        </li>
    </ul>
</div>

<div class="main-content-wrapper"> 
    
    <div class="content">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2"><?php echo $page_title ?? 'Dashboard'; ?></h1>
        </div>
