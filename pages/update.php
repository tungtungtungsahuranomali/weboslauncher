<?php
if (!isset($db)) {
    require_once __DIR__ . '/../config.php';
    $db = init_db_connection();
}


// Ambil versi sistem saat ini
$current_version = 'v12.11-multipage';
try {
    $stmt = $db->prepare("SELECT setting_value FROM global_settings WHERE setting_key = 'system_version'");
    $stmt->execute();
    $res = $stmt->fetchColumn();
    if ($res) $current_version = $res;
} catch (Exception $e) {
    // Lewati jika tabel belum ada
}
?>

<div class="max-w-3xl mx-auto">
    <h1 class="text-3xl font-bold text-yellow-500 mb-6 flex items-center gap-2">
        <svg class="w-8 h-8 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4l3 3m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        System Update
    </h1>

    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold mb-2 text-gray-800">Current Version</h2>
        <p class="text-gray-600 mb-4">Installed Launcher Version: 
            <b class="text-green-600"><?=htmlspecialchars($current_version)?></b>
        </p>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold mb-4 text-gray-800">Upload New Update (.apk)</h2>
        <form id="updateForm" enctype="multipart/form-data">
            <input type="file" name="update_file" id="update_file"
                   accept=".apk" required
                   class="block w-full border border-gray-300 rounded p-2 mb-4" />

            <button type="submit"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-5 py-2 rounded font-semibold">
                Upload & Install
            </button>
        </form>

        <div id="progress-wrapper" class="hidden mt-4">
            <div class="w-full bg-gray-200 rounded-full h-4">
                <div id="progress-bar" class="bg-green-500 h-4 rounded-full w-0 transition-all duration-300"></div>
            </div>
            <p id="progress-text" class="text-sm text-gray-600 mt-2">Uploading...</p>
        </div>

        <div id="upload-result" class="mt-4 text-sm"></div>

        <hr class="my-6 border-gray-300">

        <button id="pushUpdateBtn"
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded font-semibold w-full">
            🚀 Push Update ke Semua Perangkat
        </button>

        <div id="push-result" class="mt-4 text-sm"></div>
    </div>
</div>

<script>
document.getElementById('updateForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const fileInput = document.getElementById('update_file');
    if (!fileInput.files.length) {
        alert("Pilih file APK terlebih dahulu.");
        return;
    }

    const formData = new FormData();
    formData.append('update_file', fileInput.files[0]);

    const progressWrapper = document.getElementById('progress-wrapper');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const resultBox = document.getElementById('upload-result');

    progressWrapper.classList.remove('hidden');
    progressBar.style.width = '0%';
    progressText.textContent = 'Uploading...';
    resultBox.textContent = '';

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'upload_update.php', true);

    xhr.upload.addEventListener('progress', (event) => {
        if (event.lengthComputable) {
            const percent = Math.round((event.loaded / event.total) * 100);
            progressBar.style.width = percent + '%';
            progressText.textContent = 'Uploading... ' + percent + '%';
        }
    });

    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.status === 'success') {
                    progressBar.classList.replace('bg-green-500', 'bg-blue-500');
                    progressText.textContent = 'Upload Complete. Installing...';
                    resultBox.innerHTML = `<p class="text-green-600 font-semibold mt-2">✅ ${response.message}</p>`;
                } else {
                    resultBox.innerHTML = `<p class="text-red-600 font-semibold mt-2">❌ ${response.message}</p>`;
                }
            } catch (err) {
                resultBox.innerHTML = `<p class="text-red-600 font-semibold">❌ Invalid JSON response.</p>`;
            }
        } else {
            resultBox.innerHTML = `<p class="text-red-600 font-semibold">❌ Server Error: ${xhr.status}</p>`;
        }
    };

    xhr.onerror = function() {
        resultBox.innerHTML = `<p class="text-red-600 font-semibold">⚠️ Network Error. Try again.</p>`;
    };

    xhr.send(formData);
});

document.getElementById('pushUpdateBtn').addEventListener('click', async function() {
    const btn = this;
    btn.disabled = true;
    btn.textContent = "Processing...";
    const resultBox = document.getElementById('push-result');
    resultBox.innerHTML = "";

    try {
        const res = await fetch('api.php?action=pushUpdate');
        const data = await res.json();
        if (data.status === 'success') {
            resultBox.innerHTML = `<p class="text-green-600 font-semibold mt-2">✅ ${data.message}</p>`;
        } else {
            resultBox.innerHTML = `<p class="text-red-600 font-semibold mt-2">❌ ${data.message}</p>`;
        }
    } catch (err) {
        resultBox.innerHTML = `<p class="text-red-600 font-semibold mt-2">⚠️ ${err.message}</p>`;
    }

    btn.disabled = false;
    btn.textContent = "🚀 Push Update ke Semua Perangkat";
});
</script>