<?php
// Start the session for user authentication
session_start(); 

// Include the database connection file. 
// This file contains the $pdo object for database interaction.
include 'config/db_connect.php'; 

$error = null; // Initialize error variable for displaying messages

// Check if the form was submitted using POST method
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 1. Get and sanitize user input
    $username = htmlspecialchars($_POST['username']);
    $password = $_POST['password'];

    // 2. Prepare the SQL query to fetch user data by username
    // Using PDO prepared statement for security
    $sql = "SELECT id, username, password, user_type FROM users WHERE username = :username";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    // 3. Check if user exists and password is correct using password_verify()
    // password_verify checks the entered password against the hashed password in the DB
    if ($user && $password === $user['password']) { 

        // Authentication successful: Store user data in session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_type'] = $user['user_type'];

        // 4. Redirect based on user type
        if ($user['user_type'] === 'admin') {
            header('Location: admin/index.php'); // Redirect to Admin Dashboard
        } else {
            header('Location: student/index.php'); // Redirect to Student Portal
        }
        exit;
    } else {
        // Authentication failed: Set error message
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - University Library System</title>
    <link rel="stylesheet" 
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa; /* Light gray background */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh; /* Full viewport height */
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            padding: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            background-color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h3 class="text-center mb-4 text-primary">Library System Login</h3>
        
        <form method="POST" action="login.php">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Log In</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>