<?php
/**
 * IPTV Channel Manager
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';
$db = init_db_connection();
if (!$db) {
    echo "<h2 style='color:red'>DB Error</h2>";
    exit;
}
$success = '';
$error = '';
$activeTab = $_GET['tab'] ?? 'dashboard';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pa = $_POST['post_action'] ?? '';
    if ($pa === 'save_text_colors') {
        $categoryColor = strtoupper(trim((string)($_POST['category_text_color'] ?? '')));
        $channelColor = strtoupper(trim((string)($_POST['channel_text_color'] ?? '')));

        $isValidHex = static function ($value) {
            return (bool)preg_match('/^#[0-9A-F]{6}$/', $value);
        };

        if (!$isValidHex($categoryColor) || !$isValidHex($channelColor)) {
            $error = 'Format warna tidak valid. Gunakan format HEX seperti #FFFFFF.';
        } else {
            set_setting('iptv_category_text_color', $categoryColor);
            set_setting('iptv_channel_text_color', $channelColor);
            $success = 'Warna teks IPTV berhasil disimpan.';
        }
        $activeTab = 'dashboard';
    }
    if ($pa === 'upload_iptv_bg') {
        if (!empty($_FILES['bg_file']['name']) && ($_FILES['bg_file']['error'] ?? 1) === 0) {
            $uploadDir = __DIR__ . '/../uploads/iptvbg/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0775, true);
            }

            $ext = strtolower(pathinfo($_FILES['bg_file']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($ext, $allowed, true)) {
                $filename = 'iptv_bg.' . $ext;
                $targetFile = $uploadDir . $filename;

                // Bersihkan versi lama (beda ekstensi)
                foreach (glob($uploadDir . 'iptv_bg.*') as $oldFile) {
                    if ($oldFile !== $targetFile && is_file($oldFile)) {
                        @unlink($oldFile);
                    }
                }

                if (move_uploaded_file($_FILES['bg_file']['tmp_name'], $targetFile)) {
                    // cache-bust supaya TV langsung dapat versi baru
                    $relativePath = "uploads/iptvbg/" . $filename . "?v=" . time();
                    set_setting('iptv_bg', $relativePath);
                    $success = 'Background IPTV berhasil diperbarui.';
                } else {
                    $error = 'Gagal mengupload file background.';
                }
            } else {
                $error = 'Format file tidak valid. Gunakan JPG/PNG/WEBP.';
            }
        } else {
            $error = 'Pilih file gambar terlebih dahulu.';
        }
        $activeTab = 'dashboard';
    }
    if ($pa === 'add_channel') {
        $lcn = (int)($_POST['lcn'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $cat = trim($_POST['category'] ?? 'Umum');
        $surl = trim($_POST['stream_url'] ?? '');
        $logo = trim($_POST['logo_url_text'] ?? '');
        if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['logo_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
                $dir = __DIR__ . '/../uploads/iptv/';
                if (!is_dir($dir))
                    mkdir($dir, 0755, true);
                $fn = uniqid('ch_') . '.' . $ext;
                if (move_uploaded_file($_FILES['logo_file']['tmp_name'], $dir . $fn))
                    $logo = rtrim(BASE_URL, '/') . '/uploads/iptv/' . $fn;
            }
        }
        if ($title === '' || $surl === '')
            $error = 'Nama dan Stream URL wajib diisi.';
        else {
            $db->prepare("INSERT INTO channels (lcn,title,category,stream_url,logo_url) VALUES (?,?,?,?,?)")->execute([$lcn, $title, $cat, $surl, $logo]);
            $success = 'Channel ditambahkan.';
        }
        $activeTab = 'manual';
    }
    if ($pa === 'edit_channel') {
        $id = (int)($_POST['edit_id'] ?? 0);
        $lcn = (int)($_POST['lcn'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $cat = trim($_POST['category'] ?? '');
        $surl = trim($_POST['stream_url'] ?? '');
        $logo = trim($_POST['logo_url_text'] ?? '');
        if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === 0) {
            $ext = strtolower(pathinfo($_FILES['logo_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) {
                $dir = __DIR__ . '/../uploads/iptv/';
                if (!is_dir($dir))
                    mkdir($dir, 0755, true);
                $fn = uniqid('ch_') . '.' . $ext;
                if (move_uploaded_file($_FILES['logo_file']['tmp_name'], $dir . $fn))
                    $logo = rtrim(BASE_URL, '/') . '/uploads/iptv/' . $fn;
            }
        }
        if ($id > 0) {
            $db->prepare("UPDATE channels SET lcn=?,title=?,category=?,stream_url=?,logo_url=? WHERE id=?")->execute([$lcn, $title, $cat, $surl, $logo, $id]);
            $success = 'Channel diperbarui.';
        }
        $activeTab = 'manual';
    }
    if ($pa === 'toggle_status') {
        $id = (int)($_POST['channel_id'] ?? 0);
        $ns = ($_POST['new_status'] ?? 'enabled') === 'disabled' ? 'disabled' : 'enabled';
        if ($id > 0) {
            $db->prepare("UPDATE channels SET status=? WHERE id=?")->execute([$ns, $id]);
            $success = 'Status diubah.';
        }
        $activeTab = 'manual';
    }
    if ($pa === 'delete_channel') {
        $id = (int)($_POST['channel_id'] ?? 0);
        if ($id > 0) {
            $db->prepare("DELETE FROM channels WHERE id=?")->execute([$id]);
            $success = 'Channel dihapus.';
        }
        $activeTab = 'manual';
    }
    if ($pa === 'add_playlist') {
        // Backward-compat: treat as add_category (URL tidak dipakai lagi)
        $name = trim($_POST['name'] ?? '');
        if ($name !== '') {
            $db->prepare("INSERT INTO playlists (name,url,default_category) VALUES (?,?,?)")->execute([$name, '', $name]);
            $success = 'Kategori ditambahkan.';
        } else {
            $error = 'Nama kategori wajib diisi.';
        }
        $activeTab = 'playlist';
    }
    if ($pa === 'delete_playlist') {
        $id = (int)($_POST['playlist_id'] ?? 0);
        if ($id > 0) {
            $db->prepare("DELETE FROM playlists WHERE id=?")->execute([$id]);
            $success = 'Kategori dihapus.';
        }
        $activeTab = 'playlist';
    }
    if ($pa === 'edit_playlist') {
        $id = (int)($_POST['playlist_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        if ($id > 0 && $name !== '') {
            // Ambil nama kategori lama (fallback dari name/default_category)
            $oldStmt = $db->prepare("
                SELECT TRIM(COALESCE(NULLIF(name,''), NULLIF(default_category,''))) AS old_name
                FROM playlists
                WHERE id=?
                LIMIT 1
            ");
            $oldStmt->execute([$id]);
            $oldName = trim((string)($oldStmt->fetchColumn() ?? ''));

            $db->beginTransaction();
            try {
                // Update master kategori
                $db->prepare("UPDATE playlists SET name=?, default_category=? WHERE id=?")->execute([$name, $name, $id]);

                // Sinkronkan ke channel yang masih pakai nama kategori lama
                if ($oldName !== '' && $oldName !== $name) {
                    $db->prepare("UPDATE channels SET category=? WHERE category=?")->execute([$name, $oldName]);
                }

                $db->commit();
                $success = 'Kategori diperbarui dan channel terkait ikut disinkronkan.';
            } catch (Exception $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                $error = 'Gagal update kategori: ' . $e->getMessage();
            }
        } else {
            $error = 'Nama kategori wajib diisi.';
        }
        $activeTab = 'playlist';
    }
    if ($pa === 'import_m3u') {
        $m3u = '';
        if (isset($_FILES['m3u_file']) && $_FILES['m3u_file']['error'] === 0)
            $m3u = file_get_contents($_FILES['m3u_file']['tmp_name']);
        elseif (!empty($_POST['local_file'])) {
            $lf = __DIR__ . '/../dokumentasi/m3u/tvhotel/pl.m3u';
            if (file_exists($lf))
                $m3u = file_get_contents($lf);
            else
                $error = 'File pl.m3u tidak ditemukan.';
        }
        if ($m3u !== '' && $error === '') {
            $lines = explode("\n", str_replace("\r", "", $m3u));
            $imp = 0;
            $skip = 0;
            $lcn_c = (int)$db->query("SELECT COALESCE(MAX(lcn),0) FROM channels")->fetchColumn();
            for ($i = 0; $i < count($lines); $i++) {
                $line = trim($lines[$i]);
                if (strpos($line, '#EXTINF:') === 0) {
                    $t = '';
                    $lg = '';
                    $ct = 'Umum';
                    $u = '';
                    $cp = strrpos($line, ',');
                    if ($cp !== false)
                        $t = trim(substr($line, $cp + 1));
                    if (preg_match('/tvg-logo="([^"]*)"/', $line, $m))
                        $lg = $m[1];
                    if (preg_match('/group-title="([^"]*)"/', $line, $m))
                        $ct = $m[1];
                    for ($j = $i + 1; $j < count($lines); $j++) {
                        $nx = trim($lines[$j]);
                        if ($nx !== '' && strpos($nx, '#') !== 0) {
                            $u = $nx;
                            $i = $j;
                            break;
                        }
                    }
                    if ($t !== '' && $u !== '') {
                        $chk = $db->prepare("SELECT COUNT(*) FROM channels WHERE title=? AND stream_url=?");
                        $chk->execute([$t, $u]);
                        if ($chk->fetchColumn() == 0) {
                            $lcn_c++;
                            try {
                                $db->prepare("INSERT INTO channels (lcn,title,category,stream_url,logo_url,status) VALUES (?,?,?,?,?,'enabled')")->execute([$lcn_c, $t, $ct, $u, $lg]);
                                $imp++;
                            }
                            catch (Exception $e) {
                                $skip++;
                            }
                        }
                        else
                            $skip++;
                    }
                }
            }
            $success = "Import selesai: $imp channel ditambahkan, $skip dilewati.";
        }
        $activeTab = 'manual';
    }
}
try {
    $total_ch = (int)$db->query("SELECT COUNT(*) FROM channels")->fetchColumn();
    $active_ch = (int)$db->query("SELECT COUNT(*) FROM channels WHERE status='enabled'")->fetchColumn();
    $total_pl = (int)$db->query("SELECT COUNT(*) FROM playlists")->fetchColumn();
    $cats = $db->query("SELECT DISTINCT category FROM channels ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
}
catch (Exception $e) {
    $total_ch = 0;
    $active_ch = 0;
    $total_pl = 0;
    $cats = [];
}
include __DIR__ . '/iptv_view.php';

// Flash messages
if ($success)
    echo '<div class="bg-green-50 border border-green-200 text-green-700 px-5 py-3 rounded-lg mb-5 text-sm">' . htmlspecialchars($success) . '</div>';
if ($error)
    echo '<div class="bg-red-50 border border-red-200 text-red-700 px-5 py-3 rounded-lg mb-5 text-sm">' . htmlspecialchars($error) . '</div>';

// Tabs
echo '<div class="iptv-tabs">';
echo '<a href="?page=iptv&tab=dashboard" class="iptv-tab ' . ($activeTab === 'dashboard' ? 'active' : '') . '">📊 Dashboard</a>';
echo '<a href="?page=iptv&tab=manual" class="iptv-tab ' . ($activeTab === 'manual' ? 'active' : '') . '">📡 Channel</a>';
echo '<a href="?page=iptv&tab=playlist" class="iptv-tab ' . ($activeTab === 'playlist' ? 'active' : '') . '">🗂️ Kategori</a>';
echo '</div>';

// === DASHBOARD TAB ===
if ($activeTab === 'dashboard') {
    echo '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px;">';
    echo '<div class="sc"><div style="display:flex;align-items:center;gap:14px;"><div class="si" style="background:#dbeafe;">📡</div><div><div class="sv">' . $total_ch . '</div><div class="sl">Total Channel</div></div></div></div>';
    echo '<div class="sc"><div style="display:flex;align-items:center;gap:14px;"><div class="si" style="background:#dcfce7;">✅</div><div><div class="sv">' . $active_ch . '</div><div class="sl">Channel Aktif</div></div></div></div>';
    echo '<div class="sc"><div style="display:flex;align-items:center;gap:14px;"><div class="si" style="background:#fef3c7;">🔗</div><div><div class="sv">' . $total_pl . '</div><div class="sl">Playlist External</div></div></div></div>';
    echo '</div>';

    echo '<div class="fc"><h3 style="font-size:15px;font-weight:600;color:#1f2937;margin-bottom:8px;">📥 Import dari File M3U</h3>';
    echo '<p style="font-size:12px;color:#9ca3af;margin-bottom:16px;">Import channel dari file .m3u ke database. Duplikat dilewati otomatis.</p>';
    echo '<div style="display:flex;gap:12px;flex-wrap:wrap;">';
    echo '<form method="POST" action="?page=iptv&tab=dashboard" enctype="multipart/form-data" style="display:flex;gap:8px;align-items:center;">';
    echo '<input type="hidden" name="post_action" value="import_m3u"><input type="file" name="m3u_file" accept=".m3u,.m3u8" class="fi" style="max-width:280px;" required>';
    echo '<button type="submit" class="bx" style="background:#eab308;color:#111;padding:9px 16px;">📥 Import File</button></form>';
    if (file_exists(__DIR__ . '/../dokumentasi/m3u/tvhotel/pl.m3u')) {
        echo '<form method="POST" action="?page=iptv&tab=dashboard" onsubmit="return confirm(\'Import semua channel dari pl.m3u?\')">';
        echo '<input type="hidden" name="post_action" value="import_m3u"><input type="hidden" name="local_file" value="1">';
        echo '<button type="submit" class="bx" style="background:#dbeafe;color:#1e40af;padding:9px 16px;">📂 Import pl.m3u (lokal)</button></form>';
    }
    echo '</div></div>';

    echo '<div class="fc" style="margin-top:16px;"><h3 style="font-size:15px;font-weight:600;color:#1f2937;margin-bottom:4px;">ℹ️ API Endpoint</h3>';
    echo '<p style="font-size:12px;color:#9ca3af;margin-bottom:10px;">URL untuk APK player:</p>';
    echo '<code style="background:#1f2937;color:#4ade80;padding:10px 16px;border-radius:8px;display:block;font-size:13px;">' . rtrim(BASE_URL, '/') . '/api.php?action=get_channels</code></div>';

    // Background IPTV
    $current_iptv_bg = trim((string)($db->query("SELECT setting_value FROM global_settings WHERE setting_key='iptv_bg'")->fetchColumn() ?? ''));
    $current_iptv_bg_full = $current_iptv_bg ? get_full_url($current_iptv_bg) : '';
    $currentCategoryTextColor = strtoupper(trim((string)($db->query("SELECT setting_value FROM global_settings WHERE setting_key='iptv_category_text_color'")->fetchColumn() ?? '')));
    $currentChannelTextColor = strtoupper(trim((string)($db->query("SELECT setting_value FROM global_settings WHERE setting_key='iptv_channel_text_color'")->fetchColumn() ?? '')));
    if (!preg_match('/^#[0-9A-F]{6}$/', $currentCategoryTextColor)) {
        $currentCategoryTextColor = '#FFFFFF';
    }
    if (!preg_match('/^#[0-9A-F]{6}$/', $currentChannelTextColor)) {
        $currentChannelTextColor = '#EDEDED';
    }

    echo '<div class="fc" style="margin-top:16px;">';
    echo '<h3 style="font-size:15px;font-weight:600;color:#1f2937;margin-bottom:8px;">🖼️ Background Aplikasi IPTV</h3>';
    echo '<p style="font-size:12px;color:#9ca3af;margin-bottom:12px;">Background ini dipakai oleh aplikasi <b>TakeOffIPTV</b> (player). Setelah upload, APK akan mengambil otomatis dari API.</p>';

    if ($current_iptv_bg_full) {
        echo '<div style="display:flex;gap:14px;align-items:center;flex-wrap:wrap;margin-bottom:12px;">';
        echo '<img src="' . htmlspecialchars($current_iptv_bg_full) . '" alt="IPTV BG" style="width:220px;height:120px;object-fit:cover;border-radius:12px;border:1px solid #e5e7eb;background:#111827">';
        echo '<div style="font-size:12px;color:#6b7280;"><div style="font-weight:600;color:#374151;margin-bottom:4px;">Current</div><code style="background:#f3f4f6;color:#111827;padding:6px 10px;border-radius:8px;display:inline-block;">' . htmlspecialchars($current_iptv_bg) . '</code></div>';
        echo '</div>';
    } else {
        echo '<div style="font-size:12px;color:#9ca3af;margin-bottom:12px;">Belum ada background IPTV. Upload di bawah.</div>';
    }

    echo '<form method="POST" action="?page=iptv&tab=dashboard" enctype="multipart/form-data" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">';
    echo '<input type="hidden" name="post_action" value="upload_iptv_bg">';
    echo '<input type="file" name="bg_file" accept="image/*" class="fi" style="max-width:320px;" required>';
    echo '<button type="submit" class="bx" style="background:#eab308;color:#111;padding:9px 16px;">⬆️ Upload Background</button>';
    echo '</form>';

    echo '<div style="margin-top:12px;">';
    echo '<div style="font-size:12px;color:#6b7280;margin-bottom:6px;">API yang dipakai aplikasi:</div>';
    echo '<code style="background:#1f2937;color:#4ade80;padding:10px 16px;border-radius:8px;display:block;font-size:13px;">' . rtrim(BASE_URL, '/') . '/api.php?action=getIptvBackground</code>';
    echo '</div>';
    echo '</div>';

    echo '<div class="fc" style="margin-top:16px;">';
    echo '<h3 style="font-size:15px;font-weight:600;color:#1f2937;margin-bottom:8px;">🎨 Warna Teks IPTV (APK)</h3>';
    echo '<p style="font-size:12px;color:#9ca3af;margin-bottom:12px;">Atur warna teks kategori dan nama channel yang tampil di aplikasi <b>TakeOffIPTV</b>.</p>';
    echo '<form method="POST" action="?page=iptv&tab=dashboard" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;align-items:end;">';
    echo '<input type="hidden" name="post_action" value="save_text_colors">';
    echo '<div><label style="display:block;font-size:12px;font-weight:500;color:#374151;margin-bottom:6px;">Warna teks kategori</label><input type="color" name="category_text_color" value="' . htmlspecialchars($currentCategoryTextColor) . '" class="fi" style="height:42px;padding:4px;"></div>';
    echo '<div><label style="display:block;font-size:12px;font-weight:500;color:#374151;margin-bottom:6px;">Warna nama channel</label><input type="color" name="channel_text_color" value="' . htmlspecialchars($currentChannelTextColor) . '" class="fi" style="height:42px;padding:4px;"></div>';
    echo '<button type="submit" class="bx" style="background:#eab308;color:#111;padding:9px 16px;justify-self:start;">💾 Simpan Warna</button>';
    echo '</form>';
    echo '</div>';
}

// === CHANNEL TAB ===
if ($activeTab === 'manual') {
    echo '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">';
    echo '<h3 style="font-size:16px;font-weight:600;color:#1f2937;">📡 Kelola Channel (' . $total_ch . ')</h3>';
    echo '<button onclick="document.getElementById(\'mAdd\').classList.add(\'show\')" class="bx" style="background:#eab308;color:#111;padding:8px 16px;font-size:13px;">➕ Tambah Channel</button></div>';

    if ($total_ch > 0) {
        $fcat = $_GET['cat'] ?? '';
        echo '<div class="fc" style="padding:0;overflow-x:auto;"><table class="ct"><thead><tr><th>No</th><th>Logo</th><th>Nama Channel</th><th>Kategori</th><th>Status</th><th style="text-align:center;">Aksi</th></tr></thead><tbody>';
        $sql = "SELECT * FROM channels" . ($fcat !== '' ? " WHERE category=?" : '') . " ORDER BY lcn ASC";
        $prm = $fcat !== '' ? [$fcat] : [];
        $st = $db->prepare($sql);
        $st->execute($prm);
        while ($ch = $st->fetch()) {
            $logoHtml = $ch['logo_url'] ? '<img src="' . htmlspecialchars($ch['logo_url']) . '" class="clogo" onerror="this.style.display=\'none\'">' : '<div class="clogo" style="display:flex;align-items:center;justify-content:center;">📺</div>';
            $statusCls = $ch['status'] === 'enabled' ? 'bon' : 'boff';
            $statusTxt = $ch['status'] === 'enabled' ? '● LIVE' : '○ OFF';
            $newSt = $ch['status'] === 'enabled' ? 'disabled' : 'enabled';
            $chJson = htmlspecialchars(json_encode($ch), ENT_QUOTES);
            echo '<tr>';
            echo '<td style="font-weight:600;color:#9ca3af;">' . $ch['lcn'] . '</td>';
            echo '<td>' . $logoHtml . '</td>';
            echo '<td><div style="font-weight:500;color:#1f2937;">' . htmlspecialchars($ch['title']) . '</div><div style="font-size:10px;color:#9ca3af;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' . htmlspecialchars(substr($ch['stream_url'], 0, 50)) . '</div></td>';
            echo '<td><span style="background:#f3f4f6;color:#4b5563;padding:2px 8px;border-radius:6px;font-size:11px;">' . htmlspecialchars($ch['category']) . '</span></td>';
            echo '<td><form method="POST" action="?page=iptv&tab=manual" style="display:inline;"><input type="hidden" name="post_action" value="toggle_status"><input type="hidden" name="channel_id" value="' . $ch['id'] . '"><input type="hidden" name="new_status" value="' . $newSt . '"><button type="submit" class="' . $statusCls . '" style="border:none;cursor:pointer;">' . $statusTxt . '</button></form></td>';
            echo '<td style="text-align:center;"><div style="display:flex;gap:4px;justify-content:center;"><button onclick="editCh(' . $chJson . ')" class="bx" style="background:#fef3c7;color:#92400e;">✏️</button>';
            echo '<form method="POST" action="?page=iptv&tab=manual" onsubmit="return confirm(\'Hapus?\')" style="display:inline;"><input type="hidden" name="post_action" value="delete_channel"><input type="hidden" name="channel_id" value="' . $ch['id'] . '"><button type="submit" class="bx" style="background:#fee2e2;color:#dc2626;">🗑️</button></form></div></td>';
            echo '</tr>';
        }
        echo '</tbody></table></div>';
        if (count($cats) > 1) {
            echo '<div style="margin-top:12px;display:flex;gap:6px;flex-wrap:wrap;align-items:center;"><span style="font-size:12px;color:#6b7280;">Filter:</span>';
            echo '<a href="?page=iptv&tab=manual" class="bx" style="background:' . ($fcat === '' ? '#eab308' : '#f3f4f6') . ';color:' . ($fcat === '' ? '#111' : '#4b5563') . ';">Semua</a>';
            foreach ($cats as $c)
                echo '<a href="?page=iptv&tab=manual&cat=' . urlencode($c) . '" class="bx" style="background:' . ($fcat === $c ? '#eab308' : '#f3f4f6') . ';color:' . ($fcat === $c ? '#111' : '#4b5563') . ';">' . htmlspecialchars($c) . '</a>';
            echo '</div>';
        }
    }
    else {
        echo '<div class="fc" style="text-align:center;padding:40px;"><p style="font-size:48px;margin-bottom:10px;">📡</p><p style="color:#6b7280;">Belum ada channel. Tambahkan manual atau import M3U.</p>';
        echo '<a href="?page=iptv&tab=dashboard" style="display:inline-block;margin-top:12px;background:#eab308;color:#111;padding:8px 20px;border-radius:8px;font-size:13px;font-weight:500;text-decoration:none;">📥 Import M3U</a></div>';
    }

    // Ambil kategori dari tabel playlists (sekarang dipakai sebagai master kategori)
    $categoryOptions = [];
    try {
        $categoryOptions = $db->query("
            SELECT DISTINCT
                TRIM(COALESCE(NULLIF(name,''), NULLIF(default_category,''))) AS cat_name
            FROM playlists
            WHERE TRIM(COALESCE(NULLIF(name,''), NULLIF(default_category,''))) <> ''
            ORDER BY cat_name ASC
        ")->fetchAll(PDO::FETCH_COLUMN);
    } catch (Exception $e) { $categoryOptions = []; }

    // Tambahkan kategori existing dari channel agar tidak hilang dari pilihan
    try {
        $existingCats = $db->query("SELECT DISTINCT category FROM channels WHERE category <> '' ORDER BY category ASC")->fetchAll(PDO::FETCH_COLUMN);
        $categoryOptions = array_values(array_unique(array_merge($categoryOptions, $existingCats)));
        sort($categoryOptions);
    } catch (Exception $e) { }

    $categorySelectHtml = function($selected) use ($categoryOptions) {
        $html = '<select name="category" class="fi">';
        foreach ($categoryOptions as $opt) {
            $sel = ($selected === $opt) ? ' selected' : '';
            $html .= '<option value="' . htmlspecialchars($opt) . '"' . $sel . '>' . htmlspecialchars($opt) . '</option>';
        }
        if (empty($categoryOptions)) {
            $html .= '<option value="Umum" selected>Umum</option>';
        }
        $html .= '</select>';
        return $html;
    };

    // Modal Add
    echo '<div class="im" id="mAdd"><div class="imb"><h3 style="font-size:16px;font-weight:600;margin-bottom:16px;">➕ Tambah Channel</h3>';
    echo '<form method="POST" action="?page=iptv&tab=manual" enctype="multipart/form-data"><input type="hidden" name="post_action" value="add_channel">';
    echo '<div style="display:grid;grid-template-columns:80px 1fr;gap:12px;"><div><label style="font-size:12px;font-weight:500;color:#374151;">LCN</label><input type="number" name="lcn" class="fi" value="' . ($total_ch + 1) . '"></div>';
    echo '<div><label style="font-size:12px;font-weight:500;color:#374151;">Nama *</label><input type="text" name="title" class="fi" required></div></div>';
    echo '<div style="margin-top:10px;"><label style="font-size:12px;font-weight:500;color:#374151;">Kategori</label>' . $categorySelectHtml('Umum') . '</div>';
    echo '<div style="margin-top:10px;"><label style="font-size:12px;font-weight:500;color:#374151;">Stream URL *</label><input type="text" name="stream_url" class="fi" required placeholder="http://..."></div>';
    echo '<div style="margin-top:10px;padding:10px;border:1px solid #e5e7eb;border-radius:8px;"><label style="font-size:12px;font-weight:500;color:#374151;">Logo</label><input type="file" name="logo_file" class="fi" style="margin-top:4px;" accept="image/*"><div style="text-align:center;font-size:11px;color:#9ca3af;margin:6px 0;">— atau —</div><input type="text" name="logo_url_text" class="fi" placeholder="URL logo"></div>';
    echo '<div style="display:flex;gap:8px;justify-content:flex-end;margin-top:16px;"><button type="button" onclick="document.getElementById(\'mAdd\').classList.remove(\'show\')" class="bx" style="background:#f3f4f6;color:#374151;padding:8px 16px;">Batal</button><button type="submit" class="bx" style="background:#eab308;color:#111;padding:8px 16px;">💾 Simpan</button></div></form></div></div>';

    // Modal Edit
    echo '<div class="im" id="mEdit"><div class="imb"><h3 style="font-size:16px;font-weight:600;margin-bottom:16px;">✏️ Edit Channel</h3>';
    echo '<form method="POST" action="?page=iptv&tab=manual" enctype="multipart/form-data"><input type="hidden" name="post_action" value="edit_channel"><input type="hidden" name="edit_id" id="e_id">';
    echo '<div style="display:grid;grid-template-columns:80px 1fr;gap:12px;"><div><label style="font-size:12px;font-weight:500;color:#374151;">LCN</label><input type="number" name="lcn" id="e_lcn" class="fi"></div>';
    echo '<div><label style="font-size:12px;font-weight:500;color:#374151;">Nama *</label><input type="text" name="title" id="e_title" class="fi" required></div></div>';
    // Select untuk edit (diisi via JS)
    echo '<div style="margin-top:10px;"><label style="font-size:12px;font-weight:500;color:#374151;">Kategori</label><div id="e_cat_wrap">' . $categorySelectHtml('Umum') . '</div></div>';
    echo '<div style="margin-top:10px;"><label style="font-size:12px;font-weight:500;color:#374151;">Stream URL *</label><input type="text" name="stream_url" id="e_url" class="fi" required></div>';
    echo '<div style="margin-top:10px;padding:10px;border:1px solid #e5e7eb;border-radius:8px;"><label style="font-size:12px;font-weight:500;color:#374151;">Ganti Logo</label><input type="file" name="logo_file" class="fi" style="margin-top:4px;" accept="image/*"><label style="font-size:11px;color:#9ca3af;margin-top:6px;display:block;">URL saat ini:</label><input type="text" name="logo_url_text" id="e_logo" class="fi"></div>';
    echo '<div style="display:flex;gap:8px;justify-content:flex-end;margin-top:16px;"><button type="button" onclick="document.getElementById(\'mEdit\').classList.remove(\'show\')" class="bx" style="background:#f3f4f6;color:#374151;padding:8px 16px;">Batal</button><button type="submit" class="bx" style="background:#eab308;color:#111;padding:8px 16px;">💾 Update</button></div></form></div></div>';

    echo '<script>
function setEditCategory(cat){
  const wrap = document.getElementById("e_cat_wrap");
  if(!wrap) return;
  const sel = wrap.querySelector("select[name=category]");
  if(!sel) return;
  // kalau kategori belum ada di master, inject sebagai option agar tidak hilang
  let has = false;
  for(const opt of sel.options){ if(opt.value === cat){ has = true; break; } }
  if(!has && cat){
    const o = document.createElement("option");
    o.value = cat; o.textContent = cat;
    sel.appendChild(o);
  }
  sel.value = cat || "Umum";
}
function editCh(d){
  document.getElementById("e_id").value=d.id;
  document.getElementById("e_lcn").value=d.lcn;
  document.getElementById("e_title").value=d.title;
  setEditCategory(d.category || "Umum");
  document.getElementById("e_url").value=d.stream_url;
  document.getElementById("e_logo").value=d.logo_url||"";
  document.getElementById("mEdit").classList.add("show");
}
document.querySelectorAll(".im").forEach(function(m){
  m.addEventListener("click",function(e){if(e.target===this)this.classList.remove("show");});
});
</script>';
}

// === KATEGORI TAB (pakai tabel playlists, tanpa ubah DB) ===
if ($activeTab === 'playlist') {
    echo '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">';
    echo '<h3 style="font-size:16px;font-weight:600;color:#1f2937;">🗂️ Master Kategori IPTV</h3>';
    echo '<button onclick="document.getElementById(\'mPl\').classList.add(\'show\')" class="bx" style="background:#eab308;color:#111;padding:8px 16px;font-size:13px;">➕ Tambah Kategori</button></div>';

    echo '<div class="fc" style="padding:0;overflow-x:auto;"><table class="ct"><thead><tr><th>Nama Kategori</th><th style="text-align:center;">Aksi</th></tr></thead><tbody>';
    $pls = $db->query("
        SELECT
            id,
            TRIM(COALESCE(NULLIF(name,''), NULLIF(default_category,''))) AS category_name
        FROM playlists
        ORDER BY id DESC
    ")->fetchAll();
    if (empty($pls)) {
        echo '<tr><td colspan="2" style="text-align:center;padding:30px;color:#9ca3af;">Belum ada kategori.</td></tr>';
    }
    else {
        foreach ($pls as $pl) {
            $catName = trim((string)($pl['category_name'] ?? ''));
            if ($catName === '') {
                continue;
            }
            echo '<tr><td style="font-weight:500;">' . htmlspecialchars($catName) . '</td>';
            echo '<td style="text-align:center;">';
            echo '<div style="display:inline-flex;gap:6px;align-items:center;">';
            echo '<button type="button" onclick="openEditCategory(' . (int)$pl['id'] . ', \'' . htmlspecialchars($catName, ENT_QUOTES) . '\')" class="bx" style="background:#fef3c7;color:#92400e;">✏️ Edit</button>';
            echo '<form method="POST" action="?page=iptv&tab=playlist" onsubmit="return confirm(\'Hapus kategori ini?\')" style="display:inline-flex;margin:0;"><input type="hidden" name="post_action" value="delete_playlist"><input type="hidden" name="playlist_id" value="' . $pl['id'] . '"><button type="submit" class="bx" style="background:#fee2e2;color:#dc2626;">🗑️ Hapus</button></form>';
            echo '</div>';
            echo '</td></tr>';
        }
    }
    echo '</tbody></table></div>';

    // Modal Add Kategori
    echo '<div class="im" id="mPl"><div class="imb" style="max-width:440px;"><h3 style="font-size:16px;font-weight:600;margin-bottom:16px;">➕ Tambah Kategori</h3>';
    echo '<form method="POST" action="?page=iptv&tab=playlist"><input type="hidden" name="post_action" value="add_playlist">';
    echo '<div style="margin-bottom:10px;"><label style="font-size:12px;font-weight:500;color:#374151;">Nama Kategori *</label><input type="text" name="name" class="fi" required placeholder="Contoh: LIVE TV 1"></div>';
    echo '<div style="display:flex;gap:8px;justify-content:flex-end;"><button type="button" onclick="document.getElementById(\'mPl\').classList.remove(\'show\')" class="bx" style="background:#f3f4f6;color:#374151;padding:8px 16px;">Batal</button><button type="submit" class="bx" style="background:#eab308;color:#111;padding:8px 16px;">💾 Simpan</button></div></form></div></div>';

    // Modal Edit Kategori
    echo '<div class="im" id="mPlEdit"><div class="imb" style="max-width:440px;"><h3 style="font-size:16px;font-weight:600;margin-bottom:16px;">✏️ Edit Kategori</h3>';
    echo '<form method="POST" action="?page=iptv&tab=playlist">';
    echo '<input type="hidden" name="post_action" value="edit_playlist">';
    echo '<input type="hidden" name="playlist_id" id="edit_playlist_id">';
    echo '<div style="margin-bottom:10px;"><label style="font-size:12px;font-weight:500;color:#374151;">Nama Kategori *</label><input type="text" name="name" id="edit_playlist_name" class="fi" required></div>';
    echo '<div style="display:flex;gap:8px;justify-content:flex-end;"><button type="button" onclick="document.getElementById(\'mPlEdit\').classList.remove(\'show\')" class="bx" style="background:#f3f4f6;color:#374151;padding:8px 16px;">Batal</button><button type="submit" class="bx" style="background:#eab308;color:#111;padding:8px 16px;">💾 Update</button></div>';
    echo '</form></div></div>';

    echo '<script>
function openEditCategory(id,name){
  document.getElementById("edit_playlist_id").value = id;
  document.getElementById("edit_playlist_name").value = name || "";
  document.getElementById("mPlEdit").classList.add("show");
}
document.querySelectorAll(".im").forEach(function(m){
  m.addEventListener("click",function(e){if(e.target===this)this.classList.remove("show");});
});
</script>';
}
