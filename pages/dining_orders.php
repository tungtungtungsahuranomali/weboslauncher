<?php

require_once __DIR__ . '/../config.php';

$db = init_db_connection();
if ($db === null) {
    echo "<div class='p-4 bg-red-100 text-red-700 rounded'>❌ Gagal koneksi database.</div>";
    return;
}
?>

<h1 class="text-2xl font-bold text-yellow-500 mb-6">📦 Dining Orders</h1>

<div class="bg-white rounded-lg shadow p-6">
  <div class="flex justify-between items-center mb-4">
    <h2 class="text-lg font-semibold text-gray-700">Daftar Pesanan Masuk</h2>
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
          <th class="border px-3 py-2 text-left">Pesanan</th>
          <th class="border px-3 py-2 text-left">Total Item</th>
          <th class="border px-3 py-2 text-left">Total Harga</th>
        </tr>
      </thead>
      <tbody>
        <?php
        try {
        
          $stmt = $db->query("SELECT * FROM hotel_orders ORDER BY id DESC");
          $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

          if (!$orders) {
              echo "<tr><td colspan='7' class='text-center text-gray-500 py-4'>Belum ada pesanan masuk.</td></tr>";
          } else {
              $no = 1;
              foreach ($orders as $order) {
                  $items = json_decode($order['items'], true);

                  $totalItems = 0;
                  if (is_array($items)) {
                      foreach ($items as $i) {
                          $totalItems += (int)($i['qty'] ?? 0);
                      }
                  }
               

                  echo "<tr class='hover:bg-gray-50'>";
                  echo "<td class='border px-3 py-2'>{$no}</td>";
                  echo "<td class='border px-3 py-2 text-gray-600'>{$order['ordered_at']}</td>"; // 'created_at' diganti 'ordered_at' sesuai DB
                  echo "<td class='border px-3 py-2 font-semibold'>{$order['room_number']}</td>";
                  echo "<td class='border px-3 py-2'>{$order['guest_name']}</td>";

                  echo "<td class='border px-3 py-2'>";
                  if (is_array($items)) {
                      echo "<ul class='list-disc list-inside'>";
                      foreach ($items as $i) {
                          $qty = htmlspecialchars($i['qty']);
                          $name = htmlspecialchars($i['name']);
                          $price = number_format($i['price'] ?? 0);
                          echo "<li>{$name} <span class='text-gray-500'>(x{$qty}) @ Rp {$price}</span></li>";
                      }
                      echo "</ul>";
                  } else {
                      echo "<i>Data tidak valid</i>";
                  }
                  echo "</td>";

                  // Gunakan hasil perhitungan $totalItems
                  echo "<td class='border px-3 py-2 text-center text-yellow-600 font-bold'>{$totalItems}</td>";
                  // Tampilkan total harga
                  echo "<td class='border px-3 py-2 font-semibold text-green-600'>Rp " . number_format($order['total_price'] ?? 0) . "</td>";
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