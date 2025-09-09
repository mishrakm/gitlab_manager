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

    // Validation helper
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
?>
<html>
<head>
    <title>Project Properties</title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            background: #f6f8fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 900px;
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
        a {
            color: #1976d2;
            text-decoration: none;
            font-weight: 500;
        }
        a:hover {
            text-decoration: underline;
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
        form textarea,
        form select {
            font-size: 1rem;
            padding: 8px 10px;
            border: 1px solid #bfc9d1;
            border-radius: 5px;
            background: #fff;
            margin-right: 10px;
            min-width: 180px;
        }
        form textarea {
            min-width: 260px;
            min-height: 38px;
            resize: vertical;
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
                <td><?php echo nl2br(htmlspecialchars($property['property_value'])); ?></td>
                <td><?php echo ucfirst($property['selection_type']); ?></td>
                <td class="actions">
                    <a href="project_properties.php?project_id=<?php echo $project_id; ?>&edit=<?php echo $property['property_id']; ?>">Edit</a>
                    <a href="project_properties.php?project_id=<?php echo $project_id; ?>&delete=<?php echo $property['property_id']; ?>" onclick="return confirm('Delete this property?');">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>
