
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            display: flex; 
            flex-direction: column;
            margin: 0;
            min-height: 100vh; 
            background-color: #f0f0f0;
            align-items: center; 
            justify-content: flex-start; 
        }
        .container {
            display: flex;
            flex-direction: column;
            width: 100%; 
            padding: 20px;
            box-sizing: border-box; 
            align-items: center; 
            text-align: center; 
        }
        h1 {
            color: #00FF7F;
        }
        h2 {
            color: #333;
        }
        .button-group {
            display: inline-block;
        }
        .delete-button,
        .rename-button,
        .edit-button {
            margin-left: 5px;
            cursor: pointer;
        }
        #current-time {
            margin-bottom: 20px; 
        }
    </style>
</head>
<body>
    <h1>Sing-box文件管理器</h1>
</body>
</html>

<?php
$configDir = '/etc/neko/config/';

ini_set('memory_limit', '256M');

if (!is_dir($configDir)) {
    mkdir($configDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['configFileInput'])) {
        $file = $_FILES['configFileInput'];
        $uploadFilePath = $configDir . basename($file['name']);

        if ($file['error'] === UPLOAD_ERR_OK) {
            if (move_uploaded_file($file['tmp_name'], $uploadFilePath)) {
                echo '配置文件上传成功：' . htmlspecialchars(basename($file['name']));
            } else {
                echo '配置文件上传失败！';
            }
        } else {
            echo '上传错误：' . $file['error'];
        }
    }

    if (isset($_POST['deleteConfigFile'])) {
        $fileToDelete = $configDir . basename($_POST['deleteConfigFile']);
        if (file_exists($fileToDelete) && unlink($fileToDelete)) {
            echo '配置文件删除成功：' . htmlspecialchars(basename($_POST['deleteConfigFile']));
        } else {
            echo '配置文件删除失败！';
        }
    }

    if (isset($_POST['oldFileName'], $_POST['newFileName'], $_POST['fileType'])) {
        $oldFileName = basename($_POST['oldFileName']);
        $newFileName = basename($_POST['newFileName']);
    
        if ($_POST['fileType'] === 'config') {
            $oldFilePath = $configDir . $oldFileName;
            $newFilePath = $configDir . $newFileName;
        } else {
            echo '无效的文件类型';
            exit;
        }

        if (file_exists($oldFilePath) && !file_exists($newFilePath)) {
            if (rename($oldFilePath, $newFilePath)) {
                echo '文件重命名成功：' . htmlspecialchars($oldFileName) . ' -> ' . htmlspecialchars($newFileName);
            } else {
                echo '文件重命名失败！';
            }
        } else {
            echo '文件重命名失败，文件不存在或新文件名已存在。';
        }
    }

    if (isset($_POST['editFile']) && isset($_POST['fileType'])) {
        $fileToEdit = $configDir . basename($_POST['editFile']);
        $fileContent = '';
        $editingFileName = htmlspecialchars($_POST['editFile']);

        if (file_exists($fileToEdit)) {
            $handle = fopen($fileToEdit, 'r');
            if ($handle) {
                while (($line = fgets($handle)) !== false) {
                    $fileContent .= htmlspecialchars($line);
                }
                fclose($handle);
            } else {
                echo '无法打开文件';
            }
        }
    }

    if (isset($_POST['saveContent'], $_POST['fileName'], $_POST['fileType'])) {
        $fileToSave = $configDir . basename($_POST['fileName']);
        $contentToSave = $_POST['saveContent'];
        file_put_contents($fileToSave, $contentToSave);
        echo '<p>文件内容已更新：' . htmlspecialchars(basename($fileToSave)) . '</p>';
    }

    if (isset($_GET['customFile'])) {
        $customDir = rtrim($_GET['customDir'], '/') . '/';
        $customFilePath = $customDir . basename($_GET['customFile']);
        if (file_exists($customFilePath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($customFilePath) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($customFilePath));
            readfile($customFilePath);
            exit;
        } else {
            echo '文件不存在！';
        }
    }
}

$configFiles = scandir($configDir);

if ($configFiles !== false) {
    $configFiles = array_diff($configFiles, array('.', '..'));
} else {
    $configFiles = []; 
}

