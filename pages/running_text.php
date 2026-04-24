<?php

if (!isset($marquee_text)) {
    $db = $db ?? init_db_connection();
    $marquee_text = '';
    if ($db) {
        $stmt = $db->query("SELECT content FROM system_marquee WHERE id = 1");
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);
        $marquee_text = $row ? $row['content'] : '';
    }
}
?>

<div class="bg-white p-8 rounded-lg shadow-lg">
    <h2 class="text-2xl font-semibold text-gray-700 mb-6">
        Pengaturan Running Text (Marquee)
    </h2>

    <form action="admin.php?page=running_text" method="POST">
        <input type="hidden" name="action" value="save_marquee">

        <div>
            <label for="marquee_text" class="block text-sm font-medium text-gray-700">
                Teks Berjalan
            </label>
            <textarea
                id="marquee_text"
                name="marquee_text"
                rows="4"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-yellow-400 focus:border-yellow-400"
            ><?php echo htmlspecialchars($marquee_text); ?></textarea>

            <p class="text-xs text-gray-500 mt-2">
                Teks ini akan ditampilkan sebagai teks berjalan di bagian bawah launcher.
            </p>
        </div>

        <button
            type="submit"
            class="mt-4 px-6 py-2 bg-yellow-400 text-gray-900 font-semibold rounded-lg shadow-md hover:bg-yellow-500 focus:outline-none focus:ring-2 focus:ring-yellow-300 focus:ring-opacity-75">
            Simpan Teks Berjalan
        </button>
    </form>
</div>