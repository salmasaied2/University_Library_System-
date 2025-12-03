<?php
// Set the page title
$page_title = "Manage Users";

// Include the template top (handles session, auth check, and DB connection)
include 'layout_admin_top.php'; 

// Initialize status message
$status_message = null;

// --- USER CRUD LOGIC START ---

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    try {
        if ($action === 'add') {
            // C - CREATE: Add a new user (Student or Admin)
            $password_hash = $_POST['password'];
            
            $sql = "INSERT INTO users (username, password, user_type, name, email) 
                    VALUES (:username, :password, :user_type, :name, :email)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':username' => $_POST['username'],
                ':password' => $password_hash,
                ':user_type' => $_POST['user_type'],
                ':name' => $_POST['name'],
                ':email' => $_POST['email']
            ]);
            $_SESSION['message'] = "User '{$_POST['username']}' added successfully.";

        } elseif ($action === 'delete') {
            // D - DELETE: Remove a user record
            $user_id = $_POST['user_id'];
            
            // Safety Check 1: Prevent an admin from deleting themselves
            if ($user_id == $_SESSION['user_id']) {
                $_SESSION['message'] = "Error: Cannot delete your own active admin account.";
            } else {
                 // Safety Check 2: Prevent deletion if user has active loans
                $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM loans WHERE user_id = :id AND return_date IS NULL");
                $stmt_check->execute([':id' => $user_id]);
                if ($stmt_check->fetchColumn() > 0) {
                    $_SESSION['message'] = "Error: Cannot delete user with active loans. They must return their books first.";
                } else {
                    // Perform deletion
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
                    $stmt->execute([':id' => $user_id]);
                    $_SESSION['message'] = "User deleted successfully.";
                }
            }

        } elseif ($action === 'update') {
            // U - UPDATE: Modify an existing user record
            $sql = "UPDATE users SET username = :username, user_type = :user_type, name = :name, email = :email ";
            
            // Check if password field was provided/changed
            if (!empty($_POST['password'])) {
                //$password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $password_hash = $_POST['password'];
                $sql .= ", password = :password ";
            }
            
            $sql .= "WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            
            $params = [
                ':username' => $_POST['username'],
                ':user_type' => $_POST['user_type'],
                ':name' => $_POST['name'],
                ':email' => $_POST['email'],
                ':id' => $_POST['user_id']
            ];
            
            if (!empty($_POST['password'])) {
                $params[':password'] = $password_hash;
            }
            
            $stmt->execute($params);
            $_SESSION['message'] = "User updated successfully.";
        }

    } catch (PDOException $e) {
        $_SESSION['message'] = "Database error: " . $e->getMessage();
    }
    
    // Post-Redirect-Get pattern
    header('Location: manage_users.php');
    exit;
}

// --- USER CRUD LOGIC END ---

// --- R - READ Operation: Fetch all users for display ---
try {
    $stmt_users = $pdo->query("SELECT id, username, user_type, name, email FROM users ORDER BY user_type DESC, name ASC");
    $all_users = $stmt_users->fetchAll();

} catch (PDOException $e) {
    $db_error = "Error fetching users: " . $e->getMessage();
    $all_users = [];
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

<button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addUserModal">
    Add New User
</button>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Username</th>
                <th>Email</th>
                <th>User Type</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($all_users as $user): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['id']); ?></td>
                <td><?php echo htmlspecialchars($user['name']); ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo htmlspecialchars($user['email']); ?></td>
                <td><?php echo htmlspecialchars(ucfirst($user['user_type'])); ?></td>
                <td>
                    <button type="button" class="btn btn-sm btn-info text-white" 
                            data-bs-toggle="modal" 
                            data-bs-target="#editUserModal_<?php echo $user['id']; ?>">
                        Edit
                    </button>
                    <form method="POST" action="manage_users.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
            
            <div class="modal fade" id="editUserModal_<?php echo $user['id']; ?>" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog">
                <div class="modal-content">
                  <form method="POST" action="manage_users.php">
                      <input type="hidden" name="action" value="update">
                      <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                      <div class="modal-header">
                        <h5 class="modal-title">Edit User: <?php echo htmlspecialchars($user['name']); ?></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body">
                          <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required></div>
                          <div class="mb-3"><label class="form-label">Username</label><input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required></div>
                          <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>"></div>
                          <div class="mb-3">
                              <label class="form-label">User Type</label>
                              <select name="user_type" class="form-select" required>
                                  <option value="student" <?php echo ($user['user_type'] == 'student' ? 'selected' : ''); ?>>Student</option>
                                  <option value="admin" <?php echo ($user['user_type'] == 'admin' ? 'selected' : ''); ?>>Admin</option>
                              </select>
                          </div>
                          <div class="mb-3">
                              <label class="form-label">New Password (Leave blank to keep old)</label>
                              <input type="password" name="password" class="form-control">
                          </div>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Save Changes</button>
                      </div>
                  </form>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($all_users)): ?>
            <tr>
                <td colspan="6" class="text-center">No users found.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="manage_users.php">
          <input type="hidden" name="action" value="add">
          <div class="modal-header">
            <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
              <div class="mb-3"><label class="form-label">Name</label><input type="text" name="name" class="form-control" required></div>
              <div class="mb-3"><label class="form-label">Username</label><input type="text" name="username" class="form-control" required></div>
              <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
              <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
              <div class="mb-3">
                  <label class="form-label">User Type</label>
                  <select name="user_type" class="form-select" required>
                      <option value="student">Student</option>
                      <option value="admin">Admin</option>
                  </select>
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Create User</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php 
// Include the bottom template
include 'layout_admin_bottom.php'; 
?>