function formatSize($size) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    $unit = 0;
    while ($size >= 1024 && $unit < count($units) - 1) {
        $size /= 1024;
        $unit++;
    }
    return round($size, 2) . ' ' . $units[$unit];
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            background-image: url('/nekoclash/assets/img/1.jpg');
            background-size: cover;
            background-repeat: no-repeat;
            color: white;
        }
        .editor {
            width: 100%;
            height: 400px; 
            background-color: #222; 
            color: white;
            padding: 10px;
            border: 1px solid #555;
            border-radius: 5px;
            font-family: monospace; 
        }
        .delete-button, .rename-button, .edit-button {
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .delete-button {
            background-color: red;
            color: white;
            border: none;
        }
        .delete-button:hover {
            background-color: darkred;
        }
        .rename-button {
            background-color: lightgreen;
            color: black;
            border: none;
        }
        .rename-button:hover {
            background-color: darkgreen;
        }
        .edit-button {
            background-color: orange;
            color: white;
            border: none;
        }
        .edit-button:hover {
            background-color: darkred;
        }
        .button-group {
            display: inline-flex;
            gap: 5px;
        }
    </style>
</head>
<body>
    <h1 style="color: #00FFFF;">文件上传和下载管理</h1>

    <h2 style="color: #00FF7F;">配置文件管理</h2>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="file" name="configFileInput" required>
        <input type="submit" value="上传配置文件">
    </form>
    <ul>
        <?php foreach ($configFiles as $file): ?>
            <?php $filePath = $configDir . $file; ?>
            <li>
                <a href="download.php?file=<?php echo urlencode($file); ?>"><?php echo htmlspecialchars($file); ?></a>
                (大小: <?php echo file_exists($filePath) ? formatSize(filesize($filePath)) : '文件不存在'; ?>)
                <div class="button-group">
                    <form action="" method="post" style="display:inline;">
                        <input type="hidden" name="deleteConfigFile" value="<?php echo htmlspecialchars($file); ?>">
                        <input type="submit" class="delete-button" value="删除" onclick="return confirm('确定要删除这个文件吗？');">
                    </form>

                    <form action="" method="post" style="display:inline;">
                        <input type="hidden" name="oldFileName" value="<?php echo htmlspecialchars($file); ?>">
                        <input type="text" name="newFileName" placeholder="新文件名" required>
                        <input type="hidden" name="fileType" value="config">
                        <input type="submit" class="rename-button" value="重命名">
                    </form>

                    <form action="" method="post" style="display:inline;">
                        <input type="hidden" name="editFile" value="<?php echo htmlspecialchars($file); ?>">
                        <input type="hidden" name="fileType" value="config">
                        <input type="submit" class="edit-button" value="编辑">
                    </form>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>

    <?php if (isset($fileContent)): ?>
        <?php $fileToEdit = $configDir . basename($_POST['editFile']); ?>
        <h2 style="color: #00FF7F;">编辑文件: <?php echo $editingFileName; ?></h2>
        <p>最后更新日期: <?php echo date('Y-m-d H:i:s', filemtime($fileToEdit)); ?></p>
        <form action="" method="post">
            <textarea name="saveContent" rows="15" cols="150" class="editor"><?php echo $fileContent; ?></textarea><br>
            <input type="hidden" name="fileName" value="<?php echo htmlspecialchars($_POST['editFile']); ?>">
            <input type="hidden" name="fileType" value="<?php echo htmlspecialchars($_POST['fileType']); ?>">
            <input type="submit" value="保存内容">
        </form>
    <?php endif; ?>
    <br>
    <style>
        .button {
            text-decoration: none;
            padding: 10px;
            background-color: lightblue;
            color: black;
            border: 1px solid #007bff;
            border-radius: 5px;
            transition: background-color 0.3s; 
        }
        .button:hover {
            background-color: deepskyblue; 
        }
    </style>

    <div style="display: flex; gap: 10px;">
        <a href="javascript:history.back()" class="button">返回上一级菜单</a>
        <a href="/nekoclash/upload_sb.php" class="button">返回当前菜单</a>
        <a href="/nekoclash/configs.php" class="button">返回配置菜单</a>
        <a href="/nekoclash" class="button">返回主菜单</a>
    </div>
</body>
</html>
<?php
$subscriptionPath = '/etc/neko/config/';
$dataFile = $subscriptionPath . 'subscription_data.json';
$configFile = $subscriptionPath . 'config.json';

$message = "";
$subscriptionData = [
    'subscription' => [
        'url' => '',
        'file_name' => 'config.json',
    ]
];

if (!file_exists($subscriptionPath)) {
    mkdir($subscriptionPath, 0755, true);
}

if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode($subscriptionData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

$subscriptionData = json_decode(file_get_contents($dataFile), true);
if (!$subscriptionData) {
    $subscriptionData = $subscriptionData;
}

if (isset($_POST['update'])) {
    $subscriptionUrl = $_POST['subscription_url'] ?? '';
    $customFileName = $_POST['custom_file_name'] ?? $subscriptionData['subscription']['file_name'];

    $subscriptionData['subscription']['url'] = $subscriptionUrl;
    $subscriptionData['subscription']['file_name'] = $customFileName;

    if (!empty($subscriptionUrl)) {
        $finalPath = $subscriptionPath . $customFileName;

        $originalContent = file_exists($finalPath) ? file_get_contents($finalPath) : '';

        $ch = curl_init($subscriptionUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $fileContent = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($fileContent === false) {
            $message = "无法下载文件。cURL 错误信息: " . $error;
        } else {
            $fileContent = str_replace("\xEF\xBB\xBF", '', $fileContent);

            $parsedData = json_decode($fileContent, true);
            if ($parsedData === null && json_last_error() !== JSON_ERROR_NONE) {
                file_put_contents($finalPath, $originalContent);
                $message = "解析 JSON 数据失败！错误信息: " . json_last_error_msg();
            } else {
                if (isset($parsedData['inbounds'])) {
                    $newInbounds = [];

                    foreach ($parsedData['inbounds'] as $inbound) {
                        if (isset($inbound['type']) && $inbound['type'] === 'mixed' && $inbound['tag'] === 'mixed-in') {
                            $newInbounds[] = $inbound;
                        } elseif (isset($inbound['type']) && $inbound['type'] === 'tun') {
                            continue;
                        }
                    }

                    $newInbounds[] = [
                        "type" => "mixed",
                        "tag" => "SOCKS-in",
                        "listen" => "::",
                        "listen_port" => 4673
                    ];

                    $newInbounds[] = [
                        "auto_route" => true,
                        "domain_strategy" => "prefer_ipv4",
                        "endpoint_independent_nat" => true,
                        "inet4_address" => "172.19.0.1/30",
                        "inet6_address" => "2001:0470:f9da:fdfa::1/64",
                        "mtu" => 9000,
                        "sniff" => true,
                        "sniff_override_destination" => true,
                        "stack" => "system",
                        "strict_route" => true,
                        "type" => "tun"
                    ];

                    $parsedData['inbounds'] = $newInbounds;
                }

                if (isset($parsedData['experimental']['clash_api'])) {
                    $parsedData['experimental']['clash_api'] = [
                        "external_ui" => "/etc/neko/ui/",
                        "external_controller" => "0.0.0.0:9090",
                        "secret" => "Akun"
                    ];
                }

                $fileContent = json_encode($parsedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

                if (file_put_contents($finalPath, $fileContent) === false) {
                    $message = "无法保存文件到: $finalPath";
                } else {
                    $message = "订阅链接 {$subscriptionUrl} 更新成功！文件已保存到: {$finalPath}，并成功解析和替换 JSON 数据。";
                }
            }
        }
    } else {
        $message = "订阅链接为空！";
    }

    file_put_contents($dataFile, json_encode($subscriptionData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sing-box文件管理器</title>
    <style>
        .input-group {
            margin-bottom: 10px;
        }
        .input-group label {
            margin-right: 10px;
            white-space: nowrap;
        }
        .help-text {
            font-size: 14px;
            color: white;
            margin-bottom: 20px;
        }
        body {
            background-color: #333;
            font-family: Arial, sans-serif;
        }
        h1, .help-text {
            color: #00FF7F;
        }
        textarea.copyable {
            width: 50%;
            height: 150px;
            resize: none;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #444;
            color: white;
            font-size: 14px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
            display: none;
        }
        textarea.copyable:focus {
            outline: none;
            border-color: #ff79c6;
            box-shadow: 0 0 5px rgba(255, 121, 198, 0.5);
        }
        #copyButton {
            background-color: #00BFFF;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        #copyButton:hover {
            background-color: #008CBA;
        }
        button[name="update"] {
            background-color: #FF6347;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button[name="update"]:hover {
            background-color: darkgreen;
        }
        #convertButton,
        button[name="convert_base64"] {
            background-color: #00BFFF;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        #convertButton:hover,
        button[name="convert_base64"]:hover {
            background-color: #008CBA;
        }
        button[name="set_auto_update"] {
            background-color: #32CD32;
            color: white;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        button[name="set_auto_update"]:hover {
            background-color: #228B22;
        }
        .form-spacing {
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <h1 style="color: #00FF7F;">Sing-box订阅程序</h1>
    <p class="help-text">
        请填写Sing-box订阅链接订阅，也可以手动上传配置文件，不要修改默认名称，注意端口和接口。<br>
    </p>

    <?php if ($message): ?>
        <p><?php echo nl2br(htmlspecialchars($message)); ?></p>
    <?php endif; ?>

    <h2 style="color: #00FF7F;">订阅链接设置</h2>
    <form method="post">
        <div class="input-group">
            <label for="subscription_url">订阅链接:</label>
            <input type="text" name="subscription_url" id="subscription_url" value="<?php echo htmlspecialchars($subscriptionData['subscription']['url']); ?>" required>
        </div>
        <div class="input-group">
            <label for="custom_file_name">自定义文件名 (默认为 config.json):</label>
            <input type="text" name="custom_file_name" id="custom_file_name" value="<?php echo htmlspecialchars($subscriptionData['subscription']['file_name']); ?>">
        </div>
        <button type="submit" name="update">更新配置</button>
    </form>
</body>
</html>