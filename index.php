<?php
session_start();
require_once 'connection.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $usertype = $_SESSION['usertype'];
    if ($usertype === 'admin') header('Location: projects.php');
    elseif ($usertype === 'developer') header('Location: projects.php');
    elseif ($usertype === 'deployer') header('Location: build.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username=?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['usertype'] = $user['usertype'];
        if ($user['usertype'] === 'admin') header('Location: projects.php');
        elseif ($user['usertype'] === 'developer') header('Location: projects.php');
        elseif ($user['usertype'] === 'deployer') header('Location: build.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Roboto', Arial, sans-serif; background: #f6f8fa; margin: 0; padding: 0; }
        .container { max-width: 400px; margin: 80px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); padding: 36px 40px 40px 40px; }
        h1 { color: #2d3a4b; margin-top: 0; text-align: center; }
        form { display: flex; flex-direction: column; gap: 18px; }
        input[type="text"], input[type="password"] { font-size: 1rem; padding: 10px 12px; border: 1px solid #bfc9d1; border-radius: 5px; background: #fff; }
        button { background: #1976d2; color: #fff; border: none; border-radius: 5px; padding: 10px 0; font-size: 1rem; font-weight: 500; cursor: pointer; transition: background 0.2s; }
        button:hover { background: #1256a3; }
        .error { color: #d32f2f; font-weight: 500; margin-bottom: 10px; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Username" required autofocus>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
