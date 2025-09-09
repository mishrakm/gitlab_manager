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
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f4f4f4; }
        form { margin-bottom: 20px; }
    </style>
</head>
<body>
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
            <td>
                <a href="projects.php?edit=<?php echo $project['project_id']; ?>">Edit</a> |
                <a href="projects.php?delete=<?php echo $project['project_id']; ?>" onclick="return confirm('Delete this project?');">Delete</a> |
                <a href="project_properties.php?project_id=<?php echo $project['project_id']; ?>">Properties</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
