<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['usertype'] !== 'admin') {
    header('Location: index.php');
    exit;
}
?>
<?php
require_once 'connection.php';

// Handle Add User
$form_errors = [];
if (isset($_POST['add'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $usertype = $_POST['usertype'] ?? '';
    // Validation
    if (strlen($username) < 3) $form_errors[] = 'Username must be at least 3 characters.';
    if (strlen($password) < 4) $form_errors[] = 'Password must be at least 4 characters.';
    if (!in_array($usertype, ['admin', 'developer', 'deployer'])) $form_errors[] = 'Invalid user type.';
    // Check for duplicate username
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username=?");
    $stmt->execute([$username]);
    if ($stmt->fetchColumn() > 0) $form_errors[] = 'Username already exists.';
    if (empty($form_errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, usertype) VALUES (?, ?, ?)");
        $stmt->execute([$username, $hash, $usertype]);
        header("Location: users.php");
        exit;
    }
}

// Handle Delete User
if (isset($_GET['delete'])) {
    $user_id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM users WHERE user_id=?");
    $stmt->execute([$user_id]);
    header("Location: users.php");
    exit;
}

// Fetch all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY user_id DESC");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Users</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Roboto', Arial, sans-serif; background: #f6f8fa; margin: 0; padding: 0; }
        .container { max-width: 700px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); padding: 32px 40px 40px 40px; }
        h1 { color: #2d3a4b; margin-top: 0; }
        form { display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end; background: #f4f7fa; padding: 18px 20px 10px 20px; border-radius: 8px; margin-bottom: 32px; }
        form input[type="text"], form input[type="password"], form select { font-size: 1rem; padding: 8px 10px; border: 1px solid #bfc9d1; border-radius: 5px; background: #fff; margin-right: 10px; min-width: 160px; }
        form button { background: #1976d2; color: #fff; border: none; border-radius: 5px; padding: 8px 22px; font-size: 1rem; font-weight: 500; cursor: pointer; transition: background 0.2s; }
        form button:hover { background: #1256a3; }
        .error { color: #d32f2f; font-weight: 500; margin-bottom: 10px; }
        table { border-collapse: collapse; width: 100%; background: #fff; margin-top: 10px; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.04); }
        th, td { border: none; padding: 12px 10px; text-align: left; }
        th { background: #e3eaf3; color: #2d3a4b; font-weight: 700; }
        tr:nth-child(even) { background: #f7fafd; }
        tr:hover { background: #eaf3fb; }
        .actions a { color: #1976d2; margin-right: 10px; }
        .actions a:last-child { margin-right: 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Users</h1>
        <?php if (!empty($form_errors)): ?>
            <div class="error">
                <?php foreach ($form_errors as $err): ?>
                    <div><?php echo htmlspecialchars($err); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required minlength="3">
            <input type="password" name="password" placeholder="Password" required minlength="4">
            <select name="usertype" required>
                <option value="">-- User Type --</option>
                <option value="admin">Admin</option>
                <option value="developer">Developer</option>
                <option value="deployer">Deployer</option>
            </select>
            <button type="submit" name="add">Add User</button>
        </form>
        <table>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>User Type</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($users as $user): ?>
            <tr>
                <td><?php echo $user['user_id']; ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td><?php echo ucfirst($user['usertype']); ?></td>
                <td class="actions">
                    <a href="users.php?delete=<?php echo $user['user_id']; ?>" onclick="return confirm('Delete this user?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
