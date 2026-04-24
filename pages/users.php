<?php
/**
 * Manajemen Pengguna (CRUD)
 * - List, Tambah, Edit, Hapus pengguna
 * - Checklist permissions per menu
 * - Validasi username unik
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../functions.php';

$db = init_db_connection();
if (!$db) {
    echo "<h2 style='color:red;text-align:center;'>❌ Database tidak dapat terhubung.</h2>";
    exit;
}

// Cek akses
if (!has_permission('users')) {
    echo "<div class='text-center py-20'><p class='text-6xl mb-4'>🔒</p><p class='text-gray-500 text-lg'>Anda tidak memiliki akses ke halaman ini.</p></div>";
    exit;
}

// Daftar semua menu yang bisa diatur
$all_pages = [
    'dashboard' => '📊 Dashboard',
    'devices' => '📱 Perangkat',
    'checkin' => '🔑 Check-In / Out',
    'send_notification' => '🔔 Notifikasi',
    'facilities' => '🏢 Facilities',
    'amenities' => '🧴 Amenities',
    'information' => 'ℹ️ Information',
    'dining' => '🍽️ Dining Menu',
    'dining_orders' => '📋 Pesanan Dining',
    'amenity_requests' => '📦 Permintaan Amenities',
    'app_control' => '📺 Entertainment Apps',
    'running_text' => '📝 Running Text',
    'update' => '⬆️ System Update',
    'flashscreen' => '🖼️ Flashscreen',
    'server_config' => '⚙️ Konfigurasi',
    'users' => '👥 Pengguna',
    'iptv' => '📺 IPTV Channel',
];

$action = $_GET['action'] ?? 'list';
$edit_id = (int) ($_GET['id'] ?? 0);
$current_admin_id = $_SESSION['admin_id'] ?? 0;

// ============================================================
// PROSES POST
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_action = $_POST['action'] ?? '';

    // --- TAMBAH PENGGUNA ---
    if ($post_action === 'add_user') {
        $username = trim($_POST['username'] ?? '');
        $display_name = trim($_POST['display_name'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirm = trim($_POST['confirm'] ?? '');
        $role = ($_POST['role'] ?? 'admin') === 'superadmin' ? 'superadmin' : 'admin';
        $permissions = $_POST['permissions'] ?? [];

        if ($username === '' || $password === '') {
            flash('error', 'Username dan password wajib diisi.');
        } elseif ($password !== $confirm) {
            flash('error', 'Konfirmasi password tidak cocok.');
        } else {
            // Cek duplikat username
            $stmt = $db->prepare("SELECT COUNT(*) FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetchColumn() > 0) {
                flash('error', 'Username "' . htmlspecialchars($username) . '" sudah digunakan. Gunakan username lain.');
            } else {
                try {
                    $db->beginTransaction();

                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $db->prepare("INSERT INTO admins (username, display_name, password_hash, role, created_at) VALUES (?, ?, ?, ?, NOW())");
                    $stmt->execute([$username, $display_name ?: $username, $hash, $role]);
                    $new_id = $db->lastInsertId();

                    // Simpan permissions
                    $stmt_perm = $db->prepare("INSERT INTO admin_permissions (admin_id, page_key, allowed) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE allowed = VALUES(allowed)");
                    foreach ($all_pages as $page_key => $label) {
                        $allowed = in_array($page_key, $permissions) ? 1 : 0;
                        $stmt_perm->execute([$new_id, $page_key, $allowed]);
                    }

                    $db->commit();
                    flash('success', 'Pengguna "' . htmlspecialchars($username) . '" berhasil ditambahkan.');
                } catch (Exception $e) {
                    $db->rollBack();
                    flash('error', 'Gagal menambahkan pengguna: ' . $e->getMessage());
                }
            }
        }
        header('Location: admin.php?page=users');
        exit;
    }

    // --- EDIT PENGGUNA ---
    if ($post_action === 'edit_user') {
        $user_id = (int) ($_POST['user_id'] ?? 0);
        $display_name = trim($_POST['display_name'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $confirm = trim($_POST['confirm'] ?? '');
        $role = ($_POST['role'] ?? 'admin') === 'superadmin' ? 'superadmin' : 'admin';
        $permissions = $_POST['permissions'] ?? [];

        if ($password !== '' && $password !== $confirm) {
            flash('error', 'Konfirmasi password tidak cocok.');
        } else {
            try {
                $db->beginTransaction();

                if ($password !== '') {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $db->prepare("UPDATE admins SET display_name = ?, password_hash = ?, role = ? WHERE id = ?");
                    $stmt->execute([$display_name, $hash, $role, $user_id]);
                } else {
                    $stmt = $db->prepare("UPDATE admins SET display_name = ?, role = ? WHERE id = ?");
                    $stmt->execute([$display_name, $role, $user_id]);
                }

                // Update permissions
                $stmt_perm = $db->prepare("INSERT INTO admin_permissions (admin_id, page_key, allowed) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE allowed = VALUES(allowed)");
                foreach ($all_pages as $page_key => $label) {
                    $allowed = in_array($page_key, $permissions) ? 1 : 0;
                    $stmt_perm->execute([$user_id, $page_key, $allowed]);
                }

                $db->commit();

                // Jika edit diri sendiri, update session
                if ($user_id == $current_admin_id) {
                    $_SESSION['admin_display_name'] = $display_name;
                    $_SESSION['admin_role'] = $role;
                    unset($_SESSION['admin_permissions']);
                }

                flash('success', 'Pengguna berhasil diperbarui.');
            } catch (Exception $e) {
                $db->rollBack();
                flash('error', 'Gagal memperbarui pengguna: ' . $e->getMessage());
            }
        }
        header('Location: admin.php?page=users');
        exit;
    }

    // --- HAPUS PENGGUNA ---
    if ($post_action === 'delete_user') {
        $user_id = (int) ($_POST['user_id'] ?? 0);

        if ($user_id == $current_admin_id) {
            flash('error', 'Anda tidak bisa menghapus akun Anda sendiri.');
        } else {
            try {
                $stmt = $db->prepare("DELETE FROM admins WHERE id = ?");
                $stmt->execute([$user_id]);
                flash('success', 'Pengguna berhasil dihapus.');
            } catch (Exception $e) {
                flash('error', 'Gagal menghapus pengguna: ' . $e->getMessage());
            }
        }
        header('Location: admin.php?page=users');
        exit;
    }
}

// ============================================================
// AMBIL DATA
// ============================================================

// Ambil semua pengguna
$users = $db->query("SELECT id, username, display_name, role, created_at FROM admins ORDER BY id ASC")->fetchAll();

// Jika mode edit, ambil data user + permissions
$edit_user = null;
$edit_perms = [];
if ($action === 'edit' && $edit_id > 0) {
    $stmt = $db->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_user = $stmt->fetch();

    if ($edit_user) {
        $edit_perms = get_user_permissions($edit_id);
    } else {
        $action = 'list';
    }
}

// Flash messages
$msg_success = flash('success');
$msg_error = flash('error');
?>

<style>
    .user-table {
        width: 100%;
        border-collapse: collapse;
    }

    .user-table th,
    .user-table td {
        padding: 12px 16px;
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
    }

    .user-table th {
        background: #f9fafb;
        color: #6b7280;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 600;
    }

    .user-table tr:hover {
        background: #f3f4f6;
    }

    .badge {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 600;
    }

    .badge-superadmin {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-admin {
        background: #dbeafe;
        color: #1e40af;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.15s;
        border: none;
        text-decoration: none;
    }

    .btn-primary {
        background: #eab308;
        color: #111827;
    }

    .btn-primary:hover {
        background: #ca8a04;
    }

    .btn-secondary {
        background: #f3f4f6;
        color: #374151;
        border: 1px solid #d1d5db;
    }

    .btn-secondary:hover {
        background: #e5e7eb;
    }

    .btn-danger {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
    }

    .btn-danger:hover {
        background: #fee2e2;
    }

    .btn-sm {
        padding: 5px 12px;
        font-size: 12px;
    }

    .form-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        padding: 24px;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-group label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: #374151;
        margin-bottom: 6px;
    }

    .form-control {
        width: 100%;
        padding: 10px 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: 14px;
        transition: border-color 0.15s;
        outline: none;
        box-sizing: border-box;
    }

    .form-control:focus {
        border-color: #eab308;
        box-shadow: 0 0 0 3px rgba(234, 179, 8, 0.15);
    }

    .perm-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 8px;
    }

    .perm-item {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.15s;
    }

    .perm-item:hover {
        background: #fffbeb;
        border-color: #fbbf24;
    }

    .perm-item input[type=checkbox] {
        width: 16px;
        height: 16px;
        accent-color: #eab308;
        cursor: pointer;
    }

    .perm-item.checked {
        background: #fffbeb;
        border-color: #fbbf24;
    }

    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.4);
        z-index: 100;
        align-items: center;
        justify-content: center;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-content {
        background: white;
        border-radius: 16px;
        padding: 28px;
        width: 100%;
        max-width: 560px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }
</style>

<?php if ($msg_success): ?>
    <div class="bg-green-50 border border-green-200 text-green-700 px-5 py-3 rounded-lg mb-6 flex items-center gap-2">
        <span>✅</span>
        <?= $msg_success ?>
    </div>
<?php endif; ?>
<?php if ($msg_error): ?>
    <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-3 rounded-lg mb-6 flex items-center gap-2">
        <span>❌</span>
        <?= htmlspecialchars($msg_error) ?>
    </div>
<?php endif; ?>

<?php if ($action === 'list'): ?>
    <!-- ==================== DAFTAR PENGGUNA ==================== -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Daftar Pengguna</h2>
            <p class="text-sm text-gray-500 mt-1">Kelola akun admin dan hak akses menu</p>
        </div>
        <a href="?page=users&action=add" class="btn btn-primary">➕ Tambah Pengguna</a>
    </div>

    <div class="form-card overflow-x-auto">
        <table class="user-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Username</th>
                    <th>Nama Tampilan</th>
                    <th>Role</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $i => $u): ?>
                    <tr>
                        <td class="text-gray-400">
                            <?= $i + 1 ?>
                        </td>
                        <td class="font-medium text-gray-800">
                            <?= htmlspecialchars($u['username']) ?>
                        </td>
                        <td class="text-gray-600">
                            <?= htmlspecialchars($u['display_name'] ?: '-') ?>
                        </td>
                        <td>
                            <span class="badge <?= $u['role'] === 'superadmin' ? 'badge-superadmin' : 'badge-admin' ?>">
                                <?= $u['role'] === 'superadmin' ? '⭐ Superadmin' : '👤 Admin' ?>
                            </span>
                        </td>
                        <td class="text-gray-500 text-sm">
                            <?= formatDate($u['created_at']) ?>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;">
                                <a href="?page=users&action=edit&id=<?= $u['id'] ?>" class="btn btn-secondary btn-sm">✏️
                                    Edit</a>
                                <?php if ($u['id'] != $current_admin_id): ?>
                                    <button type="button"
                                        onclick="confirmDelete(<?= $u['id'] ?>, '<?= htmlspecialchars($u['username'], ENT_QUOTES) ?>')"
                                        class="btn btn-danger btn-sm">🗑️ Hapus</button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal Konfirmasi Hapus -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal-content" style="max-width:400px;">
            <h3 class="text-lg font-semibold text-gray-800 mb-2">Konfirmasi Hapus</h3>
            <p class="text-gray-600 mb-5">Yakin ingin menghapus pengguna <strong id="deleteUsername"></strong>?</p>
            <form method="POST" action="admin.php?page=users" id="deleteForm">
                <input type="hidden" name="action" value="delete_user">
                <input type="hidden" name="user_id" id="deleteUserId">
                <div style="display:flex;gap:10px;justify-content:flex-end;">
                    <button type="button" onclick="closeDeleteModal()" class="btn btn-secondary">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function confirmDelete(id, username) {
            document.getElementById('deleteUserId').value = id;
            document.getElementById('deleteUsername').textContent = username;
            document.getElementById('deleteModal').classList.add('active');
        }
        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.remove('active');
        }
        document.getElementById('deleteModal').addEventListener('click', function (e) {
            if (e.target === this) closeDeleteModal();
        });
    </script>

<?php elseif ($action === 'add'): ?>
    <!-- ==================== TAMBAH PENGGUNA ==================== -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Tambah Pengguna Baru</h2>
            <p class="text-sm text-gray-500 mt-1">Buat akun admin baru dengan hak akses menu</p>
        </div>
        <a href="?page=users" class="btn btn-secondary">← Kembali</a>
    </div>

    <div class="form-card" style="max-width:700px;">
        <form method="POST" action="admin.php?page=users" autocomplete="off">
            <input type="hidden" name="action" value="add_user">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label>Username <span style="color:#dc2626;">*</span></label>
                    <input type="text" name="username" class="form-control" placeholder="contoh: john_doe" required
                        pattern="[a-zA-Z0-9_]+" title="Hanya huruf, angka, dan underscore">
                </div>
                <div class="form-group">
                    <label>Nama Tampilan</label>
                    <input type="text" name="display_name" class="form-control" placeholder="contoh: John Doe">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label>Password <span style="color:#dc2626;">*</span></label>
                    <input type="password" name="password" class="form-control" placeholder="Minimal 4 karakter" required
                        minlength="4">
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password <span style="color:#dc2626;">*</span></label>
                    <input type="password" name="confirm" class="form-control" placeholder="Ulangi password" required>
                </div>
            </div>

            <div class="form-group">
                <label>Role</label>
                <select name="role" class="form-control">
                    <option value="admin">👤 Admin (akses sesuai permissions)</option>
                    <option value="superadmin">⭐ Superadmin (akses semua menu)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Hak Akses Menu</label>
                <p style="font-size:12px;color:#9ca3af;margin-bottom:10px;">Centang menu yang boleh diakses oleh pengguna
                    ini. Superadmin otomatis bisa akses semua.</p>
                <div style="margin-bottom:8px;">
                    <label
                        style="display:inline-flex;align-items:center;gap:6px;font-size:12px;cursor:pointer;color:#6b7280;">
                        <input type="checkbox" id="checkAll" onclick="toggleAll(this)"> <strong>Pilih Semua</strong>
                    </label>
                </div>
                <div class="perm-grid">
                    <?php foreach ($all_pages as $key => $label): ?>
                        <label class="perm-item" id="perm-<?= $key ?>">
                            <input type="checkbox" name="permissions[]" value="<?= $key ?>" checked
                                onchange="updatePermStyle(this, '<?= $key ?>')">
                            <span>
                                <?= $label ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
                <a href="?page=users" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">💾 Simpan Pengguna</button>
            </div>
        </form>
    </div>

    <script>
        function toggleAll(master) {
            document.querySelectorAll('input[name="permissions[]"]').forEach(function (cb) {
                cb.checked = master.checked;
                updatePermStyle(cb, cb.value);
            });
        }
        function updatePermStyle(cb, key) {
            var el = document.getElementById('perm-' + key);
            if (el) {
                if (cb.checked) el.classList.add('checked');
                else el.classList.remove('checked');
            }
        }
        // Init styles
        document.querySelectorAll('input[name="permissions[]"]').forEach(function (cb) {
            updatePermStyle(cb, cb.value);
        });
    </script>

<?php elseif ($action === 'edit' && $edit_user): ?>
    <!-- ==================== EDIT PENGGUNA ==================== -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Edit Pengguna:
                <?= htmlspecialchars($edit_user['username']) ?>
            </h2>
            <p class="text-sm text-gray-500 mt-1">Ubah informasi dan hak akses pengguna</p>
        </div>
        <a href="?page=users" class="btn btn-secondary">← Kembali</a>
    </div>

    <div class="form-card" style="max-width:700px;">
        <form method="POST" action="admin.php?page=users" autocomplete="off">
            <input type="hidden" name="action" value="edit_user">
            <input type="hidden" name="user_id" value="<?= $edit_user['id'] ?>">

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($edit_user['username']) ?>" disabled
                        style="background:#f3f4f6;color:#9ca3af;">
                    <p style="font-size:11px;color:#9ca3af;margin-top:4px;">Username tidak bisa diubah</p>
                </div>
                <div class="form-group">
                    <label>Nama Tampilan</label>
                    <input type="text" name="display_name" class="form-control"
                        value="<?= htmlspecialchars($edit_user['display_name'] ?? '') ?>" placeholder="contoh: John Doe">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label>Password Baru <span style="color:#9ca3af;font-size:11px;">(kosongkan jika tidak
                            diubah)</span></label>
                    <input type="password" name="password" class="form-control" placeholder="Password baru">
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password Baru</label>
                    <input type="password" name="confirm" class="form-control" placeholder="Ulangi password baru">
                </div>
            </div>

            <div class="form-group">
                <label>Role</label>
                <select name="role" class="form-control">
                    <option value="admin" <?= ($edit_user['role'] ?? 'admin') === 'admin' ? 'selected' : '' ?>>👤 Admin
                    </option>
                    <option value="superadmin" <?= ($edit_user['role'] ?? '') === 'superadmin' ? 'selected' : '' ?>>⭐
                        Superadmin</option>
                </select>
            </div>

            <div class="form-group">
                <label>Hak Akses Menu</label>
                <p style="font-size:12px;color:#9ca3af;margin-bottom:10px;">Centang menu yang boleh diakses oleh pengguna
                    ini.</p>
                <div style="margin-bottom:8px;">
                    <label
                        style="display:inline-flex;align-items:center;gap:6px;font-size:12px;cursor:pointer;color:#6b7280;">
                        <input type="checkbox" id="checkAllEdit" onclick="toggleAllEdit(this)"> <strong>Pilih Semua</strong>
                    </label>
                </div>
                <div class="perm-grid">
                    <?php foreach ($all_pages as $key => $label):
                        $checked = !empty($edit_perms[$key]);
                        ?>
                        <label class="perm-item <?= $checked ? 'checked' : '' ?>" id="perm-<?= $key ?>">
                            <input type="checkbox" name="permissions[]" value="<?= $key ?>" <?= $checked ? 'checked' : '' ?>
                            onchange="updatePermStyleEdit(this, '
                    <?= $key ?>')">
                            <span>
                                <?= $label ?>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
                <a href="?page=users" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
            </div>
        </form>
    </div>

    <script>
        function toggleAllEdit(master) {
            document.querySelectorAll('input[name="permissions[]"]').forEach(function (cb) {
                cb.checked = master.checked;
                updatePermStyleEdit(cb, cb.value);
            });
        }
        function updatePermStyleEdit(cb, key) {
            var el = document.getElementById('perm-' + key);
            if (el) {
                if (cb.checked) el.classList.add('checked');
                else el.classList.remove('checked');
            }
        }
    </script>

<?php endif; ?>