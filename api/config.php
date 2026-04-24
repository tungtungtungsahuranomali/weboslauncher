<?php
// Auto-detect OS untuk ADB path
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    define("ADB_PATH", "C:\\adb\\platform-tools\\adb.exe");
}
else {
    // Linux: install via "sudo apt install android-tools-adb"
    define("ADB_PATH", "/usr/bin/adb");
}

define("ADB_PORT", "5555");

$CLEAR_APPS = [
    "com.google.android.youtube",
    "com.google.android.youtube.tv",
    "com.netflix.mediaclient",
    "com.netflix.ninja",
    "com.spotify.music",
    "com.spotify.tv.android",
    "in.startv.hotstar.dplus.tv",
    "com.vidio.android.tv",
    "com.ctcorp.hospitality"
];
