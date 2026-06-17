# TakeOff - Hotel IPTV Management System

<div align="center">

![Version](https://img.shields.io/badge/version-3.9.1-blue)
![PHP](https://img.shields.io/badge/PHP-8.0+-purple)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange)
![License](https://img.shields.io/badge/license-proprietary-red)

**Sistem manajemen TV hotel dengan antarmuka TV Launcher untuk tamu dan panel admin untuk staff hotel.**

</div>

---

## Language / Bahasa

> **Click to switch:**
> - [English](#english)
> - [Indonesia](#indonesia)

---

# Indonesia

## Ringkasan

TakeOff adalah sistem manajemen IPTV hotel yang terdiri dari:

| Komponen | Deskripsi |
|----------|-----------|
| **TV Launcher** | Antarmuka tamu di TV (Smart TV/Android TV) |
| **Admin Panel** | CMS untuk staff hotel |
| **REST API** | Backend untuk semua komunikasi |
| **VHP Integration** | Integrasi dengan Property Management System |
| **WhatsApp Notification** | Notifikasi pesanan ke staff via WA |

---

## Kebutuhan Sistem

### Local Development

| Tool | Versi Minimum |
|------|---------------|
| Web Server | Apache/Nginx (XAMPP/WAMP) |
| PHP | 7.4+ (lihat ekstensi di bawah) |
| Database | MySQL 5.7+ / MariaDB 10.3+ |

### Production Server

| Tool | Versi Minimum |
|------|---------------|
| OS | Ubuntu 20.04+ / Debian 11+ |
| Web Server | Nginx + PHP-FPM |
| PHP | 7.4+ (pdo_mysql, json, mbstring, curl, zip, xml, gd, fileinfo, openssl) |
| Database | MySQL 8.0+ / MariaDB 10.6+ |
| Composer | 2.x |

> **📌 Ekstensi PHP yang dibutuhkan:**
> - `pdo_mysql` — koneksi database
> - `json` — JSON encode/decode
> - `mbstring` — multibyte string
> - `curl` — WhatsApp API (Fonnte)
> - `zip` — import Excel (PhpSpreadsheet)
> - `xml` / `xmlreader` / `xmlwriter` — PhpSpreadsheet
> - `gd` — upload & resize gambar
> - `fileinfo` — validasi file upload
> - `openssl` — HTTPS/Fonnte API
>
> **Instalasi semua ekstensi (Ubuntu/Debian):**
> ```bash
> sudo apt update
> sudo apt install -y php php-mysql php-json php-mbstring php-curl php-zip php-xml php-gd php-fileinfo openssl
> ```

---

## Instalasi Lokal (XAMPP/WAMP)

### Langkah 1: Copy File

```
C:\xampp\htdocs\takeoff\
```

### Langkah 2: Buat Database

```
phpMyAdmin → New → Buat database: takeoff_new → Import take_off.sql
```

### Langkah 3: Edit Konfigurasi

```php
<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'takeoff_new');

define('BASE_URL', 'http://localhost/takeoff/');

define('VHP_USER', 'vhp_admin');
define('VHP_PASS', 'PassHotelRahasia123!');
?>
```

### Langkah 4: Test di Browser

| Halaman | URL |
|---------|-----|
| TV Launcher | http://localhost/takeoff/ |
| Admin Panel | http://localhost/takeoff/admin.php |
| API | http://localhost/takeoff/api.php |

> **Default Login:** `rizal` / `rizal`

---

## Konfigurasi

### File `config.php`

| Konstanta | Deskripsi | Default |
|-----------|-----------|---------|
| `DB_HOST` | Host database | `localhost` |
| `DB_USER` | Username database | `root` |
| `DB_PASS` | Password database | `''` |
| `DB_NAME` | Nama database | `takeoff_new` |
| `BASE_URL` | URL dasar aplikasi | `http://localhost/takeoff/` |
| `VHP_USER` | User PMS integration | `vhp_admin` |
| `VHP_PASS` | Password PMS integration | `PassHotelRahasia123!` |

### Konfigurasi via Database

```sql
-- Settings utama
SELECT * FROM global_settings;

-- Settings server/remote
SELECT * FROM system_settings;
```

---

## Struktur Proyek

```
takeoff/
├── index.php                  # TV Launcher (entry point)
├── admin.php                  # Admin Panel
├── api.php                    # REST API
├── config.php                 # Konfigurasi database & app
├── functions.php              # Helper functions
├── VHP.php                    # PMS simulator tool
├── wa_helper.php             # WhatsApp notification
├── checkout_clear_helper.php # Auto-clear TV on checkout
├── take_off.sql               # Database schema
├── .htaccess                  # Apache rewrite rules
│
├── api/                       # API sub-modules
│   ├── adb_helper.php
│   ├── device_helper.php
│   ├── flashscreen.php
│   ├── clear_data.php
│   ├── getDining.php
│   ├── getFacilities.php
│   ├── getInfo.php
│   ├── posOrder.php
│   └── api.php
│
├── pages/                     # Admin page modules
│   ├── dashboard.php
│   ├── devices.php
│   ├── checkin.php
│   ├── dining.php
│   ├── amenities.php
│   ├── facilities.php
│   ├── information.php
│   ├── promotion.php
│   ├── app_control.php
│   ├── running_text.php
│   ├── flashscreen.php
│   ├── server_config.php
│   ├── iptv.php
│   ├── users.php
│   └── login.php
│
├── js/                        # JavaScript
├── img/                       # Gambar static
├── uploads/                   # File upload
│   ├── flashscreen/
│   ├── update/
│   ├── dining/
│   ├── amenities/
│   └── facilities/
├── vendor/                    # Composer dependencies
└── APK/                      # Android APK
```

---

## API Endpoints

Akses: `api.php?action=ACTION_NAME`

### Guest/Fitur

| Action | Deskripsi |
|--------|-----------|
| `checkRegistration` | Cek status registrasi device |
| `getGuestInfo` | Info tamu & kamar |
| `getMarqueeText` | Ambil teks berjalan |
| `getAppVisibility` | Get visible menu apps |
| `getFacilities` | Daftar fasilitas hotel |
| `getInfo` | Informasi hotel |
| `getDining` | Menu dining |
| `getAmenities` | Room amenities |
| `getPromotion` | Promosi |
| `submitDiningOrder` | Submit pesanan makanan |
| `submitAmenityRequest` | Submit request amenities |
| `getNotifications` | Ambil notifikasi push |
| `get_channels` | List channel IPTV |

### PMS Integration (VHP)

| Action | URL |
|--------|-----|
| Check-in | `/checkin` |
| Modify Guest | `/modify` |
| Checkout | `/checkout` |

---

## VPS/Server Installation

### Prerequisites

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx php8.1-fpm php8.1-cli php8.1-mysql php8.1-mbstring php8.1-curl mysql-server mysql-client composer
```

### Langkah 1: Buat Database

```sql
CREATE DATABASE takeoff_new CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'takeoff_user'@'localhost' IDENTIFIED BY 'takeoff_password';
GRANT ALL PRIVILEGES ON takeoff_new.* TO 'takeoff_user'@'localhost';
FLUSH PRIVILEGES;
```

### Langkah 2: Upload & Import

```bash
sudo mkdir -p /var/www/takeoff
mysql -u takeoff_user -p takeoff_new < take_off.sql
```

### Langkah 3: Install Dependencies

```bash
cd /var/www/takeoff
composer install --no-dev
```

### Langkah 4: Edit config.php

```php
<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'takeoff_user');
define('DB_PASS', 'takeoff_password');
define('DB_NAME', 'takeoff_new');

define('BASE_URL', 'http://YOUR_SERVER_IP/');

define('VHP_USER', 'vhp_admin');
define('VHP_PASS', 'PassHotelRahasia123!');
?>
```

### Langkah 5: Permissions

```bash
sudo chown -R www-data:www-data /var/www/takeoff
sudo chmod -R 755 /var/www/takeoff
sudo chmod -R 775 /var/www/takeoff/uploads
```

### Langkah 6: Nginx Config

```nginx
server {
    listen 80;
    server_name YOUR_SERVER_IP;

    root /var/www/takeoff;
    index index.php index.html;

    location = /checkin { rewrite ^ /api.php?action=vhp_checkin last; }
    location = /modify  { rewrite ^ /api.php?action=vhp_modifyguest last; }
    location = /checkout{ rewrite ^ /api.php?action=vhp_checkout last; }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
    }

    location ~ /\. { deny all; }
    location = /config.php { deny all; }
    client_max_body_size 50M;
}
```

```bash
sudo ln -s /etc/nginx/sites-available/takeoff /etc/nginx/sites-enabled/takeoff
sudo nginx -t && sudo systemctl reload nginx
```

---

## Panel Admin Pages

| Halaman | Fungsi |
|---------|--------|
| Dashboard | Overview sistem |
| Devices | Manajemen TV devices |
| Check-in | Check-in/out tamu |
| Dining | Manage menu makanan |
| Amenities | Manage amenities kamar |
| Facilities | Manage fasilitas hotel |
| Information | Info hotel |
| Promotions | Manage promosi |
| App Control | Enable/disable app di TV |
| Running Text | Teks berjalan |
| Flashscreen | Splash screen |
| Server Config | Konfigurasi server |
| IPTV | Manage channel |
| Users | Manajemen user admin |

---

## Troubleshooting

| Error | Solusi |
|-------|--------|
| Database Connection Failed | Cek `config.php` credentials |
| 404 Page Not Found | Enable mod_rewrite: `sudo a2enmod rewrite` |
| Permission Denied | `sudo chown -R www-data:www-data /var/www/takeoff` |
| APK Cannot Connect | Cek `BASE_URL` & firewall port 80/443 |
| WhatsApp Error | Cek `wa_fonnte_token` di `system_settings` |

---

# English

## Overview

TakeOff is a hotel IPTV management system with:

| Component | Description |
|-----------|-------------|
| **TV Launcher** | Guest interface on Smart TV/Android TV |
| **Admin Panel** | CMS for hotel staff |
| **REST API** | Backend for all communications |
| **VHP Integration** | Property Management System integration |
| **WhatsApp Notification** | Order notifications via WA |

---

## System Requirements

### Local Development

| Tool | Minimum Version |
|------|-----------------|
| Web Server | Apache/Nginx (XAMPP/WAMP) |
| PHP | 7.4+ (see extensions below) |
| Database | MySQL 5.7+ / MariaDB 10.3+ |

### Production Server

| Tool | Minimum Version |
|------|-----------------|
| OS | Ubuntu 20.04+ / Debian 11+ |
| Web Server | Nginx + PHP-FPM |
| PHP | 7.4+ (pdo_mysql, json, mbstring, curl, zip, xml, gd, fileinfo, openssl) |
| Database | MySQL 8.0+ / MariaDB 10.6+ |
| Composer | 2.x |

> **📌 Required PHP extensions:**
> - `pdo_mysql` — database connection
> - `json` — JSON encode/decode
> - `mbstring` — multibyte string
> - `curl` — WhatsApp API (Fonnte)
> - `zip` — Excel import (PhpSpreadsheet)
> - `xml` / `xmlreader` / `xmlwriter` — PhpSpreadsheet
> - `gd` — image upload & resize
> - `fileinfo` — file upload validation
> - `openssl` — HTTPS/Fonnte API
>
> **Install all extensions (Ubuntu/Debian):**
> ```bash
> sudo apt update
> sudo apt install -y php php-mysql php-json php-mbstring php-curl php-zip php-xml php-gd php-fileinfo openssl
> ```

---

## Local Installation (XAMPP)

### Step 1: Copy Files

```
C:\xampp\htdocs\takeoff\
```

### Step 2: Create Database

```
phpMyAdmin → New → Create database: takeoff_new → Import take_off.sql
```

### Step 3: Edit Configuration

```php
<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'takeoff_new');

define('BASE_URL', 'http://localhost/takeoff/');

define('VHP_USER', 'vhp_admin');
define('VHP_PASS', 'PassHotelRahasia123!');
?>
```

### Step 4: Test in Browser

| Page | URL |
|------|-----|
| TV Launcher | http://localhost/takeoff/ |
| Admin Panel | http://localhost/takeoff/admin.php |
| API | http://localhost/takeoff/api.php |

> **Default Login:** `rizal` / `rizal`

---

## Configuration

### File `config.php`

| Constant | Description | Default |
|----------|-------------|---------|
| `DB_HOST` | Database host | `localhost` |
| `DB_USER` | Database username | `root` |
| `DB_PASS` | Database password | `''` |
| `DB_NAME` | Database name | `takeoff_new` |
| `BASE_URL` | Application base URL | `http://localhost/takeoff/` |
| `VHP_USER` | PMS integration user | `vhp_admin` |
| `VHP_PASS` | PMS integration password | `PassHotelRahasia123!` |

### Database-based Configuration

```sql
-- Main settings
SELECT * FROM global_settings;

-- Server/remote settings
SELECT * FROM system_settings;
```

---

## Project Structure

```
takeoff/
├── index.php                  # TV Launcher (entry point)
├── admin.php                  # Admin Panel
├── api.php                    # REST API
├── config.php                 # Database & app configuration
├── functions.php              # Helper functions
├── VHP.php                    # PMS simulator tool
├── wa_helper.php             # WhatsApp notification
├── checkout_clear_helper.php # Auto-clear TV on checkout
├── take_off.sql               # Database schema
├── .htaccess                  # Apache rewrite rules
│
├── api/                       # API sub-modules
│   ├── adb_helper.php
│   ├── device_helper.php
│   ├── flashscreen.php
│   ├── clear_data.php
│   ├── getDining.php
│   ├── getFacilities.php
│   ├── getInfo.php
│   ├── posOrder.php
│   └── api.php
│
├── pages/                     # Admin page modules
│   ├── dashboard.php
│   ├── devices.php
│   ├── checkin.php
│   ├── dining.php
│   ├── amenities.php
│   ├── facilities.php
│   ├── information.php
│   ├── promotion.php
│   ├── app_control.php
│   ├── running_text.php
│   ├── flashscreen.php
│   ├── server_config.php
│   ├── iptv.php
│   ├── users.php
│   └── login.php
│
├── js/                        # JavaScript
├── img/                       # Static images
├── uploads/                   # File uploads
│   ├── flashscreen/
│   ├── update/
│   ├── dining/
│   ├── amenities/
│   └── facilities/
├── vendor/                    # Composer dependencies
└── APK/                      # Android APK
```

---

## API Endpoints

Access: `api.php?action=ACTION_NAME`

### Guest/Feature

| Action | Description |
|--------|-------------|
| `checkRegistration` | Check device registration status |
| `getGuestInfo` | Get guest & room info |
| `getMarqueeText` | Get running text |
| `getAppVisibility` | Get visible menu apps |
| `getFacilities` | Hotel facilities list |
| `getInfo` | Hotel information |
| `getDining` | Dining menu |
| `getAmenities` | Room amenities |
| `getPromotion` | Promotions |
| `submitDiningOrder` | Submit food order |
| `submitAmenityRequest` | Submit amenity request |
| `getNotifications` | Get push notifications |
| `get_channels` | IPTV channel list |

### PMS Integration (VHP)

| Action | URL |
|--------|-----|
| Check-in | `/checkin` |
| Modify Guest | `/modify` |
| Checkout | `/checkout` |

---

## VPS/Server Installation

### Prerequisites

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx php8.1-fpm php8.1-cli php8.1-mysql php8.1-mbstring php8.1-curl mysql-server mysql-client composer
```

### Step 1: Create Database

```sql
CREATE DATABASE takeoff_new CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'takeoff_user'@'localhost' IDENTIFIED BY 'takeoff_password';
GRANT ALL PRIVILEGES ON takeoff_new.* TO 'takeoff_user'@'localhost';
FLUSH PRIVILEGES;
```

### Step 2: Upload & Import

```bash
sudo mkdir -p /var/www/takeoff
mysql -u takeoff_user -p takeoff_new < take_off.sql
```

### Step 3: Install Dependencies

```bash
cd /var/www/takeoff
composer install --no-dev
```

### Step 4: Edit config.php

```php
<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'takeoff_user');
define('DB_PASS', 'takeoff_password');
define('DB_NAME', 'takeoff_new');

define('BASE_URL', 'http://YOUR_SERVER_IP/');

define('VHP_USER', 'vhp_admin');
define('VHP_PASS', 'PassHotelRahasia123!');
?>
```

### Step 5: Permissions

```bash
sudo chown -R www-data:www-data /var/www/takeoff
sudo chmod -R 755 /var/www/takeoff
sudo chmod -R 775 /var/www/takeoff/uploads
```

### Step 6: Nginx Config

```nginx
server {
    listen 80;
    server_name YOUR_SERVER_IP;

    root /var/www/takeoff;
    index index.php index.html;

    location = /checkin { rewrite ^ /api.php?action=vhp_checkin last; }
    location = /modify  { rewrite ^ /api.php?action=vhp_modifyguest last; }
    location = /checkout{ rewrite ^ /api.php?action=vhp_checkout last; }

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.1-fpm.sock;
    }

    location ~ /\. { deny all; }
    location = /config.php { deny all; }
    client_max_body_size 50M;
}
```

```bash
sudo ln -s /etc/nginx/sites-available/takeoff /etc/nginx/sites-enabled/takeoff
sudo nginx -t && sudo systemctl reload nginx
```

---

## Admin Panel Pages

| Page | Function |
|------|----------|
| Dashboard | System overview |
| Devices | TV device management |
| Check-in | Guest check-in/out |
| Dining | Food menu management |
| Amenities | Room amenities management |
| Facilities | Hotel facilities management |
| Information | Hotel information |
| Promotions | Promotion management |
| App Control | Enable/disable TV apps |
| Running Text | Running text management |
| Flashscreen | Splash screen |
| Server Config | Server configuration |
| IPTV | Channel management |
| Users | Admin user management |

---

## Troubleshooting

| Error | Solution |
|-------|----------|
| Database Connection Failed | Check `config.php` credentials |
| 404 Page Not Found | Enable mod_rewrite: `sudo a2enmod rewrite` |
| Permission Denied | `sudo chown -R www-data:www-data /var/www/takeoff` |
| APK Cannot Connect | Check `BASE_URL` & firewall port 80/443 |
| WhatsApp Error | Check `wa_fonnte_token` in `system_settings` |

---

<div align="center">

## License

**Proprietary** - All rights reserved

</div>
