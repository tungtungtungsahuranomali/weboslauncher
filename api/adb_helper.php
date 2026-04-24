<?php

require_once __DIR__ . "/config.php";
require_once __DIR__ . "/adb_helper.php";
require_once __DIR__ . "/device_helper.php";

function adbConnect($deviceIp)
{
    $cmd = ADB_PATH . " connect " . escapeshellarg($deviceIp . ":" . ADB_PORT);
    return shell_exec($cmd);
}

function adbClearPackage($deviceIp, $package)
{
    $cmd = ADB_PATH . " -s " . escapeshellarg($deviceIp . ":" . ADB_PORT)
        . " shell pm clear " . escapeshellarg($package);
    return shell_exec($cmd);
}

function adbDisconnect($deviceIp)
{
    $cmd = ADB_PATH . " disconnect " . escapeshellarg($deviceIp . ":" . ADB_PORT);
    return shell_exec($cmd);
}

function adbStartLauncher($deviceIp)
{
    $cmd = ADB_PATH . " -s " . escapeshellarg($deviceIp . ":" . ADB_PORT)
        . " shell am start -n com.takeoff.launcher/.MainActivity";
    return shell_exec($cmd);
}
