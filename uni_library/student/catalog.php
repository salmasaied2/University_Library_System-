<?php
// Start the session to check user status (Crucial for showing Log In/Log Out)
session_start();

// Include the database connection file.
include 'config/db_connect.php'; 

$db_error = null;
$books = [];
$is_logged_in = isset($_SESSION['user_type']);

try {
    // --- 1. Fetch ALL Books (R - Read Operation) ---
    // Removed the search logic to display the full inventory automatically
    $sql_books = "
        SELECT book_id, title, author, isbn, category, available_copies 
        FROM books 
        ORDER BY title ASC";
    
    // Use query() for simple selection (no variables to bind)
    $stmt_books = $pdo->query($sql_books); 
    $books = $stmt_books->fetchAll();

} catch (PDOException $e) {
    $db_error = "Error fetching data: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Catalog</title>
    <link rel="stylesheet" 
          href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid container">
        <a class="navbar-brand" href="catalog.php">Library Catalog (Public View)</a>
        
        <?php if ($is_logged_in): ?>
            <a class="btn btn-warning btn-sm me-2" href="<?php echo $_SESSION['user_type'] === 'admin' ? 'admin/index.php' : 'student/index.php'; ?>">Go to Portal</a>
            <a class="btn btn-danger btn-sm" href="logout.php">Logout</a>
        <?php else: ?>
            <a class="btn btn-success btn-sm" href="login.php">Log In</a>
        <?php endif; ?>
    </div>
</nav>

<div class="container mt-4">
    <h1 class="mb-4">Complete Library Inventory</h1>
    
    <?php if (isset($db_error)): ?>
        <div class="alert alert-danger" role="alert"><?php echo $db_error; ?></div>
    <?php endif; ?>

    <h4>All Available Books</h4>
    
    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>ISBN</th>
                    <th>Availability</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($books)): ?>
                <tr>
                    <td colspan="5" class="text-center">No books found in the inventory.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($books as $book): ?>
                    <?php 
                        $availability_class = ($book['available_copies'] > 0) ? 'bg-success text-white' : 'bg-danger text-white';
                        $availability_text = ($book['available_copies'] > 0) ? 'Available (' . $book['available_copies'] . ' copies)' : 'Unavailable';
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($book['title']); ?></td>
                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                        <td><?php echo htmlspecialchars($book['category']); ?></td>
                        <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                        <td class="<?php echo $availability_class; ?> text-center"><?php echo $availability_text; ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>