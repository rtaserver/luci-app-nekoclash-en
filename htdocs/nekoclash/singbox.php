<?php
function logMessage($message) {
    $logFile = '/var/log/sing-box_update.log'; 
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

function writeVersionToFile($version) {
    $versionFile = '/etc/neko/core/version.txt';
    $result = file_put_contents($versionFile, $version);
    if ($result === false) {
        logMessage("无法写入版本文件: $versionFile");
        logMessage("检查路径是否存在，并确认 PHP 进程具有写权限。");
    } else {
        logMessage("成功写入版本文件: $versionFile");
    }
}

$latest_version = '1.10.0-beta.5'; 
$current_version = ''; 
$install_path = '/usr/bin/sing-box'; 
$temp_file = '/tmp/sing-box.tar.gz'; 
$temp_dir = '/tmp/singbox_temp'; 

if (file_exists($install_path)) {
    $current_version = trim(shell_exec("{$install_path} --version"));
    logMessage("当前版本: $current_version");
} else {
    logMessage("当前版本文件不存在，将视为未安装。");
}

$current_arch = trim(shell_exec("uname -m"));

$download_url = '';
switch ($current_arch) {
    case 'aarch64':
        $download_url = 'https://github.com/SagerNet/sing-box/releases/download/v1.10.0-beta.5/sing-box-1.10.0-beta.5-linux-arm64.tar.gz';
        break;
    case 'x86_64':
        $download_url = 'https://github.com/SagerNet/sing-box/releases/download/v1.10.0-beta.5/sing-box-1.10.0-beta.5-linux-amd64.tar.gz';
        break;
    default:
        logMessage("未找到适合架构的下载链接: $current_arch");
        echo "未找到适合架构的下载链接: $current_arch";
        exit;
}

logMessage("最新版本: $latest_version");
logMessage("当前架构: $current_arch");
logMessage("下载链接: $download_url");

if (trim($current_version) === trim($latest_version)) {
    logMessage("当前版本已是最新版本，无需更新。");
    echo "当前版本已是最新版本。";
    exit;
}

logMessage("开始下载核心更新...");
exec("wget -O '$temp_file' '$download_url'", $output, $return_var);
logMessage("wget 返回值: $return_var");

if ($return_var === 0) {
    if (!is_dir($temp_dir)) {
        logMessage("创建临时解压目录: $temp_dir");
        mkdir($temp_dir, 0755, true);
    } else {
        logMessage("临时解压目录已存在: $temp_dir");
    }

    logMessage("解压命令: tar -xzf '$temp_file' -C '$temp_dir'");
    exec("tar -xzf '$temp_file' -C '$temp_dir'", $output, $return_var);
    logMessage("解压返回值: $return_var");

    if ($return_var === 0) {
        logMessage("解压后的文件列表:");
        exec("ls -lR '$temp_dir'", $output);
        logMessage(implode("\n", $output));

        $extracted_file = glob("$temp_dir/sing-box-*/*sing-box")[0] ?? '';
        if ($extracted_file && file_exists($extracted_file)) {
            logMessage("移动文件命令: cp -f '$extracted_file' '$install_path'");
            exec("cp -f '$extracted_file' '$install_path'", $output, $return_var);
            logMessage("替换文件返回值: $return_var");

            if ($return_var === 0) {
                exec("chmod 0755 '$install_path'", $output, $return_var);
                logMessage("设置权限命令: chmod 0755 '$install_path'");
                logMessage("设置权限返回值: $return_var");

                if ($return_var === 0) {
                    logMessage("核心更新完成！当前版本: $latest_version");
                    writeVersionToFile($latest_version); 
                    echo "更新完成！当前版本: $latest_version";
                } else {
                    logMessage("设置权限失败！");
                    echo "设置权限失败！";
                }
            } else {
                logMessage("替换文件失败，返回值: $return_var");
                echo "替换文件失败！";
            }
        } else {
            logMessage("解压后的文件 'sing-box' 不存在。");
            echo "解压后的文件 'sing-box' 不存在。";
        }
    } else {
        logMessage("解压失败，返回值: $return_var");
        echo "解压失败！";
    }
} else {
    logMessage("下载失败，返回值: $return_var");
    echo "下载失败！";
}

if (file_exists($temp_file)) {
    unlink($temp_file);
    logMessage("清理临时文件: $temp_file");
}
if (is_dir($temp_dir)) {
    exec("rm -r '$temp_dir'");
    logMessage("清理临时解压目录: $temp_dir");
}
?>
