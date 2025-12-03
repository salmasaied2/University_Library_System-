<?php
// Set the page title
$page_title = "Manage Loans";

// Include the template top (handles session, auth check, and DB connection)
include 'layout_admin_top.php'; 

// Initialize status message
$status_message = null;

// --- LOAN CRUD LOGIC START ---

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    try {
        if ($action === 'checkout') {
            // C - CREATE: Register a new loan
            $book_id = $_POST['book_id'];
            $user_id = $_POST['user_id'];
            $loan_date = date('Y-m-d');
            // Calculate due date (e.g., 7 days from loan date)
            $due_date = date('Y-m-d', strtotime($loan_date . ' + 7 days')); 

            // 1. Check if the book has available copies
            $stmt_check = $pdo->prepare("SELECT available_copies FROM books WHERE book_id = :id");
            $stmt_check->execute([':id' => $book_id]);
            $available = $stmt_check->fetchColumn();

            if ($available > 0) {
                // 2. Insert the loan record
                $sql_insert = "INSERT INTO loans (book_id, user_id, loan_date, due_date) VALUES (:book_id, :user_id, :loan_date, :due_date)";
                $stmt_insert = $pdo->prepare($sql_insert);
                $stmt_insert->execute([':book_id' => $book_id, ':user_id' => $user_id, ':loan_date' => $loan_date, ':due_date' => $due_date]);

                // 3. U - Update: Decrement available copies in the books table
                $sql_update_book = "UPDATE books SET available_copies = available_copies - 1 WHERE book_id = :id";
                $stmt_update_book = $pdo->prepare($sql_update_book);
                $stmt_update_book->execute([':id' => $book_id]);

                $_SESSION['message'] = "Book successfully checked out. Due date: " . $due_date;
            } else {
                $_SESSION['message'] = "Error: Book is currently unavailable (0 copies left).";
            }

        } elseif ($action === 'checkin') {
            // U - UPDATE: Mark a loan as returned
            $loan_id = $_POST['loan_id'];
            $book_id = $_POST['book_id'];
            $return_date = date('Y-m-d');

            // 1. Update the loan record with the return date
            $sql_update_loan = "UPDATE loans SET return_date = :return_date WHERE loan_id = :id";
            $stmt_update_loan = $pdo->prepare($sql_update_loan);
            $stmt_update_loan->execute([':return_date' => $return_date, ':id' => $loan_id]);

            // 2. U - Update: Increment available copies in the books table
            $sql_update_book = "UPDATE books SET available_copies = available_copies + 1 WHERE book_id = :id";
            $stmt_update_book = $pdo->prepare($sql_update_book);
            $stmt_update_book->execute([':id' => $book_id]);

            $_SESSION['message'] = "Book successfully checked in.";
        }

    } catch (PDOException $e) {
        $_SESSION['message'] = "Database error: " . $e->getMessage();
    }
    
    // Post-Redirect-Get pattern
    header('Location: manage_loans.php');
    exit;
}

// --- LOAN CRUD LOGIC END ---

// --- R - READ Operation: Fetch current active loans ---
try {
    // Join three tables to get full details: Loan, User (for student name), Book (for title)
    $sql_active_loans = "
        SELECT 
            l.loan_id, l.loan_date, l.due_date, l.book_id, 
            u.name as student_name, 
            b.title as book_title
        FROM loans l
        JOIN users u ON l.user_id = u.id
        JOIN books b ON l.book_id = b.book_id
        WHERE l.return_date IS NULL 
        ORDER BY l.due_date ASC";
    
    $stmt_loans = $pdo->query($sql_active_loans);
    $active_loans = $stmt_loans->fetchAll();

    // Fetch all books and students for the Checkout Modal dropdowns
    $all_books = $pdo->query("SELECT book_id, title, available_copies FROM books WHERE available_copies > 0 ORDER BY title ASC")->fetchAll();
    $all_students = $pdo->query("SELECT id, name, username FROM users WHERE user_type = 'student' ORDER BY name ASC")->fetchAll();

} catch (PDOException $e) {
    $db_error = "Error fetching data: " . $e->getMessage();
    $active_loans = [];
    $all_books = [];
    $all_students = [];
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

<button type="button" class="btn btn-success mb-3" data-bs-toggle="modal" data-bs-target="#checkoutModal">
    Check Out Book
</button>

<h4>Active Loans</h4>
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>Loan ID</th>
                <th>Student</th>
                <th>Book Title</th>
                <th>Loan Date</th>
                <th>Due Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($active_loans as $loan): ?>
            <?php 
                $status_class = 'text-success';
                $status_text = 'On Time';
                // Check if the due date is past the current date
                if (strtotime($loan['due_date']) < time()) {
                    $status_class = 'text-danger fw-bold';
                    $status_text = 'OVERDUE';
                }
            ?>
            <tr>
                <td><?php echo htmlspecialchars($loan['loan_id']); ?></td>
                <td><?php echo htmlspecialchars($loan['student_name']); ?></td>
                <td><?php echo htmlspecialchars($loan['book_title']); ?></td>
                <td><?php echo htmlspecialchars($loan['loan_date']); ?></td>
                <td><?php echo htmlspecialchars($loan['due_date']); ?></td>
                <td class="<?php echo $status_class; ?>"><?php echo $status_text; ?></td>
                <td>
                    <form method="POST" action="manage_loans.php" style="display:inline;" onsubmit="return confirm('Confirm book check-in?');">
                        <input type="hidden" name="action" value="checkin">
                        <input type="hidden" name="loan_id" value="<?php echo $loan['loan_id']; ?>">
                        <input type="hidden" name="book_id" value="<?php echo $loan['book_id']; ?>">
                        <button type="submit" class="btn btn-sm btn-info text-white">Check In</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($active_loans)): ?>
            <tr>
                <td colspan="7" class="text-center">No active loans found.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="manage_loans.php">
          <input type="hidden" name="action" value="checkout">
          <div class="modal-header">
            <h5 class="modal-title" id="checkoutModalLabel">Check Out New Book</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <div class="mb-3">
                  <label class="form-label">Student (User)</label>
                  <select name="user_id" class="form-select" required>
                      <option value="">Select Student</option>
                      <?php foreach ($all_students as $student): ?>
                          <option value="<?php echo $student['id']; ?>">
                              <?php echo htmlspecialchars($student['name']) . " (" . htmlspecialchars($student['username']) . ")"; ?>
                          </option>
                      <?php endforeach; ?>
                  </select>
              </div>
              <div class="mb-3">
                  <label class="form-label">Book to Check Out</label>
                  <select name="book_id" class="form-select" required>
                      <option value="">Select Book</option>
                      <?php foreach ($all_books as $book): ?>
                          <option value="<?php echo $book['book_id']; ?>">
                              <?php echo htmlspecialchars($book['title']) . " (Available: " . htmlspecialchars($book['available_copies']) . ")"; ?>
                          </option>
                      <?php endforeach; ?>
                  </select>
              </div>
              <p class="text-muted small">Note: Loan period is automatically 7 days.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success">Confirm Check Out</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php 
// Include the bottom template
include 'layout_admin_bottom.php'; 
?>