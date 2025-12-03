<?php
// Start the session
session_start();

// Include the database connection file.
include '../config/db_connect.php'; 

// --- ACCESS CONTROL AND AUTHORIZATION ---
// Check if user is logged in AND is a Student
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'student') {
    // If not authorized, redirect to the login page
    header('Location: ../login.php'); 
    exit;
}

$user_id = $_SESSION['user_id'];
$active_loans = [];
$db_error = null;

try {
    // --- 1. Fetch Student's Active Loans ---
    $sql_loans = "
        SELECT 
            l.loan_date, l.due_date, 
            b.title as book_title, b.author
        FROM loans l
        JOIN books b ON l.book_id = b.book_id
        WHERE l.user_id = :user_id AND l.return_date IS NULL 
        ORDER BY l.due_date ASC";
    
    $stmt_loans = $pdo->prepare($sql_loans);
    // Execute query using the student's user ID
    $stmt_loans->execute([':user_id' => $user_id]); 
    $active_loans = $stmt_loans->fetchAll();

} catch (PDOException $e) {
    $db_error = "Error fetching data: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal - University Library</title>
    <link rel="stylesheet" 
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-info">
    <div class="container-fluid container">
        <a class="navbar-brand" href="index.php">Student Library Portal</a>
        <span class="navbar-text ms-auto me-2 text-white">
            Welcome, <?php echo $_SESSION['username']; ?>
        </span>
        <a class="btn btn-secondary btn-sm me-2" href="../catalog.php">View Catalog</a>
        <a class="btn btn-danger btn-sm" href="../logout.php">Logout</a>
    </div>
</nav>

<div class="container mt-4">
    <h1 class="mb-4">My Library Services</h1>
    
    <?php if (isset($db_error)): ?>
        <div class="alert alert-danger" role="alert"><?php echo $db_error; ?></div>
    <?php endif; ?>

    <div class="card mb-5">
        <div class="card-header bg-primary text-white">
            <h4>My Active Loans (Books Currently Checked Out)</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Loan Date</th>
                            <th>Due Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($active_loans)): ?>
                        <tr>
                            <td colspan="5" class="text-center">You currently have no books checked out.</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($active_loans as $loan): ?>
                            <?php 
                                $status_class = 'text-success';
                                $status_text = 'On Time';
                                // Check if the due date is past the current date
                                if (strtotime($loan['due_date']) < time()) {
                                    $status_class = 'text-danger fw-bold';
                                    $status_text = 'OVERDUE! Please return immediately.';
                                }
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($loan['book_title']); ?></td>
                                <td><?php echo htmlspecialchars($loan['author']); ?></td>
                                <td><?php echo htmlspecialchars($loan['loan_date']); ?></td>
                                <td><?php echo htmlspecialchars($loan['due_date']); ?></td>
                                <td class="<?php echo $status_class; ?>"><?php echo $status_text; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>