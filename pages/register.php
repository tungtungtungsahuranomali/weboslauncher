<?php

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$db = init_db_connection();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm'] ?? '');

    if ($username === '' || $password === '' || $confirm === '') {
        $message = '<div class="bg-red-500/20 text-red-300 px-4 py-2 rounded mb-4 text-sm border border-red-500/40">
                        Semua kolom wajib diisi.
                    </div>';
    } elseif ($password !== $confirm) {
        $message = '<div class="bg-red-500/20 text-red-300 px-4 py-2 rounded mb-4 text-sm border border-red-500/40">
                        Konfirmasi password tidak cocok.
                    </div>';
    } else {
        $stmt = $db->prepare("SELECT COUNT(*) FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            $message = '<div class="bg-yellow-500/20 text-yellow-300 px-4 py-2 rounded mb-4 text-sm border border-yellow-500/40">
                            Username sudah digunakan.
                        </div>';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $db->prepare("INSERT INTO admins (username, password_hash, created_at) VALUES (?, ?, NOW())");
            if ($stmt->execute([$username, $hash])) {
                $message = '<div class="bg-green-500/20 text-green-300 px-4 py-2 rounded mb-4 text-sm border border-green-500/40">
                                Akun berhasil dibuat. Silakan login.
                            </div>';
            } else {
                $message = '<div class="bg-red-500/20 text-red-300 px-4 py-2 rounded mb-4 text-sm border border-red-500/40">
                                Terjadi kesalahan saat menyimpan data.
                            </div>';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registrasi Admin - TakeOff IPTV</title>
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
  background: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(20px);
  border: 1px solid rgba(255,255,255,0.2);
}
input::placeholder {
  color: #9ca3af;
}
.logo {
  width: 80px;
  height: 80px;
  object-fit: contain;
  margin: 0 auto 10px;
}
</style>
</head>

<body>
  <div class="w-full max-w-sm p-8 rounded-2xl shadow-2xl card text-center">
    <!-- LOGO -->
    <?php
    $logo_path = "../img/logo.png";
    if (file_exists(__DIR__ . '/../img/logo.png')) {
        echo '<img src="' . $logo_path . '" alt="Logo" class="logo">';
    } else {
        echo '<div class="text-yellow-400 text-5xl mb-2">🏨</div>';
    }
    ?>

    <h1 class="text-3xl font-extrabold text-yellow-400">TakeOff IPTV</h1>
    <p class="text-sm text-gray-200 mb-6">Registrasi Admin</p>

    <?= $message ?>

    <form method="POST" action="">
      <div class="mb-4 text-left">
        <label class="block text-gray-300 text-sm mb-1">Username</label>
        <input type="text" name="username" required placeholder="Buat username"
          class="w-full px-3 py-2 rounded-lg bg-gray-800/50 border border-gray-600 text-gray-100 focus:outline-none focus:ring-2 focus:ring-yellow-400">
      </div>

      <div class="mb-4 text-left">
        <label class="block text-gray-300 text-sm mb-1">Password</label>
        <input type="password" name="password" required placeholder="Buat password"
          class="w-full px-3 py-2 rounded-lg bg-gray-800/50 border border-gray-600 text-gray-100 focus:outline-none focus:ring-2 focus:ring-yellow-400">
      </div>

      <div class="mb-6 text-left">
        <label class="block text-gray-300 text-sm mb-1">Konfirmasi Password</label>
        <input type="password" name="confirm" required placeholder="Ulangi password"
          class="w-full px-3 py-2 rounded-lg bg-gray-800/50 border border-gray-600 text-gray-100 focus:outline-none focus:ring-2 focus:ring-yellow-400">
      </div>

      <button type="submit"
        class="w-full py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-800 rounded-lg transition duration-300 ease-in-out">Buat Akun</button>
    </form>
  </div>
</body>

</html>