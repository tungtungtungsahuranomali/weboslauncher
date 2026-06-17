<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$db = init_db_connection();
if ($db === null) {
    echo "<div class='p-4 bg-red-100 text-red-700 rounded'>❌ Gagal koneksi database.</div>";
    return;
}

// Auto-create table if not exists
try {
    $db->exec("CREATE TABLE IF NOT EXISTS transportation_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        room_number VARCHAR(20) NOT NULL,
        guest_name VARCHAR(100) NOT NULL,
        pickup_point VARCHAR(255) DEFAULT '',
        destination VARCHAR(255) DEFAULT 'By Request',
        num_passengers INT DEFAULT 1,
        preferred_time VARCHAR(50) DEFAULT 'NOW',
        notes TEXT DEFAULT NULL,
        status ENUM('Pending','Completed','Cancelled') DEFAULT 'Pending',
        requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
} catch (Exception $e) {
    // table already exists
}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) { header('Location: ?page=transport_requests'); exit; }

    try {
        if (isset($_POST['selesai'])) {
            $db->prepare("UPDATE transportation_requests SET status='Completed' WHERE id=?")->execute([$id]);
        }
        if (isset($_POST['hapus'])) {
            $db->prepare("DELETE FROM transportation_requests WHERE id=?")->execute([$id]);
        }
    } catch (Exception $e) {}

    header('Location: ?page=transport_requests');
    exit;
}

$statusFilter = $_GET['status'] ?? '';
?>
<h1 class="text-2xl font-bold text-yellow-500 mb-6">🚐 Permintaan Transportasi</h1>

<div class="bg-white rounded-lg shadow p-6">
  <div class="flex justify-between items-center mb-4">
    <h2 class="text-lg font-semibold text-gray-700">Daftar Permintaan Masuk</h2>
    <div class="flex gap-2">
      <a href="?page=transport_requests" class="px-3 py-1.5 text-sm rounded <?= $statusFilter === '' ? 'bg-yellow-400 text-gray-900' : 'bg-gray-200' ?>">Semua</a>
      <a href="?page=transport_requests&status=Pending" class="px-3 py-1.5 text-sm rounded <?= $statusFilter === 'Pending' ? 'bg-yellow-400 text-gray-900' : 'bg-gray-200' ?>">Pending</a>
      <a href="?page=transport_requests&status=Completed" class="px-3 py-1.5 text-sm rounded <?= $statusFilter === 'Completed' ? 'bg-yellow-400 text-gray-900' : 'bg-gray-200' ?>">Selesai</a>
      <button onclick="location.reload()" class="px-4 py-2 bg-yellow-400 text-gray-900 font-semibold rounded hover:bg-yellow-500">🔄 Refresh</button>
    </div>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full border border-gray-200 text-sm">
      <thead class="bg-gray-100 text-gray-700">
        <tr>
          <th class="border px-3 py-2 text-left">#</th>
          <th class="border px-3 py-2 text-left">Waktu</th>
          <th class="border px-3 py-2 text-left">Kamar</th>
          <th class="border px-3 py-2 text-left">Tamu</th>
          <th class="border px-3 py-2 text-left">Tujuan</th>
          <th class="border px-3 py-2 text-center">Pnp</th>
          <th class="border px-3 py-2 text-left">Waktu</th>
          <th class="border px-3 py-2 text-left">Status</th>
          <th class="border px-3 py-2 text-left">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
        try {
            $sql = "SELECT * FROM transportation_requests";
            $params = [];
            if ($statusFilter !== '') {
                $sql .= " WHERE status=?";
                $params[] = $statusFilter;
            }
            $sql .= " ORDER BY id DESC";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

          if (!$requests) {
              echo "<tr><td colspan='9' class='text-center text-gray-500 py-4'>Belum ada permintaan transportasi.</td></tr>";
          } else {
              $no = 1;
              foreach ($requests as $req) {
                  $isPending = $req['status'] === 'Pending';
                  $timeLabel = $req['preferred_time'] === 'NOW' ? '🔴 Sekarang' : htmlspecialchars($req['preferred_time']);
                  echo "<tr class='hover:bg-gray-50'>";
                  echo "<td class='border px-3 py-2'>{$no}</td>";
                  echo "<td class='border px-3 py-2 text-gray-600'>{$req['requested_at']}</td>";
                  echo "<td class='border px-3 py-2 font-semibold'>{$req['room_number']}</td>";
                  echo "<td class='border px-3 py-2'>{$req['guest_name']}</td>";
                  echo "<td class='border px-3 py-2'>" . htmlspecialchars($req['destination']) . "</td>";
                  echo "<td class='border px-3 py-2 text-center'>{$req['num_passengers']}</td>";
                  echo "<td class='border px-3 py-2'>{$timeLabel}</td>";
                  echo "<td class='border px-3 py-2'>";
                  if ($isPending) {
                      echo "<span class='px-2 py-1 text-xs rounded bg-yellow-100 text-yellow-800'>Pending</span>";
                  } else {
                      echo "<span class='px-2 py-1 text-xs rounded bg-green-100 text-green-800'>Selesai</span>";
                  }
                  echo "</td>";
                  echo "<td class='border px-3 py-2'>
                          <form method='POST' class='flex gap-1'>";
                  echo "  <input type='hidden' name='id' value='{$req['id']}'>";
                  if ($isPending) {
                      echo "  <button type='submit' name='selesai' class='px-2 py-1 text-xs bg-green-500 text-white rounded hover:bg-green-600'>✓ Selesai</button>";
                  }
                  echo "  <button type='submit' name='hapus' class='px-2 py-1 text-xs bg-red-500 text-white rounded hover:bg-red-600' onclick=\"return confirm('Hapus?')\">🗑</button>
                          </form>
                        </td>";
                  echo "</tr>";
                  $no++;
              }
          }
        } catch (Exception $e) {
            echo "<tr><td colspan='9' class='text-center text-red-500 py-4'>Kesalahan DB: {$e->getMessage()}</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>
