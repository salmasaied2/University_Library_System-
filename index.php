<?php
// Start the session to check if the user is already logged in
session_start();

// If the user is already logged in, redirect them immediately to their respective portal
if (isset($_SESSION['user_type'])) {
    if ($_SESSION['user_type'] === 'admin') {
        header('Location: admin/index.php');
    } else {
        header('Location: student/index.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to University Library</title>
    <link rel="stylesheet" 
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        .welcome-container {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #e9ecef;
            flex-direction: column;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="welcome-container">
    <h1 class="display-4 text-primary mb-4">University Library System</h1>
    <p class="lead mb-5">Access the student portal or browse the public catalog.</p>

    <div class="d-grid gap-2 col-6 mx-auto">
        <a href="login.php" class="btn btn-primary btn-lg">Log In (Student/Admin Portal)</a>
        <a href="catalog.php" class="btn btn-secondary btn-lg">View Catalog as Guest</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>