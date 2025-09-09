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
        body {
            font-family: 'Roboto', Arial, sans-serif;
            background: linear-gradient(120deg, #e3eaf3 0%, #f6f8fa 100%);
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 400px;
            margin: 80px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(25, 118, 210, 0.10), 0 1.5px 6px rgba(0,0,0,0.04);
            padding: 40px 44px 44px 44px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        h1 {
            color: #1976d2;
            margin-top: 0;
            text-align: center;
            font-size: 2.1rem;
            letter-spacing: 1px;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 22px;
            width: 100%;
        }
        input[type="text"], input[type="password"] {
            font-size: 1.08rem;
            padding: 12px 14px;
            border: 1.5px solid #bfc9d1;
            border-radius: 7px;
            background: #f7fafd;
            transition: border 0.2s;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            border: 1.5px solid #1976d2;
            outline: none;
            background: #fff;
        }
        button {
            background: linear-gradient(90deg, #1976d2 60%, #1256a3 100%);
            color: #fff;
            border: none;
            border-radius: 7px;
            padding: 12px 0;
            font-size: 1.08rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s;
            box-shadow: 0 2px 8px rgba(25, 118, 210, 0.08);
        }
        button:hover {
            background: linear-gradient(90deg, #1256a3 60%, #1976d2 100%);
        }
        .error {
            color: #d32f2f;
            font-weight: 500;
            margin-bottom: 10px;
            text-align: center;
            background: #ffeaea;
            border-radius: 6px;
            padding: 8px 0;
        }
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
