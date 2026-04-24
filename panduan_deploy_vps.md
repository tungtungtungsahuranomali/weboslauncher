# Panduan Deploy AHFix ke VPS Klien (FINAL)

> [!CAUTION]
> VPS milik klien. **JANGAN** edit/hapus config existing (`ahf-app-2`, `ahf-server`, `ligat`).

## Info VPS

| Item | Nilai |
|---|---|
| IP | `76.13.23.233` |
| SSH Port | `22` |
| PHP | `8.4` (socket: `php8.4-fpm.sock`) |
| Nginx sites aktif | `ahf-server` (port 80/8080), `ahf-app-2` (port 8081), `ligat` (port 80/443) |

## Info Project Baru

| Item | Nilai |
|---|---|
| Folder | `/var/www/barelang` |
| Database | `db_barelang` |
| DB User | `barelang_user` |
| DB Pass | `barelang_pass` |
| Port Nginx | `8082` |
| Port WebSocket | `9082` |
| Akses | `http://76.13.23.233:8082/` |

---

## Langkah 1: Login SSH

```bash
ssh root@76.13.23.233
```

---

## Langkah 2: Buat Database

```bash
sudo mysql -u root -p
```

```sql
CREATE DATABASE db_barelang CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'barelang_user'@'localhost' IDENTIFIED BY 'barelang_pass';
GRANT ALL PRIVILEGES ON db_barelang.* TO 'barelang_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

---

## Langkah 3: Import Database

1. Export dari XAMPP: phpMyAdmin → database `take_off` → **Export** → SQL → Go
2. Upload file `.sql` ke VPS via WinSCP, taruh di `/tmp/`
3. Import & hapus:

```bash
mysql -u barelang_user -p db_barelang < /tmp/take_off.sql
rm /tmp/take_off.sql
```

---

## Langkah 4: Buat Folder & Upload via WinSCP

```bash
sudo mkdir -p /var/www/barelang
```

**WinSCP:**

| Field | Nilai |
|---|---|
| Protocol | SFTP |
| Host | `76.13.23.233` |
| Port | `22` |
| User | `root` |

- Panel kiri → `D:\JOKO\xampp8.2\htdocs\AHFix`
- Panel kanan → `/var/www/barelang`
- Select All → Upload
- **Jangan upload:** `vendor/`, `.vscode/`, `run_worker.bat`

---

## Langkah 5: Install Dependencies

```bash
cd /var/www/barelang
composer install --no-dev
```

> [!NOTE]
> Jika Composer belum ada:
> ```bash
> curl -sS https://getcomposer.org/installer | php
> sudo mv composer.phar /usr/local/bin/composer
> ```

---

## Langkah 6: Edit config.php

```bash
nano /var/www/barelang/config.php
```

Ubah menjadi:

```php
<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'barelang_user');
define('DB_PASS', 'barelang_pass');
define('DB_NAME', 'db_barelang');

define('BASE_URL', 'http://76.13.23.233:8082/');

define('VHP_USER', 'vhp_admin');
define('VHP_PASS', 'PassHotelRahasia123!');

