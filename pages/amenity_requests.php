<?php

require_once __DIR__ . '/../config.php';

$db = init_db_connection();
if ($db === null) {
    echo "<div class='p-4 bg-red-100 text-red-700 rounded'>❌ Gagal koneksi database.</div>";
    return;
}
?>

<h1 class="text-2xl font-bold text-yellow-500 mb-6">📦 Permintaan Amenities</h1>

<div class="bg-white rounded-lg shadow p-6">
  <div class="flex justify-between items-center mb-4">
    <h2 class="text-lg font-semibold text-gray-700">Daftar Permintaan Masuk</h2>
    <button onclick="location.reload()" class="px-4 py-2 bg-yellow-400 text-gray-900 font-semibold rounded hover:bg-yellow-500">🔄 Refresh</button>
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full border border-gray-200 text-sm">
      <thead class="bg-gray-100 text-gray-700">
        <tr>
          <th class="border px-3 py-2 text-left">#</th>
          <th class="border px-3 py-2 text-left">Waktu</th>
          <th class="border px-3 py-2 text-left">Kamar</th>
          <th class="border px-3 py-2 text-left">Nama Tamu</th>
          <th class="border px-3 py-2 text-left">Permintaan</th>
          <th class="border px-3 py-2 text-left">Status</th>
          <th class="border px-3 py-2 text-left">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php
        try {
          $stmt = $db->query("SELECT * FROM amenity_requests ORDER BY id DESC");
          $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

          if (!$requests) {
              echo "<tr><td colspan='7' class='text-center text-gray-500 py-4'>Belum ada permintaan masuk.</td></tr>";
          } else {
              $no = 1;
              foreach ($requests as $req) {
                  $items = json_decode($req['items'], true);
                  echo "<tr class='hover:bg-gray-50'>";
                  echo "<td class='border px-3 py-2'>{$no}</td>";
                  echo "<td class='border px-3 py-2 text-gray-600'>{$req['requested_at']}</td>";
                  echo "<td class='border px-3 py-2 font-semibold'>{$req['room_number']}</td>";
                  echo "<td class='border px-3 py-2'>{$req['guest_name']}</td>";

                  echo "<td class='border px-3 py-2'>";
                  if (is_array($items)) {
                      echo "<ul class='list-disc list-inside'>";
                      foreach ($items as $i) {
                          $qty = htmlspecialchars($i['qty']);
                          $name = htmlspecialchars($i['name']);
                          echo "<li>{$name} <span class='text-gray-500'>(x{$qty})</span></li>";
                      }
                      echo "</ul>";
                  } else {
                      echo "<i>Data tidak valid</i>";
                  }
                  echo "</td>";
                  
                  echo "<td class='border px-3 py-2'>{$req['status']}</td>";
                  echo "<td class='border px-3 py-2'>...</td>"; // Tempat untuk tombol (Selesaikan, Batal)

                  echo "</tr>";
                  $no++;
              }
          }
        } catch (Exception $e) {
            echo "<tr><td colspan='7' class='text-center text-red-500 py-4'>Kesalahan DB: {$e->getMessage()}</td></tr>";
        }
        ?>
      </tbody>
    </table>
  </div>
</div>