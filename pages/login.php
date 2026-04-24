<?php

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../php-error.log');


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';


$db = init_db_connection();
if (!$db) {
    echo "<h2 style='color:red;text-align:center;margin-top:40vh;'>âŒ Database tidak dapat terhubung.<br>Periksa config.php.</h2>";
    exit;
}

$error = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Semua kolom wajib diisi.';
    } else {
        try {
            $stmt = $db->prepare("SELECT * FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password_hash'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_display_name'] = $admin['display_name'] ?? $admin['username'];
                $_SESSION['admin_role'] = $admin['role'] ?? 'admin';
                // Reset cached permissions
                unset($_SESSION['admin_permissions']);

                // Redirect ke dashboard menggunakan BASE_URL dari config.php
                header('Location: ' . rtrim(BASE_URL, '/') . '/admin.php?page=dashboard');
                exit;
            } else {
                $error = 'Username atau password salah.';
            }
        } catch (Exception $e) {
            $error = 'Terjadi kesalahan sistem. Coba lagi nanti.';
            error_log("Login Error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login Admin - TakeOff IPTV</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
body {
  font-family: 'Inter', sans-serif;
  background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #facc15 100%);
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
}
.card {
  background: rgba(15, 23, 42, 0.90);
  backdrop-filter: blur(18px);
  border: 1px solid rgba(148, 163, 184, 0.4);
  box-shadow: 0 4px 30px rgba(0,0,0,0.5);
}
input::placeholder { color: #9ca3af; }
.logo {
  width: 90px;
  height: 90px;
  object-fit: contain;
  margin: 0 auto 15px;
}
</style>
</head>

<body>
  <div class="w-full max-w-sm p-8 rounded-2xl shadow-2xl card text-center text-gray-100">
    <!-- LOGO -->
    <?php
    // cek file logo (path otomatis dari BASE_URL di config.php)
    $logo_fs  = __DIR__ . '/../img/logi.png';   // path di server
    $logo_web = rtrim(parse_url(BASE_URL, PHP_URL_PATH), '/') . '/img/logi.png'; // URL untuk browser

    if (file_exists($logo_fs)) {
        echo '<img src="' . $logo_web . '" alt="Logo Hotel" class="logo">';
    } else {
        echo '<div class="text-yellow-400 text-5xl mb-2">ðŸ¨</div>';
    }
    ?>

    <h1 class="text-3xl font-extrabold text-yellow-400 mb-1">TakeOff IPTV</h1>
    <p class="text-sm text-gray-300 mb-6">Hotel Admin Login</p>

    <?php if ($error): ?>
      <div class="bg-red-500/20 text-red-300 px-4 py-2 rounded mb-4 text-sm border border-red-500/40">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="mb-4 text-left">
        <label class="block text-gray-300 text-sm mb-1">Username</label>
        <input type="text" name="username" required placeholder="Masukkan username"
          class="w-full px-3 py-2 rounded-lg bg-gray-800/60 border border-gray-500 text-gray-100 focus:outline-none focus:ring-2 focus:ring-yellow-400">
      </div>

      <div class="mb-6 text-left">
        <label class="block text-gray-300 text-sm mb-1">Password</label>
        <input type="password" name="password" required placeholder="Masukkan password"
          class="w-full px-3 py-2 rounded-lg bg-gray-800/60 border border-gray-500 text-gray-100 focus:outline-none focus:ring-2 focus:ring-yellow-400">
      </div>

      <button type="submit"
        class="w-full py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-semibold rounded-lg shadow-lg transition">
        Login
      </button>
    </form>

    <p class="text-center text-gray-400 text-xs mt-6">Â© 2025 TakeOff IPTV â€¢ Powered by OgieTV</p>
  </div>
</body>
</html>
