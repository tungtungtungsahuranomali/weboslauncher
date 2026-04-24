<?php
// Muat config hanya untuk mengambil nilai default (jika ada)
// Ini opsional, Anda tetap harus mengisinya di form
@include 'config.php';
$default_user = defined('VHP_USER') ? VHP_USER : 'vhp_admin';
$default_pass = defined('VHP_PASS') ? VHP_PASS : 'PassHotelRahasia123!';
//$default_url  = defined('BASE_URL') ? BASE_URL : 'https://192.168.0.226/AHFix/';
$default_url = defined('BASE_URL') ? BASE_URL : 'http://localhost/AHFix/';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VHP API Simulator</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        input:disabled {
            background: #e5e7eb;
        }

        .tab-button.active {
            background-color: #facc15;
            color: #1f2937;
        }

        .tab-button {
            background-color: #374151;
            color: #d1d5db;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }
    </style>
</head>

<body class="bg-gray-900 text-gray-200 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-yellow-400 mb-2">VHP (PMS) API Simulator</h1>
        <p class="text-gray-400 mb-6">Gunakan alat ini untuk menguji endpoint `api.php` Anda (vhp_checkin &
            vhp_checkout).</p>

        <!-- HASIL RESPON -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-400 mb-1">Server Response:</label>
            <pre id="responseLog"
                class="bg-black text-white p-4 rounded-lg h-48 overflow-y-auto border border-gray-700 text-xs">Menunggu permintaan...</pre>
        </div>

        <form id="apiForm" class="bg-gray-800 p-6 rounded-lg shadow-xl border border-gray-700">
            <!-- 1. KREDENSIAL (Selalu Wajib) -->
            <fieldset class="border border-gray-600 p-4 rounded-lg">
                <legend class="px-2 text-lg font-semibold text-yellow-400">1. Kredensial & URL</legend>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="baseUrl" class="block text-sm font-medium mb-1">Base URL (dari config.php)</label>
                        <input type="text" id="baseUrl" value="<?= htmlspecialchars($default_url) ?>"
                            class="w-full p-2 rounded bg-gray-700 border border-gray-600 text-white" required>
                    </div>
                    <div>
                        <label for="vhpUser" class="block text-sm font-medium mb-1">VHP User (dari config.php)</label>
                        <input type="text" id="vhpUser" value="<?= htmlspecialchars($default_user) ?>"
                            class="w-full p-2 rounded bg-gray-700 border border-gray-600 text-white" required>
                    </div>
                    <div>
                        <label for="vhpPass" class="block text-sm font-medium mb-1">VHP Pass (dari config.php)</label>
                        <input type="password" id="vhpPass" value="<?= htmlspecialchars($default_pass) ?>"
                            class="w-full p-2 rounded bg-gray-700 border border-gray-600 text-white" required>
                    </div>
                </div>
            </fieldset>

            <!-- 2. PILIH AKSI -->
            <div class="my-6">
                <label class="block text-lg font-semibold text-yellow-400 mb-2">2. Pilih Aksi</label>
                <div class_="flex rounded-lg shadow-sm">
                    <button type="button" id="tabCheckin"
                        class="tab-button active w-1/2 p-3 rounded-l-lg font-semibold">Check-In Tamu</button>
                    <button type="button" id="tabCheckout"
                        class="tab-button w-1/2 p-3 rounded-r-lg font-semibold">Check-Out Tamu</button>
                </div>
            </div>

            <!-- 3. DATA CHECK-IN -->
            <fieldset id="contentCheckin" class="tab-content active border border-gray-600 p-4 rounded-lg">
                <legend class="px-2 text-lg font-semibold text-yellow-400">3. Data Check-In</legend>
                <p class="text-xs text-gray-400 mb-4">Akan dikirim ke endpoint `?action=vhp_checkin`.</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="ci_roomNo" class="block text-sm font-medium mb-1">Room No</label>
                        <input type="text" id="ci_roomNo" value="101"
                            class="w-full p-2 rounded bg-gray-700 border border-gray-600 text-white">
                    </div>
                    <div>
                        <label for="ci_title" class="block text-sm font-medium mb-1">Title</label>
                        <input type="text" id="ci_title" value="Mr."
                            class="w-full p-2 rounded bg-gray-700 border border-gray-600 text-white">
                    </div>
                    <div>
                        <label for="ci_firstName" class="block text-sm font-medium mb-1">First Name</label>
                        <input type="text" id="ci_firstName" value="Tamu"
                            class="w-full p-2 rounded bg-gray-700 border border-gray-600 text-white">
                    </div>
                    <div>
                        <label for="ci_lastName" class="block text-sm font-medium mb-1">Last Name</label>
                        <input type="text" id="ci_lastName" value="Simulasi"
                            class="w-full p-2 rounded bg-gray-700 border border-gray-600 text-white">
                    </div>
                </div>
            </fieldset>

            <!-- 4. DATA CHECK-OUT -->
            <fieldset id="contentCheckout" class="tab-content border border-gray-600 p-4 rounded-lg">
                <legend class="px-2 text-lg font-semibold text-yellow-400">3. Data Check-Out</legend>
                <p class="text-xs text-gray-400 mb-4">Akan dikirim ke endpoint `?action=vhp_checkout`.</p>
                <div>
                    <label for="co_roomNo" class="block text-sm font-medium mb-1">Room No</label>
                    <input type="text" id="co_roomNo" value="101"
                        class="w-full p-2 rounded bg-gray-700 border border-gray-600 text-white">
                </div>
            </fieldset>

            <!-- TOMBOL KIRIM -->
            <div class="mt-6">
                <button type="submit"
                    class="w-full p-4 bg-yellow-400 text-gray-900 font-bold rounded-lg hover:bg-yellow-500 transition-all">
                    KIRIM PERMINTAAN API
                </button>
            </div>
        </form>
    </div>

    <script>
        const tabCheckin = document.getElementById('tabCheckin');
        const tabCheckout = document.getElementById('tabCheckout');
        const contentCheckin = document.getElementById('contentCheckin');
        const contentCheckout = document.getElementById('contentCheckout');
        const apiForm = document.getElementById('apiForm');
        const responseLog = document.getElementById('responseLog');
        let currentAction = 'vhp_checkin';

        // Logika Tabbing
        tabCheckin.addEventListener('click', () => {
            currentAction = 'vhp_checkin';
            tabCheckin.classList.add('active');
            tabCheckout.classList.remove('active');
            contentCheckin.classList.add('active');
            contentCheckout.classList.remove('active');
        });
        tabCheckout.addEventListener('click', () => {
            currentAction = 'vhp_checkout';
            tabCheckout.classList.add('active');
            tabCheckin.classList.remove('active');
            contentCheckout.classList.add('active');
            contentCheckin.classList.remove('active');
        });

        // Logika Submit Form
        apiForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            responseLog.textContent = 'Mengirim...';

            // 1. Ambil Kredensial
            const baseUrl = document.getElementById('baseUrl').value;
            const username = document.getElementById('vhpUser').value;
            const password = document.getElementById('vhpPass').value;

            // 2. Buat Authorization Header (Basic Auth)
            const authHeader = 'Basic ' + btoa(username + ':' + password);

            // 3. Tentukan Endpoint & Payload
            let endpoint = '';
            let payload = {};

            if (currentAction === 'vhp_checkin') {
                endpoint = baseUrl + 'api.php?action=vhp_checkin';
                payload = {
                    roomNo: document.getElementById('ci_roomNo').value,
                    title: document.getElementById('ci_title').value,
                    firstName: document.getElementById('ci_firstName').value,
                    lastName: document.getElementById('ci_lastName').value,
                    // Tambahkan data dummy lain jika diperlukan API VHP
                    nation: "IDN",
                    gender: "Male",
                    birthDate: "01/01/2000",
                    checkinDate: new Date().toISOString(),
                    checkoutDate: new Date(Date.now() + 86400000).toISOString()
                };
            } else {
                endpoint = baseUrl + 'api.php?action=vhp_checkout';
                payload = {
                    roomNo: document.getElementById('co_roomNo').value
                };
            }

            // 4. Kirim request menggunakan Fetch
            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': authHeader
                    },
                    body: JSON.stringify(payload)
                });

                // Tampilkan hasil mentah (raw)
                const responseText = await response.text();

                // Coba format sebagai JSON jika bisa
                try {
                    const jsonResponse = JSON.parse(responseText);
                    responseLog.textContent = `STATUS: ${response.status}\n\n` + JSON.stringify(jsonResponse, null, 2);
                } catch (jsonError) {
                    // Tampilkan sebagai teks biasa jika bukan JSON (misal: error 404 atau 500)
                    responseLog.textContent = `STATUS: ${response.status}\n\n` + responseText;
                }

            } catch (error) {
                responseLog.textContent = 'Koneksi Gagal (Network Error).\nPastikan Base URL sudah benar dan server `api.php` bisa diakses.\n\n' + error.message;
            }
        });
    </script>
</body>

</html>