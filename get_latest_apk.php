<?php
$directory = "uploads/update"; // Folder tempat file APK disimpan
$files = array_diff(scandir($directory), array('..', '.')); // Mengambil semua file, kecuali '.' dan '..'
usort($files, function($a, $b) use ($directory) {
    return filemtime($directory . "/" . $b) - filemtime($directory . "/" . $a); // Mengurutkan berdasarkan waktu modifikasi file
});

// Mengambil file APK terbaru
$latest_apk = $files[0];
echo json_encode(array('latest_apk' => $latest_apk));
?>
