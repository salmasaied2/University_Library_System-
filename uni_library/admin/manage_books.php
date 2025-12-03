<?php
// Set the page title first
$page_title = "Manage Books";

// 1. Include the top template (handles session, auth check, and DB connection)
include 'layout_admin_top.php'; 

// --- PHP CRUD LOGIC START ---

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    try {
        if ($action === 'add') {
            // C - CREATE: Add a new book
            $sql = "INSERT INTO books (title, author, isbn, publication_year, category, total_copies, available_copies) 
                    VALUES (:title, :author, :isbn, :pub_year, :category, :total, :available)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':title' => $_POST['title'],
                ':author' => $_POST['author'],
                ':isbn' => $_POST['isbn'],
                ':pub_year' => $_POST['publication_year'],
                ':category' => $_POST['category'],
                ':total' => $_POST['total_copies'],
                ':available' => $_POST['total_copies'] 
            ]);
            $_SESSION['message'] = "Book added successfully.";

        } elseif ($action === 'delete') {
            // D - DELETE: Remove a book record
            $book_id = $_POST['book_id'];
            
            // Safety Check: Prevent deletion if book has active loans
            $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM loans WHERE book_id = :id AND return_date IS NULL");
            $stmt_check->execute([':id' => $book_id]);
            if ($stmt_check->fetchColumn() > 0) {
                 $_SESSION['message'] = "Error: Cannot delete book with active loans.";
            } else {
                 $stmt = $pdo->prepare("DELETE FROM books WHERE book_id = :id");
                 $stmt->execute([':id' => $book_id]);
                 $_SESSION['message'] = "Book deleted successfully.";
            }

        } elseif ($action === 'update') {
            // U - UPDATE: Modify an existing book record
            $sql = "UPDATE books SET title = :title, author = :author, isbn = :isbn, publication_year = :pub_year, category = :category, total_copies = :total, available_copies = :available WHERE book_id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':title' => $_POST['title'],
                ':author' => $_POST['author'],
                ':isbn' => $_POST['isbn'],
                ':pub_year' => $_POST['publication_year'],
                ':category' => $_POST['category'],
                ':total' => $_POST['total_copies'],
                ':available' => $_POST['available_copies'],
                ':id' => $_POST['book_id']
            ]);
            $_SESSION['message'] = "Book updated successfully.";
        }

    } catch (PDOException $e) {
        $_SESSION['message'] = "Database error occurred during the operation.";
    }
    
    // Post-Redirect-Get pattern to prevent form resubmission
    header('Location: manage_books.php');
    exit;
}

// --- PHP CRUD LOGIC END ---

// --- R - READ Operation: Fetch all books for display ---
try {
    $stmt = $pdo->query("SELECT * FROM books ORDER BY book_id DESC");
    $books = $stmt->fetchAll();
} catch (PDOException $e) {
    $db_error = "Error fetching books: " . $e->getMessage();
    $books = [];
}
?>

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-info" role="alert">
        <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
    </div>
<?php endif; ?>

<?php if (isset($db_error)): ?>
    <div class="alert alert-danger" role="alert">
        <?php echo $db_error; ?>
    </div>
<?php endif; ?>

<button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addBookModal">
    Add New Book
</button>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Author</th>
                <th>ISBN</th>
                <th>Total Copies</th>
                <th>Available</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($books as $book): ?>
            <tr>
                <td><?php echo htmlspecialchars($book['book_id']); ?></td>
                <td><?php echo htmlspecialchars($book['title']); ?></td>
                <td><?php echo htmlspecialchars($book['author']); ?></td>
                <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                <td><?php echo htmlspecialchars($book['total_copies']); ?></td>
                <td><?php echo htmlspecialchars($book['available_copies']); ?></td>
                <td>
                    <button type="button" class="btn btn-sm btn-info text-white" 
                            data-bs-toggle="modal" 
                            data-bs-target="#editBookModal_<?php echo $book['book_id']; ?>">
                        Edit
                    </button>
                    <form method="POST" action="manage_books.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this book? This action cannot be undone.');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            <div class="modal fade" id="editBookModal_<?php echo $book['book_id']; ?>" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form method="POST" action="manage_books.php">
                      <input type="hidden" name="action" value="update">
                      <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                      <div class="modal-header">
                        <h5 class="modal-title">Edit Book: <?php echo htmlspecialchars($book['title']); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                          <div class="mb-3"><label class="form-label">Title</label><input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($book['title']); ?>" required></div>
                          <div class="mb-3"><label class="form-label">Author</label><input type="text" name="author" class="form-control" value="<?php echo htmlspecialchars($book['author']); ?>" required></div>
                          <div class="mb-3"><label class="form-label">ISBN</label><input type="text" name="isbn" class="form-control" value="<?php echo htmlspecialchars($book['isbn']); ?>"></div>
                          <div class="mb-3"><label class="form-label">Publication Year</label><input type="number" name="publication_year" class="form-control" min="1500" max="<?php echo date('Y'); ?>" value="<?php echo htmlspecialchars($book['publication_year']); ?>" required></div>
                          <div class="mb-3"><label class="form-label">Category</label><input type="text" name="category" class="form-control" value="<?php echo htmlspecialchars($book['category']); ?>"></div>
                          <div class="mb-3"><label class="form-label">Total Copies</label><input type="number" name="total_copies" class="form-control" min="1" value="<?php echo htmlspecialchars($book['total_copies']); ?>" required></div>
                          <div class="mb-3"><label class="form-label">Available Copies</label><input type="number" name="available_copies" class="form-control" value="<?php echo htmlspecialchars($book['available_copies']); ?>" required></div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Update Book</button>
                      </div>
                  </form>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($books)): ?>
            <tr>
                <td colspan="7" class="text-center">No books found in the inventory.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="addBookModal" tabindex="-1" aria-labelledby="addBookModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="manage_books.php">
          <input type="hidden" name="action" value="add">
          <div class="modal-header">
            <h5 class="modal-title" id="addBookModalLabel">Add New Book</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <div class="mb-3"><label class="form-label">Title</label><input type="text" name="title" class="form-control" required></div>
              <div class="mb-3"><label class="form-label">Author</label><input type="text" name="author" class="form-control" required></div>
              <div class="mb-3"><label class="form-label">ISBN</label><input type="text" name="isbn" class="form-control"></div>
              <div class="mb-3"><label class="form-label">Publication Year</label><input type="number" name="publication_year" class="form-control" min="1500" max="<?php echo date('Y'); ?>" required></div>
              <div class="mb-3"><label class="form-label">Category</label><input type="text" name="category" class="form-control"></div>
              <div class="mb-3"><label class="form-label">Total Copies</label><input type="number" name="total_copies" class="form-control" min="1" required></div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Save Book</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php 
// 6. Include the bottom template (closes tags)
include 'layout_admin_bottom.php'; 
?>