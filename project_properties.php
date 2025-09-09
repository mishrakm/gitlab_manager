<?php
require_once 'connection.php';

// Get project ID from query string
$project_id = isset($_GET['project_id']) ? intval($_GET['project_id']) : 0;
if ($project_id <= 0) {
    die('Invalid project ID.');
}

// Handle Add Property
if (isset($_POST['add'])) {
    $name = $_POST['property_name'];
    $value = $_POST['property_value'];
    $selection_type = $_POST['selection_type'];
    $stmt = $pdo->prepare("INSERT INTO project_properties (project_id, property_name, property_value, selection_type) VALUES (?, ?, ?, ?)");
    $stmt->execute([$project_id, $name, $value, $selection_type]);
    header("Location: project_properties.php?project_id=$project_id");
    exit;
}

// Handle Edit Property
if (isset($_POST['edit'])) {
    $property_id = $_POST['property_id'];
    $name = $_POST['property_name'];
    $value = $_POST['property_value'];
    $selection_type = $_POST['selection_type'];
    $stmt = $pdo->prepare("UPDATE project_properties SET property_name=?, property_value=?, selection_type=? WHERE property_id=?");
    $stmt->execute([$name, $value, $selection_type, $property_id]);
    header("Location: project_properties.php?project_id=$project_id");
    exit;
}

// Handle Delete Property
if (isset($_GET['delete'])) {
    $property_id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM project_properties WHERE property_id=?");
    $stmt->execute([$property_id]);
    header("Location: project_properties.php?project_id=$project_id");
    exit;
}

// Fetch all properties for this project
$stmt = $pdo->prepare("SELECT * FROM project_properties WHERE project_id=? ORDER BY property_id DESC");
$stmt->execute([$project_id]);
$properties = $stmt->fetchAll();

// Fetch property for editing
$edit_property = null;
if (isset($_GET['edit'])) {
    $property_id = $_GET['edit'];
    $stmt = $pdo->prepare("SELECT * FROM project_properties WHERE property_id=?");
    $stmt->execute([$property_id]);
    $edit_property = $stmt->fetch();
}

// Fetch project name
$stmt = $pdo->prepare("SELECT project_name FROM projects WHERE project_id=?");
$stmt->execute([$project_id]);
$project = $stmt->fetch();
$project_name = $project ? $project['project_name'] : '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Project Properties</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #f4f4f4; }
        form { margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Properties for Project: <?php echo htmlspecialchars($project_name); ?></h1>
    <a href="projects.php">&larr; Back to Projects</a>
    <h2><?php echo $edit_property ? 'Edit Property' : 'Add Property'; ?></h2>
    <form method="post" id="propertyForm">
        <?php if ($edit_property): ?>
            <input type="hidden" name="property_id" value="<?php echo $edit_property['property_id']; ?>">
        <?php endif; ?>
        <input type="text" name="property_name" placeholder="Property Name" required value="<?php echo $edit_property['property_name'] ?? ''; ?>">
        <select name="selection_type" id="selection_type" onchange="toggleValueInput()">
            <option value="single" <?php if (($edit_property['selection_type'] ?? '') === 'single') echo 'selected'; ?>>Single</option>
            <option value="multiselect" <?php if (($edit_property['selection_type'] ?? '') === 'multiselect') echo 'selected'; ?>>Multiselect</option>
        </select>
    <span id="valueInputContainer">
    <?php
    $property_value = $edit_property['property_value'] ?? '';
    ?>
    <textarea name="property_value" placeholder="Enter value(s), one per line for multiselect" required rows="3"><?php echo htmlspecialchars($property_value); ?></textarea>
    </span>
        <button type="submit" name="<?php echo $edit_property ? 'edit' : 'add'; ?>">
            <?php echo $edit_property ? 'Update' : 'Add'; ?>
        </button>
        <?php if ($edit_property): ?>
            <a href="project_properties.php?project_id=<?php echo $project_id; ?>">Cancel</a>
        <?php endif; ?>
    </form>
    <!-- No JS needed: always textarea for property_value -->
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Value</th>
            <th>Selection Type</th>
            <th>Actions</th>
        </tr>
        <?php foreach ($properties as $property): ?>
        <tr>
            <td><?php echo $property['property_id']; ?></td>
            <td><?php echo htmlspecialchars($property['property_name']); ?></td>
            <td><?php echo htmlspecialchars($property['property_value']); ?></td>
            <td><?php echo $property['selection_type']; ?></td>
            <td>
                <a href="project_properties.php?project_id=<?php echo $project_id; ?>&edit=<?php echo $property['property_id']; ?>">Edit</a> |
                <a href="project_properties.php?project_id=<?php echo $project_id; ?>&delete=<?php echo $property['property_id']; ?>" onclick="return confirm('Delete this property?');">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
