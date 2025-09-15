<?php
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['usertype'], ['admin', 'developer'])) {
    header('Location: index.php');
    exit;
}

// All code below this line will only run if the user is logged in
<?php
require_once 'connection.php';

// Handle Add Project
if (isset($_POST['add'])) {
    $name = $_POST['project_name'];
    $status = $_POST['status'];
    $git_project_id = $_POST['git_project_id'];
    $branch = $_POST['branch'];
    $trigger_token = $_POST['trigger_token'];
    $stmt = $pdo->prepare("INSERT INTO projects (project_name, status, git_project_id, branch, trigger_token) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $status, $git_project_id, $branch, $trigger_token]);
    header("Location: projects.php");
    exit;
}

// Handle Edit Project
if (isset($_POST['edit'])) {
    $id = $_POST['project_id'];
    $name = $_POST['project_name'];
    $status = $_POST['status'];
    $git_project_id = $_POST['git_project_id'];
    $branch = $_POST['branch'];
    $trigger_token = $_POST['trigger_token'];
    $stmt = $pdo->prepare("UPDATE projects SET project_name=?, status=?, git_project_id=?, branch=?, trigger_token=? WHERE project_id=?");
    $stmt->execute([$name, $status, $git_project_id, $branch, $trigger_token, $id]);
    header("Location: projects.php");
    exit;
}

// Handle Delete Project
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM projects WHERE project_id=?");
    $stmt->execute([$id]);
    header("Location: projects.php");
    exit;
}

// Fetch all projects
$stmt = $pdo->query("SELECT * FROM projects ORDER BY create_date DESC");
$projects = $stmt->fetchAll();

// Fetch project for editing
$edit_project = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE project_id=?");
    $stmt->execute([$id]);
    $edit_project = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Projects</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            background: #f6f8fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1000px;
            margin: 40px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            padding: 32px 40px 40px 40px;
        }
        h1 {
            color: #2d3a4b;
            margin-top: 0;
        }
        h2 {
            color: #3b4a5a;
            margin-bottom: 10px;
        }
        form {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: flex-end;
            background: #f4f7fa;
            padding: 18px 20px 10px 20px;
            border-radius: 8px;
            margin-bottom: 32px;
        }
        form input[type="text"],
        form input[type="number"],
        form select {
            font-size: 1rem;
            padding: 8px 10px;
            border: 1px solid #bfc9d1;
            border-radius: 5px;
            background: #fff;
            margin-right: 10px;
            min-width: 180px;
        }
        form button {
            background: #1976d2;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 8px 22px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }
        form button:hover {
            background: #1256a3;
        }
        form a {
            margin-left: 10px;
            color: #888;
            font-size: 0.98rem;
        }
        table {
            border-collapse: collapse;
            width: 100%;
            background: #fff;
            margin-top: 10px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        }
        th, td {
            border: none;
            padding: 12px 10px;
            text-align: left;
        }
        th {
            background: #e3eaf3;
            color: #2d3a4b;
            font-weight: 700;
        }
        tr:nth-child(even) {
            background: #f7fafd;
        }
        tr:hover {
            background: #eaf3fb;
        }
        .actions a {
            color: #1976d2;
            margin-right: 10px;
        }
        .actions a:last-child {
            margin-right: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Projects</h1>
        <h2><?php echo $edit_project ? 'Edit Project' : 'Add Project'; ?></h2>
        <form method="post">
            <?php if ($edit_project): ?>
                <input type="hidden" name="project_id" value="<?php echo $edit_project['project_id']; ?>">
            <?php endif; ?>
            <input type="text" name="project_name" placeholder="Project Name" required value="<?php echo $edit_project['project_name'] ?? ''; ?>">
            <input type="number" name="git_project_id" placeholder="Git Project ID" value="<?php echo $edit_project['git_project_id'] ?? ''; ?>">
            <input type="text" name="branch" placeholder="Branch" value="<?php echo $edit_project['branch'] ?? ''; ?>">
            <input type="text" name="trigger_token" placeholder="Trigger Token" value="<?php echo $edit_project['trigger_token'] ?? ''; ?>">
            <select name="status">
                <option value="active" <?php if (($edit_project['status'] ?? '') === 'active') echo 'selected'; ?>>Active</option>
                <option value="inactive" <?php if (($edit_project['status'] ?? '') === 'inactive') echo 'selected'; ?>>Inactive</option>
            </select>
            <button type="submit" name="<?php echo $edit_project ? 'edit' : 'add'; ?>">
                <?php echo $edit_project ? 'Update' : 'Add'; ?>
            </button>
            <?php if ($edit_project): ?>
                <a href="projects.php">Cancel</a>
            <?php endif; ?>
        </form>
        <table>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Git Project ID</th>
                <th>Branch</th>
                <th>Trigger Token</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($projects as $project): ?>
            <tr>
                <td><?php echo $project['project_id']; ?></td>
                <td><?php echo htmlspecialchars($project['project_name']); ?></td>
                <td><?php echo $project['git_project_id']; ?></td>
                <td><?php echo htmlspecialchars($project['branch']); ?></td>
                <td><?php echo htmlspecialchars($project['trigger_token']); ?></td>
                <td><?php echo $project['status']; ?></td>
                <td><?php echo $project['create_date']; ?></td>
                <td class="actions">
                    <a href="projects.php?edit=<?php echo $project['project_id']; ?>">Edit</a>
                    <a href="projects.php?delete=<?php echo $project['project_id']; ?>" onclick="return confirm('Delete this project?');">Delete</a>
                    <a href="project_properties.php?project_id=<?php echo $project['project_id']; ?>">Properties</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
