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
        <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">
        <style>
            body {
                font-family: 'Roboto', Arial, sans-serif;
                background: #f6f8fa;
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: 600px;
                margin: 60px auto;
                background: #fff;
                border-radius: 10px;
                box-shadow: 0 2px 12px rgba(0,0,0,0.08);
                padding: 36px 40px 40px 40px;
                text-align: center;
            }
            h1 {
                color: #2d3a4b;
                margin-top: 0;
            }
            ul {
                list-style: none;
                padding: 0;
                margin: 30px 0 0 0;
            }
            li {
                margin: 18px 0;
            }
            a {
                color: #1976d2;
                text-decoration: none;
                font-size: 1.15rem;
                font-weight: 500;
                padding: 10px 28px;
                border-radius: 6px;
                background: #e3eaf3;
                transition: background 0.2s;
                display: inline-block;
            }
            a:hover {
                background: #1976d2;
                color: #fff;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Select a Project to Build</h1>
            <ul>
            <?php foreach ($projects as $project): ?>
                <li><a href="build.php?project_id=<?php echo $project['project_id']; ?>"><?php echo htmlspecialchars($project['project_name']); ?></a></li>
            <?php endforeach; ?>
            </ul>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Step 2: Show dynamic form for selected project

$project_id = intval($_GET['project_id']);
$stmt = $pdo->prepare("SELECT * FROM projects WHERE project_id=?");
$stmt->execute([$project_id]);
$project = $stmt->fetch();
if (!$project) die('Project not found.');

// Fetch properties
$stmt = $pdo->prepare("SELECT * FROM project_properties WHERE project_id=? ORDER BY property_id");
$stmt->execute([$project_id]);
$properties = $stmt->fetchAll();

// Handle form submission and trigger pipeline
$selected = [];
$trigger_result = null;
$trigger_error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($properties as $prop) {
        $key = 'property_' . $prop['property_id'];
        if ($prop['selection_type'] === 'multiselect') {
            $selected[$prop['property_name']] = isset($_POST[$key]) ? $_POST[$key] : [];
        } else {
            $selected[$prop['property_name']] = isset($_POST[$key]) ? $_POST[$key] : '';
        }
    }

    // Compose flags variable (combine all selected values as space-separated string)
    $flags = [];
    foreach ($selected as $vals) {
        if (is_array($vals)) {
            foreach ($vals as $v) {
                if (trim($v) !== '') $flags[] = $v;
            }
        } else {
            if (trim($vals) !== '') $flags[] = $vals;
        }
    }
    $flags_str = trim(implode(' ', $flags));

    // Prepare data for GitLab trigger
    $token = $project['trigger_token'];
    $branch = $project['branch'];
    $git_project_id = $project['git_project_id'];
    $url = "https://pwgit.centralindia.cloudapp.azure.com/api/v4/projects/{$git_project_id}/trigger/pipeline";

    $post_fields = [
        'token' => $token,
        'ref' => $branch,
        'variables[flags]' => $flags_str
    ];

    // Trigger the pipeline using cURL
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if (curl_errno($ch)) {
        $trigger_error = 'Build trigger failed: ' . curl_error($ch);
        $curl_failed = true;
    } else if ($http_code >= 400) {
        $trigger_error = 'Build trigger failed: HTTP ' . $http_code . ' - ' . htmlspecialchars($response);
        $curl_failed = true;
    } else {
        $trigger_result = json_decode($response, true);
        $curl_failed = false;
    }
    curl_close($ch);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Build Project: <?php echo htmlspecialchars($project['project_name']); ?></title>
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', Arial, sans-serif;
            background: #f6f8fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 700px;
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
        a {
            color: #1976d2;
            text-decoration: none;
            font-weight: 500;
        }
        a:hover {
            text-decoration: underline;
        }
        form {
            background: #f4f7fa;
            padding: 18px 20px 10px 20px;
            border-radius: 8px;
            margin-bottom: 32px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        }
        form label {
            font-weight: 500;
            color: #3b4a5a;
        }
        form select {
            font-size: 1rem;
            padding: 7px 10px;
            border: 1px solid #bfc9d1;
            border-radius: 5px;
            background: #fff;
            margin-top: 6px;
            min-width: 180px;
        }
        .button-row {
            display: flex;
            gap: 16px;
            margin-top: 18px;
        }
        form button, .fallback-btn {
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
        form button:hover, .fallback-btn:hover {
            background: #1256a3;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .result-section {
            background: #f4f7fa;
            border-radius: 8px;
            padding: 18px 20px;
            margin-top: 30px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        }
        .result-section h2 {
            margin-top: 0;
        }
        .result-section ul {
            margin: 0 0 10px 0;
        }
        .result-section li {
            margin-bottom: 6px;
        }
        .result-section div {
            margin-bottom: 6px;
        }
        .error {
            color: #d32f2f;
            font-weight: 500;
        }
        .success {
            color: #388e3c;
            font-weight: 500;
        }
        .fallback-btn {
            background: #ff9800;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 7px 18px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            margin-top: 10px;
        }
        .fallback-btn:hover {
            background: #e65100;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Build Project: <?php echo htmlspecialchars($project['project_name']); ?></h1>
        <a href="build.php">&larr; Back to Project List</a>
        <form method="post">
            <?php foreach ($properties as $prop):
                $values = array_map('trim', preg_split('/\r?\n/', $prop['property_value']));
                $input_name = 'property_' . $prop['property_id'];
            ?>
            <div class="form-group">
                <label><?php echo htmlspecialchars($prop['property_name']); ?></label><br>
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
            <div class="button-row">
                <button type="submit">Submit</button>
            </div>
        </form>
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
        <div class="result-section">
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
            <h2>Pipeline Trigger Result</h2>
            <?php if ($trigger_error): ?>
                <div class="error"><?php echo $trigger_error; ?></div>
                <?php if (!empty($curl_failed)): ?>
                <div style="margin-top:1em;">
                    <b>Fallback: Trigger from your browser</b><br>
                    <div class="button-row">
                        <button class="fallback-btn" onclick="triggerFromBrowser(); return false;">Trigger Pipeline via Browser</button>
                    </div>
                    <div id="jsResult"></div>
                    <script>
                    function triggerFromBrowser() {
                        var formData = new FormData();
                        formData.append('token', <?php echo json_encode($project['trigger_token']); ?>);
                        formData.append('ref', <?php echo json_encode($project['branch']); ?>);
                        formData.append('variables[flags]', <?php echo json_encode($flags_str); ?>);
                        fetch(<?php echo json_encode($url); ?>, {
                            method: 'POST',
                            body: formData
                        })
                        .then(r => r.json())
                        .then(data => {
                            let html = '';
                            if (data.error) {
                                html = '<span class="error"><b>Error:</b> ' + data.error + '</span>';
                            } else {
                                html = `<div><b>Pipeline ID:</b> ${data.id || ''}</div>` +
                                       `<div><b>Status:</b> ${data.status || ''}</div>` +
                                       `<div><b>Branch:</b> ${data.ref || ''}</div>` +
                                       `<div><b>Commit SHA:</b> ${data.sha || ''}</div>` +
                                       `<div><b>Triggered by:</b> ${(data.user ? data.user.name + ' (' + data.user.username + ')' : 'N/A')}</div>` +
                                       `<div><b>Created at:</b> ${data.created_at || ''}</div>` +
                                       `<div><b>Pipeline URL:</b> <a href="${data.web_url}" target="_blank">${data.web_url || ''}</a></div>`;
                            }
                            document.getElementById('jsResult').innerHTML = html;
                        })
                        .catch(e => {
                            document.getElementById('jsResult').innerHTML = '<span class="error">Build trigger failed: ' + e.message + '</span>';
                        });
                    }
                    </script>
                </div>
                <?php endif; ?>
            <?php elseif ($trigger_result): ?>
                <?php if (isset($trigger_result['error'])): ?>
                    <div class="error"><b>Error:</b> <?php echo htmlspecialchars($trigger_result['error']); ?></div>
                <?php else: ?>
                    <div class="success"><b>Pipeline ID:</b> <?php echo htmlspecialchars($trigger_result['id'] ?? ''); ?></div>
                    <div><b>Status:</b> <?php echo htmlspecialchars($trigger_result['status'] ?? ''); ?></div>
                    <div><b>Branch:</b> <?php echo htmlspecialchars($trigger_result['ref'] ?? ''); ?></div>
                    <div><b>Commit SHA:</b> <?php echo htmlspecialchars($trigger_result['sha'] ?? ''); ?></div>
                    <div><b>Triggered by:</b> <?php echo isset($trigger_result['user']) ? htmlspecialchars($trigger_result['user']['name'] . ' (' . $trigger_result['user']['username'] . ')') : 'N/A'; ?></div>
                    <div><b>Created at:</b> <?php echo htmlspecialchars($trigger_result['created_at'] ?? ''); ?></div>
                    <div><b>Pipeline URL:</b> <?php if (isset($trigger_result['web_url'])): ?><a href="<?php echo htmlspecialchars($trigger_result['web_url']); ?>" target="_blank"><?php echo htmlspecialchars($trigger_result['web_url']); ?></a><?php endif; ?></div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