function init_db_connection()
{
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    }
    catch (PDOException $e) {
        error_log("Database Connection Error: " . $e->getMessage());
        return null;
    }
}
?>
```

---

## Langkah 7: Atur Permission

```bash
sudo chown -R www-data:www-data /var/www/barelang
sudo chmod -R 755 /var/www/barelang
sudo chmod -R 775 /var/www/barelang/uploads
sudo chmod -R 775 /var/www/barelang/update
```

---

## Langkah 8: Konfigurasi Nginx (Port 8082)

```bash
sudo nano /etc/nginx/sites-available/barelang
```

Paste seluruh isi:

```nginx
server {
    listen 8082;
    server_name 76.13.23.233;

    root /var/www/barelang;
    index index.php index.html;

    location = /checkin {
        rewrite ^ /api.php?action=vhp_checkin last;
    }
    location = /modify {
        rewrite ^ /api.php?action=vhp_modifyguest last;
    }
    location = /checkout {
        rewrite ^ /api.php?action=vhp_checkout last;
    }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
    }

    location ~ /\. {
        deny all;
    }

    location = /config.php {
        deny all;
    }

    client_max_body_size 50M;
}
```

Aktifkan, test & reload:

```bash
sudo ln -s /etc/nginx/sites-available/barelang /etc/nginx/sites-enabled/barelang
sudo nginx -t
sudo systemctl reload nginx
```

> [!CAUTION]
> Jika symlink sudah ada dari percobaan sebelumnya: `sudo rm /etc/nginx/sites-enabled/barelang` dulu, lalu buat ulang.

Buka port firewall:

```bash
sudo ufw allow 8082/tcp
sudo ufw allow 9082/tcp
```

---

## Langkah 9: Test di Browser

| Test | URL |
|---|---|
| Halaman utama | `http://76.13.23.233:8082/` |
| Admin | `http://76.13.23.233:8082/admin.php` |
| API | `http://76.13.23.233:8082/api.php` |
| VHP Checkin | `http://76.13.23.233:8082/checkin` |

---

## Langkah 10: Setup Worker (Cronjob)

```bash
sudo crontab -e
```

Tambahkan di paling bawah:

```
* * * * * /usr/bin/php /var/www/barelang/worker_start_launcher.php >> /var/log/barelang-worker.log 2>&1
```

Cek:

```bash
sudo crontab -l
tail -f /var/log/barelang-worker.log
```

> [!TIP]
> Untuk interval tiap 30 detik, tambahkan 2 baris:
> ```
> * * * * * /usr/bin/php /var/www/barelang/worker_start_launcher.php >> /var/log/barelang-worker.log 2>&1
> * * * * * sleep 30 && /usr/bin/php /var/www/barelang/worker_start_launcher.php >> /var/log/barelang-worker.log 2>&1
> ```

---

## Langkah 11: Setting WebSocket Port di Admin Panel

Setelah berhasil akses admin, masuk ke **Admin Panel** → **Server Config** dan update:

```sql
-- Atau langsung via MySQL jika admin panel belum bisa diakses:
mysql -u barelang_user -p db_barelang

INSERT INTO system_settings (setting_key, setting_value) VALUES
  ('remote_server_url', 'http://76.13.23.233:8082'),
  ('remote_ws_port', '9082'),
  ('remote_config_version', '1')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

EXIT;
```

| Setting | Nilai |
|---|---|
| `remote_server_url` | `http://76.13.23.233:8082` |
| `remote_ws_port` | `9082` |

> [!NOTE]
> APK akan mengambil config ini otomatis tiap 60 detik via API `getServerConfig`.
> Jika pakai Tailscale: ganti URL ke `http://100.115.233.74:8082`

---

## Menambah Project Baru (Pola yang Sama)

| Project | Folder | Nginx Port | WS Port | Akses |
|---|---|---|---|---|
| Barelang | `/var/www/barelang` | 8082 | 9082 | `http://76.13.23.233:8082/` |
| Yello | `/var/www/yello` | 8083 | 9083 | `http://76.13.23.233:8083/` |
| Project lain | `/var/www/nama` | 8084 | 9084 | `http://76.13.23.233:8084/` |

Ulangi Langkah 2–11 dengan nama folder, database, port yang berbeda.

---

## Checklist

| # | Langkah | Status |
|---|---|---|
| 1 | Login SSH | ⬜ |
| 2 | Buat database `db_barelang` | ⬜ |
| 3 | Import file `.sql` | ⬜ |
| 4 | Buat folder + Upload WinSCP | ⬜ |
| 5 | `composer install` | ⬜ |
| 6 | Edit `config.php` | ⬜ |
| 7 | Atur permission | ⬜ |
| 8 | Nginx config port `8082` + firewall `8082`+`9082` | ⬜ |
| 9 | Test browser | ⬜ |
| 10 | Setup cronjob worker | ⬜ |
| 11 | Setting WebSocket port di admin/database | ⬜ |
