<?php
ob_start();

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';



$page = $_GET['page'] ?? 'dashboard';

// ===== AJAX passthrough (no layout HTML) =====
// Untuk request XHR (mis. upload/progress flashscreen) kita perlu response JSON murni,
// jadi jangan render header/sidebar/main layout.
if (($_GET['ajax'] ?? '') === '1' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $ajaxPage = $page;
    $file = __DIR__ . "/pages/{$ajaxPage}.php";
    if (file_exists($file)) {
        include $file;
    } else {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(404);
        echo json_encode(['ok' => false, 'message' => 'Halaman AJAX tidak ditemukan.']);
    }
    exit;
}


if ($page === 'logout') {
    session_destroy();
    header('Location: ?page=login');
    exit;
}


if (!in_array($page, ['login', 'register'])) {
    require_admin_login();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_marquee') {
    $text = trim($_POST['marquee_text'] ?? '');
    $db = init_db_connection();

    if ($db) {
        $stmt = $db->prepare("INSERT INTO system_marquee (id, content) VALUES (1, ?) 
                              ON DUPLICATE KEY UPDATE content = VALUES(content)");
        $ok = $stmt->execute([$text]);

        if ($ok) {
            flash('success', 'Teks berjalan berhasil disimpan.');
        } else {
            flash('error', 'Gagal menyimpan teks berjalan.');
        }
    } else {
        flash('error', 'Koneksi database gagal.');
    }

    header('Location: ?page=running_text');
    exit;
}

// ====== UBAH PASSWORD ======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'change_password') {
    $old_pass = trim($_POST['old_password'] ?? '');
    $new_pass = trim($_POST['new_password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');
    $admin_id = $_SESSION['admin_id'] ?? 0;

    if ($old_pass === '' || $new_pass === '' || $confirm === '') {
        flash('error', 'Semua kolom password wajib diisi.');
    } elseif (strlen($new_pass) < 4) {
        flash('error', 'Password baru minimal 4 karakter.');
    } elseif ($new_pass !== $confirm) {
        flash('error', 'Konfirmasi password baru tidak cocok.');
    } else {
        $db = init_db_connection();
        if ($db && $admin_id) {
            $stmt = $db->prepare("SELECT password_hash FROM admins WHERE id = ?");
            $stmt->execute([$admin_id]);
            $row = $stmt->fetch();

            if ($row && password_verify($old_pass, $row['password_hash'])) {
                $new_hash = password_hash($new_pass, PASSWORD_BCRYPT);
                $stmt2 = $db->prepare("UPDATE admins SET password_hash = ? WHERE id = ?");
                if ($stmt2->execute([$new_hash, $admin_id])) {
                    flash('success', 'Password berhasil diubah.');
                } else {
                    flash('error', 'Gagal menyimpan password baru.');
                }
            } else {
                flash('error', 'Password lama salah.');
            }
        } else {
            flash('error', 'Koneksi database gagal.');
        }
    }

    header('Location: ?page=' . ($_GET['page'] ?? 'dashboard'));
    exit;
}


$success = flash('success');
$error = flash('error');

$admin_user = $_SESSION['admin_display_name'] ?? $_SESSION['admin_username'] ?? 'Administrator';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Hotel IPTV</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Typography base (mirip referensi) */
        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            font-size: 14px;
            color: #111827;
        }

        h1,
        h2,
        h3 {
            letter-spacing: 0.01em;
        }

        h1 {
            font-size: 18px;
        }

        /* Sidebar link style */
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            border-radius: 8px;
            font-size: 14px;
            color: #9ca3af;
            transition: all 0.15s ease;
        }

        .sidebar-link:hover {
            background: #1f2937;
            color: #e5e7eb;
        }

        .sidebar-link.active {
            background: #eab308;
            color: #111827;
            font-weight: 600;
        }

        .sidebar-group-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 12px 16px 8px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #94a3b8; /* Brighter color for visibility */
            font-weight: 400; /* Bolder text */
            cursor: pointer;
            transition: all 0.15s ease;
            background: transparent;
            border: none;
            text-align: left;
        }

        .sidebar-group-header:hover {
            color: #f8fafc;
        }

        .sidebar-group-header .header-label {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-group-content {
            display: none;
            padding-left: 20px;
        }

        .sidebar-group.open .sidebar-group-content {
            display: block;
        }

        .sidebar-group-header .chevron-icon {
            transition: transform 0.2s ease;
            color: #4b5563;
        }

        .sidebar-group.open .sidebar-group-header .chevron-icon {
            transform: rotate(180deg);
        }

        /* Hide scrollbar but keep scroll */
        .sidebar-nav::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: #374151;
            border-radius: 4px;
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen">

    <?php if (is_admin_logged_in()): ?>
        <!-- Top Header Bar -->
        <header id="topHeader"
            class="fixed top-0 left-0 md:left-64 right-0 h-14 bg-white/90 backdrop-blur border-b border-gray-200 z-40 flex items-center justify-between px-4 md:px-6">
            <div class="flex items-center gap-3">
                <!-- Hamburger (mobile & desktop) -->
                <button type="button"
                    class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-yellow-400"
                    onclick="toggleSidebar()">
                    <span class="sr-only">Toggle navigation</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
                <h1 class="text-base md:text-lg font-semibold text-gray-800">
                    <?php
                    $page_titles = [
                        'dashboard' => '📊 Dashboard',
                        'devices' => '📱 Perangkat',
                        'checkin' => '🔑 Manajemen Check-In',
                        'send_notification' => '🔔 Notifikasi',
                        'kat_facilities' => '📂 Kategori Facilities',
                        'facilities' => '🏢 Facilities',
                        'kat_general_info' => '📂 Kategori General Info',
                        'general_info' => 'ℹ️ General Information',
                        'kat_promotion' => '📂 Kategori Promotion',
                        'promotion' => '🚀 Promotion',
                        'amenities' => '🧴 Amenities',
                        'kat_info' => '📂 Kategori Information',
                        'information' => 'ℹ️ Information',
                        'kat_dining' => '📂 Kategori Dining',
                        'dining' => '🍽️ Dining Menu',
                        'dining_orders' => '📋 Pesanan Dining',
                        'amenity_requests' => '📦 Permintaan Amenities',
                        'app_control' => '📺 Entertainment Apps',
                        'running_text' => '📝 Running Text',

                        'flashscreen' => '🖼️ Flashscreen',
                        'server_config' => '⚙️ Konfigurasi',
                        'users' => '👥 Manajemen Pengguna',
                        'iptv' => '📺 IPTV Channel',
                        'units' => '🧩 Master Unit',
                    ];
                    echo $page_titles[$page] ?? 'Admin Panel';
                    ?>
                </h1>
            </div>
            <div class="flex items-center gap-2 md:gap-4">
                <span class="text-sm text-gray-500">👤 <?= htmlspecialchars($admin_user) ?></span>
                <button type="button" onclick="document.getElementById('changePwdModal').classList.add('active')"
                    class="hidden sm:inline-flex text-sm bg-blue-50 text-blue-600 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition">
                    🔑 Ubah Password
                </button>
                <a href="?page=logout"
                    class="text-sm bg-red-50 text-red-600 hover:bg-red-100 px-3 md:px-4 py-1.5 rounded-lg transition">Logout</a>
            </div>
        </header>

        <div class="flex pt-14 h-screen">
            <!-- Sidebar -->
            <aside id="sidebar"
                class="w-64 bg-gray-900/95 text-gray-300 flex flex-col fixed top-0 bottom-0 z-40 transform translate-x-0 transition-transform duration-200 ease-out">
                <!-- Branding: perfectly align with header bar text -->
                <div class="h-14 flex flex-col justify-center px-5 flex-shrink-0 pt-1">
                    <h2 class="text-[1.1rem] font-bold text-yellow-400 m-0 p-0 leading-none">TakeOff IPTV</h2>
                    <span class="text-[0.65rem] text-gray-500 m-0 p-0 mt-1 leading-none">Hotel Management System</span>
                </div>
                <nav class="flex-grow px-3 pb-3 space-y-0.5 overflow-y-auto sidebar-nav">
                    <a href="?page=dashboard" class="sidebar-link <?= ($page === 'dashboard') ? 'active' : '' ?>" style="margin-top:12px">📊
                        DASHBOARD</a>

                    <!-- UTAMA -->
                    <div class="sidebar-group" id="group-utama">
                        <button class="sidebar-group-header" onclick="toggleSidebarGroup('utama')">
                            <div class="header-label">
                                <span>🏠 UTAMA</span>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 chevron-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="sidebar-group-content">
                            <?php if (has_permission('units')): ?>
                                <a href="?page=units" class="sidebar-link <?= ($page === 'units') ? 'active' : '' ?>">🧩
                                    Master Unit</a>
                            <?php endif; ?>
                            <?php if (has_permission('devices')): ?>
                                <a href="?page=devices" class="sidebar-link <?= ($page === 'devices') ? 'active' : '' ?>">📦 Perangkat</a>
                            <?php endif; ?>
                            <?php if (has_permission('checkin')): ?>
                                <a href="?page=checkin" class="sidebar-link <?= ($page === 'checkin') ? 'active' : '' ?>">🔑 Check-In /
                                    Out</a>
                            <?php endif; ?>
                            <?php if (has_permission('send_notification')): ?>
                                <a href="?page=send_notification"
                                    class="sidebar-link <?= ($page === 'send_notification') ? 'active' : '' ?>">🔔 Notifikasi</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- KONTEN HOTEL -->
                    <div class="sidebar-group" id="group-konten">
                        <button class="sidebar-group-header" onclick="toggleSidebarGroup('konten')">
                            <div class="header-label">
                                <span>🏨 KONTEN HOTEL</span>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 chevron-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="sidebar-group-content">
                            <?php if (has_permission('kat_facilities')): ?>
                                <a href="?page=kat_facilities" class="sidebar-link <?= ($page === 'kat_facilities') ? 'active' : '' ?>">📂
                                    Kategori Facilities</a>
                            <?php endif; ?>
                            <?php if (has_permission('facilities')): ?>
                                <a href="?page=facilities" class="sidebar-link <?= ($page === 'facilities') ? 'active' : '' ?>">🏢
                                    Facilities</a>
                            <?php endif; ?>
                            <?php if (has_permission('kat_general_info')): ?>
                                <a href="?page=kat_general_info" class="sidebar-link <?= ($page === 'kat_general_info') ? 'active' : '' ?>">📂
                                    Kategori General Info</a>
                            <?php endif; ?>
                            <?php if (has_permission('general_info')): ?>
                                <a href="?page=general_info" class="sidebar-link <?= ($page === 'general_info') ? 'active' : '' ?>">ℹ️
                                    General Information</a>
                            <?php endif; ?>
                            <?php if (has_permission('kat_promotion')): ?>
                                <a href="?page=kat_promotion" class="sidebar-link <?= ($page === 'kat_promotion') ? 'active' : '' ?>">📂
                                    Kategori Promotion</a>
                            <?php endif; ?>
                            <?php if (has_permission('promotion')): ?>
                                <a href="?page=promotion" class="sidebar-link <?= ($page === 'promotion') ? 'active' : '' ?>">🚀
                                    Promotion</a>
                            <?php endif; ?>
                            <?php if (has_permission('amenities')): ?>
                                <a href="?page=amenities" class="sidebar-link <?= ($page === 'amenities') ? 'active' : '' ?>">📦
                                    Amenities</a>
                            <?php endif; ?>
                            <?php if (has_permission('kat_info')): ?>
                                <a href="?page=kat_info" class="sidebar-link <?= ($page === 'kat_info') ? 'active' : '' ?>">📂
                                    Kategori Information</a>
                            <?php endif; ?>
                            <?php if (has_permission('information')): ?>
                                <a href="?page=information" class="sidebar-link <?= ($page === 'information') ? 'active' : '' ?>">ℹ️
                                    Information</a>
                            <?php endif; ?>
                            <?php if (has_permission('kat_dining')): ?>
                                <a href="?page=kat_dining" class="sidebar-link <?= ($page === 'kat_dining') ? 'active' : '' ?>">📂
                                    Kategori Dining</a>
                            <?php endif; ?>
                            <?php if (has_permission('dining')): ?>
                                <a href="?page=dining" class="sidebar-link <?= ($page === 'dining') ? 'active' : '' ?>">🍽️ Dining
                                    Menu</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- PESANAN TAMU -->
                    <div class="sidebar-group" id="group-pesanan">
                        <button class="sidebar-group-header" onclick="toggleSidebarGroup('pesanan')">
                            <div class="header-label">
                                <span>📋 PESANAN TAMU</span>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 chevron-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="sidebar-group-content">
                            <?php if (has_permission('dining_orders')): ?>
                                <a href="?page=dining_orders" class="sidebar-link <?= ($page === 'dining_orders') ? 'active' : '' ?>">📋
                                    Pesanan Dining</a>
                            <?php endif; ?>
                            <?php if (has_permission('amenity_requests')): ?>
                                <a href="?page=amenity_requests"
                                    class="sidebar-link <?= ($page === 'amenity_requests') ? 'active' : '' ?>">📦 Permintaan
                                    Amenities</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- SYSTEM -->
                    <div class="sidebar-group" id="group-system">
                        <button class="sidebar-group-header" onclick="toggleSidebarGroup('system')">
                            <div class="header-label">
                                <span>⚙️ SYSTEM</span>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 chevron-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div class="sidebar-group-content">
                            <?php if (has_permission('app_control')): ?>
                                <a href="?page=app_control" class="sidebar-link <?= ($page === 'app_control') ? 'active' : '' ?>">📺
                                    Entertainment Apps</a>
                            <?php endif; ?>
                            <?php if (has_permission('running_text')): ?>
                                <a href="?page=running_text" class="sidebar-link <?= ($page === 'running_text') ? 'active' : '' ?>">📝
                                    Running Text</a>
                            <?php endif; ?>
                            <?php if (has_permission('flashscreen')): ?>
                                <a href="?page=flashscreen" class="sidebar-link <?= ($page === 'flashscreen') ? 'active' : '' ?>">🖼️
                                    Flashscreen</a>
                            <?php endif; ?>
                            <?php if (has_permission('server_config')): ?>
                                <a href="?page=server_config" class="sidebar-link <?= ($page === 'server_config') ? 'active' : '' ?>">⚙️
                                    Konfigurasi</a>
                            <?php endif; ?>
                            <?php if (has_permission('iptv')): ?>
                                <a href="?page=iptv" class="sidebar-link <?= ($page === 'iptv') ? 'active' : '' ?>">📺
                                    IPTV Channel</a>
                            <?php endif; ?>
                            <?php if (has_permission('users')): ?>
                                <a href="?page=users" class="sidebar-link <?= ($page === 'users') ? 'active' : '' ?>">👥
                                    Pengguna</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </nav>
            </aside>

            <!-- Main Content -->
            <main id="mainContent" class="flex-grow p-4 md:p-8 pb-32 overflow-y-auto">
                <?php if ($success): ?>
                    <div
                        class="bg-green-50 border border-green-200 text-green-700 px-5 py-3 rounded-lg mb-6 flex items-center gap-2">
                        <span>✅</span> <?= $success ?>
                    </div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-3 rounded-lg mb-6 flex items-center gap-2">
                        <span>❌</span> <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <?php
                $allowed_pages = [
                    'dashboard',
                    'devices',
                    'checkin',
                    'kat_facilities',
                    'facilities',
                    'kat_general_info',
                    'general_info',
                    'kat_promotion',
                    'promotion',
                    'amenities',
                    'kat_info',
                    'information',
                    'kat_dining',
                    'dining',
                    'dining_orders',
                    'amenity_requests',
                    'app_control',
                    'running_text',

                    'flashscreen',
                    'login',
                    'register',
                    'send_notification',
                    'server_config',
                    'users',
                    'iptv',
                    'units'
                ];

                if (in_array($page, $allowed_pages)) {
                    // Cek permission akses halaman
                    if (!has_permission($page)) {
                        echo "<div class='text-center py-20'>
                            <p class='text-6xl mb-4'>🔒</p>
                            <p class='text-gray-500 text-lg'>Anda tidak memiliki akses ke halaman <b>{$page}</b>.</p>
                            <a href='?page=dashboard' class='inline-block mt-4 px-6 py-2 bg-yellow-400 text-gray-900 rounded-lg font-medium hover:bg-yellow-500 transition'>Kembali ke Dashboard</a>
                        </div>";
                    } else {
                        $file = __DIR__ . "/pages/{$page}.php";
                        if (file_exists($file)) {
                            include $file;
                        } else {
                            echo "<div class='text-center py-20'>
                                <p class='text-6xl mb-4'>🚧</p>
                                <p class='text-gray-500 text-lg'>Halaman <b>{$page}</b> belum dibuat.</p>
                            </div>";
                        }
                    }
                } else {
                    echo "<div class='text-center py-20'>
                        <p class='text-6xl mb-4'>⚠️</p>
                        <p class='text-gray-500 text-lg'>Halaman tidak dikenal.</p>
                    </div>";
                }
                ?>
            </main>
        </div>

    <?php else: ?>
        <?php
        $auth_page = ($page === 'register') ? 'register' : 'login';
        include __DIR__ . "/pages/{$auth_page}.php";
        ?>
    <?php endif; ?>

    <?php if (is_admin_logged_in()): ?>
        <!-- Modal Ubah Password -->
        <div class="modal-overlay" id="changePwdModal"
            style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:100;align-items:center;justify-content:center;">
            <div
                style="background:white;border-radius:16px;padding:28px;width:100%;max-width:420px;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
                <h3 style="font-size:18px;font-weight:600;color:#1f2937;margin-bottom:4px;">🔑 Ubah Password</h3>
                <p style="font-size:13px;color:#9ca3af;margin-bottom:20px;">Masukkan password lama dan password baru Anda
                </p>
                <form method="POST" action="?page=<?= htmlspecialchars($page) ?>" autocomplete="off">
                    <input type="hidden" name="action" value="change_password">
                    <div style="margin-bottom:14px;">
                        <label
                            style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;">Password
                            Lama</label>
                        <input type="password" name="old_password" required placeholder="Masukkan password lama"
                            style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box;">
                    </div>
                    <div style="margin-bottom:14px;">
                        <label
                            style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;">Password
                            Baru</label>
                        <input type="password" name="new_password" required placeholder="Minimal 4 karakter" minlength="4"
                            style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box;">
                    </div>
                    <div style="margin-bottom:20px;">
                        <label
                            style="display:block;font-size:13px;font-weight:500;color:#374151;margin-bottom:6px;">Konfirmasi
                            Password Baru</label>
                        <input type="password" name="confirm_password" required placeholder="Ulangi password baru"
                            style="width:100%;padding:10px 14px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;outline:none;box-sizing:border-box;">
                    </div>
                    <div style="display:flex;gap:10px;justify-content:flex-end;">
                        <button type="button"
                            onclick="document.getElementById('changePwdModal').classList.remove('active');document.getElementById('changePwdModal').style.display='none';"
                            style="padding:8px 16px;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;background:#f3f4f6;color:#374151;border:1px solid #d1d5db;">Batal</button>
                        <button type="submit"
                            style="padding:8px 16px;border-radius:8px;font-size:13px;font-weight:500;cursor:pointer;background:#eab308;color:#111827;border:none;">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
        <script>
            (function () {
                var modal = document.getElementById('changePwdModal');
                var pwdBtn = document.querySelector('[onclick*="changePwdModal"]');
                if (modal) {
                    modal.addEventListener('click', function (e) {
                        if (e.target === this) {
                            this.style.display = 'none';
                            this.classList.remove('active');
                        }
                    });
                    if (pwdBtn) {
                        pwdBtn.addEventListener('click', function () {
                            modal.style.display = 'flex';
                        });
                    }
                }

                // Set initial layout based on screen size
                var sidebar = document.getElementById('sidebar');
                var main = document.getElementById('mainContent');
                var header = document.getElementById('topHeader');
                if (sidebar && main && header) {
                    if (window.innerWidth >= 768) {
                        // Desktop: sidebar terlihat, content bergeser
                        main.classList.add('md:ml-64');
                        sidebar.classList.remove('-translate-x-full');
                        header.style.left = '16rem'; // 64px * 4 (Tailwind md:left-64)
                    } else {
                        // Mobile: sidebar disembunyikan awalnya
                        main.classList.remove('md:ml-64');
                        sidebar.classList.add('-translate-x-full');
                        header.style.left = '0';
                    }
                }

                window.addEventListener('resize', function () {
                    var sidebar = document.getElementById('sidebar');
                    var main = document.getElementById('mainContent');
                    var header = document.getElementById('topHeader');
                    if (!sidebar || !main || !header) return;
                    if (window.innerWidth >= 768) {
                        // Kembali ke mode desktop: kalau sidebar tidak disembunyikan, content bergeser
                        if (!sidebar.classList.contains('-translate-x-full')) {
                            main.classList.add('md:ml-64');
                            header.style.left = '16rem';
                        }
                    } else {
                        // Mode mobile: content full width, sidebar off-canvas
                        main.classList.remove('md:ml-64');
                        header.style.left = '0';
                        if (!sidebar.classList.contains('-translate-x-full')) {
                            sidebar.classList.add('-translate-x-full');
                        }
                    }
                });
            })();

            function toggleSidebar() {
                var sidebar = document.getElementById('sidebar');
                var main = document.getElementById('mainContent');
                var header = document.getElementById('topHeader');
                if (!sidebar || !main || !header) return;

                // Toggle posisi sidebar
                var isHidden = sidebar.classList.contains('-translate-x-full');
                if (isHidden) {
                    sidebar.classList.remove('-translate-x-full');
                    // Kalau desktop, geser content ke kanan
                    if (window.innerWidth >= 768) {
                        main.classList.add('md:ml-64');
                        header.style.left = '16rem';
                    }
                } else {
                    sidebar.classList.add('-translate-x-full');
                    // Kalau desktop, lebarkan content
                    if (window.innerWidth >= 768) {
                        main.classList.remove('md:ml-64');
                        header.style.left = '0';
                    }
                }
            }

            function toggleSidebarGroup(groupId) {
                var group = document.getElementById('group-' + groupId);
                if (group) {
                    group.classList.toggle('open');
                }
            }

            // Auto-expand group if child is active
            document.addEventListener('DOMContentLoaded', function() {
                var activeLink = document.querySelector('.sidebar-group-content .sidebar-link.active');
                if (activeLink) {
                    var group = activeLink.closest('.sidebar-group');
                    if (group) {
                        group.classList.add('open');
                    }
                }
            });
        </script>
    <?php endif; ?>

</body>

</html>