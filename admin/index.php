<?php
// Set the page title first, so the template can use it
$page_title = "Dashboard";

// 1. Include the top template (handles session, auth check, and DB connection)
include 'layout_admin_top.php'; 

// Note: $pdo object is now available from layout_admin_top.php

// --- DASHBOARD LOGIC (R - Read Operations) ---
// Fetch summary statistics
$stmt_books = $pdo->query("SELECT COUNT(*) FROM books");
$total_books = $stmt_books->fetchColumn();

$stmt_users = $pdo->query("SELECT COUNT(*) FROM users");
$total_users = $stmt_users->fetchColumn();

$stmt_loans = $pdo->query("SELECT COUNT(*) FROM loans WHERE return_date IS NULL");
$total_loans = $stmt_loans->fetchColumn();
?>

<div class="row">
    
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-primary">
            <div class="card-header">Total Books</div>
            <div class="card-body">
                <h4 class="card-title"><?php echo $total_books; ?></h4>
                <p class="card-text">All inventory records.</p>
            </div>
            <div class="card-footer"><a href="manage_books.php" class="text-white text-decoration-none">View Details</a></div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="card text-white bg-warning">
            <div class="card-header">Currently Loaned</div>
            <div class="card-body">
                <h4 class="card-title"><?php echo $total_loans; ?></h4>
                <p class="card-text">Books not yet returned.</p>
            </div>
            <div class="card-footer"><a href="manage_loans.php" class="text-white text-decoration-none">Manage Loans</a></div>
        </div>
    </div>
    
    <div class="col-md-4 mb-3">
        <div class="card text-white bg-success">
            <div class="card-header">Total Users</div>
            <div class="card-body">
                <h4 class="card-title"><?php echo $total_users; ?></h4>
                <p class="card-text">Total students and admins.</p>
            </div>
            <div class="card-footer"><a href="manage_users.php" class="text-white text-decoration-none">View Details</a></div>
        </div>
    </div>

</div>

<?php 
// 2. Include the bottom template (closes tags)
include 'layout_admin_bottom.php'; 
?>