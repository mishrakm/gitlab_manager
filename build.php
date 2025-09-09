<?php
require_once 'connection.php';

// Step 1: List all projects
if (!isset($_GET['project_id'])) {
    $stmt = $pdo->query("SELECT project_id, project_name FROM projects ORDER BY project_name");
    $projects = $stmt->fetchAll();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Select Project</title>
    </head>
    <body>
        <h1>Select a Project to Build</h1>
        <ul>
        <?php foreach ($projects as $project): ?>
            <li><a href="build.php?project_id=<?php echo $project['project_id']; ?>"><?php echo htmlspecialchars($project['project_name']); ?></a></li>
        <?php endforeach; ?>
        </ul>
    </body>
    </html>
    <?php
    exit;
}

// Step 2: Show dynamic form for selected project
$project_id = intval($_GET['project_id']);
$stmt = $pdo->prepare("SELECT project_name FROM projects WHERE project_id=?");
$stmt->execute([$project_id]);
$project = $stmt->fetch();
if (!$project) die('Project not found.');

// Fetch properties
$stmt = $pdo->prepare("SELECT * FROM project_properties WHERE project_id=? ORDER BY property_id");
$stmt->execute([$project_id]);
$properties = $stmt->fetchAll();

// Handle form submission
$selected = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($properties as $prop) {
        $key = 'property_' . $prop['property_id'];
        if ($prop['selection_type'] === 'multiselect') {
            $selected[$prop['property_name']] = isset($_POST[$key]) ? $_POST[$key] : [];
        } else {
            $selected[$prop['property_name']] = isset($_POST[$key]) ? $_POST[$key] : '';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Build Project: <?php echo htmlspecialchars($project['project_name']); ?></title>
</head>
<body>
    <h1>Build Project: <?php echo htmlspecialchars($project['project_name']); ?></h1>
    <a href="build.php">&larr; Back to Project List</a>
    <form method="post">
        <?php foreach ($properties as $prop):
            $values = array_map('trim', preg_split('/\r?\n/', $prop['property_value']));
            $input_name = 'property_' . $prop['property_id'];
        ?>
        <div style="margin-bottom:10px;">
            <label><b><?php echo htmlspecialchars($prop['property_name']); ?></b></label><br>
            <?php if ($prop['selection_type'] === 'multiselect'): ?>
                <select name="<?php echo $input_name; ?>[]" multiple size="<?php echo min(5, count($values)); ?>">
                    <?php foreach ($values as $val): ?>
                        <option value="<?php echo htmlspecialchars($val); ?>" <?php if (isset($selected[$prop['property_name']]) && in_array($val, (array)$selected[$prop['property_name']])) echo 'selected'; ?>><?php echo htmlspecialchars($val); ?></option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <select name="<?php echo $input_name; ?>">
                    <option value="">-- Select --</option>
                    <?php foreach ($values as $val): ?>
                        <option value="<?php echo htmlspecialchars($val); ?>" <?php if (isset($selected[$prop['property_name']]) && $selected[$prop['property_name']] == $val) echo 'selected'; ?>><?php echo htmlspecialchars($val); ?></option>
                    <?php endforeach; ?>
                </select>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <button type="submit">Submit</button>
    </form>
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <h2>Selected Values</h2>
        <ul>
        <?php foreach ($selected as $label => $vals): ?>
            <li><b><?php echo htmlspecialchars($label); ?>:</b> 
                <?php 
                if (is_array($vals)) {
                    echo htmlspecialchars(implode(', ', $vals));
                } else {
                    echo htmlspecialchars($vals);
                }
                ?>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</body>
</html>
