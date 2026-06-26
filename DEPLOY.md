# Panduan Deploy & Auto-Launch IPK webOS TV

## Daftar Isi
- [1. Build IPK](#1-build-ipk)
- [2. Persiapan TV — Developer Mode](#2-persiapan-tv--developer-mode)
- [3. Setup Koneksi CLI ke TV](#3-setup-koneksi-cli-ke-tv)
- [4. Install IPK ke TV](#4-install-ipk-ke-tv)
- [5. Auto-Launch di Boot (EIM)](#5-auto-launch-di-boot-eim)
- [6. Extend Developer Mode Remote](#6-extend-developer-mode-remote)
- [7. Mode Produksi — Alternatif untuk Hotel](#7-mode-produksi--alternatif-untuk-hotel)
- [8. Troubleshooting](#8-troubleshooting)

---

## 1. Build IPK

### Prasyarat
- Node.js + npm
- webOS CLI SDK (`ares`)

```bash
npm install -g @webos-tools/cli
```

### Build

```bash
# Dari folder proyek (yang berisi folder webos/)
ares-package -o . webos/
```

Output: `com.takeoff.launcher_1.0.0_all.ipk`

**Catatan:** Error `rimraf is not a function` bisa diabaikan — IPK tetap valid.

### Verifikasi

```bash
file com.takeoff.launcher_1.0.0_all.ipk
# Output: Debian binary package (format 2.0), with control.tar.gz, data compression gz
```

---

## 2. Persiapan TV — Developer Mode

Developer Mode adalah cara resmi LG untuk deploy app tanpa root.

### Langkah-langkah

1. Buka **LG Content Store** di TV
2. Cari **"Developer Mode"** → Install
3. Buka app Developer Mode
4. Klik **"Enable"**
5. Login dengan **LG Developer Account** (daftar gratis di [webostv.developer.lge.com](https://webostv.developer.lge.com))
6. Catat **IP address** TV yang ditampilkan
7. Set **password** untuk SSH (akan diminta nanti)
8. Pastikan **"Key Server"** dalam keadaan **ON**

### Keterbatasan

| Item | Detail |
|------|--------|
| Masa aktif | **1000 jam** (~41 hari) — bisa di-extend |
| Jumlah TV per akun | **1 TV** — login ke TV lain akan logout TV sebelumnya |
| Expire | Semua app terhapus jika Developer Mode expire |
| Install | Hanya **1 app kustom** yang bisa di-install (yg terakhir) |

---

## 3. Setup Koneksi CLI ke TV

```bash
# Daftarkan TV
ares-setup-device -a <nama_tv> -i <IP_TV> -p 9922

# Masukkan password Developer Mode yang sudah diset di TV

# Verifikasi koneksi
ares-device -i -d <nama_tv>
```

### Konfigurasi SSH Key (agar bisa remote tanpa password)

Setelah `ares-setup-device` berhasil, SSH key otomatis terdaftar di `~/.ssh/`.

```bash
# Test SSH langsung
ssh -p 9922 prisoner@<IP_TV>
```

---

## 4. Install IPK ke TV

```bash
ares-install -d <nama_tv> com.takeoff.launcher_1.0.0_all.ipk
```

### Perintah Lain

```bash
# Uninstall
ares-install -d <nama_tv> --remove com.takeoff.launcher

# Launch app
ares-launch -d <nama_tv> com.takeoff.launcher

# Close app
ares-launch -d <nama_tv> --close com.takeoff.launcher

# Debug dengan Web Inspector
ares-inspect -d <nama_tv> com.takeoff.launcher --app
```

---

## 5. Auto-Launch di Boot (EIM)

### Mekanisme

EIM (External Input Manager) adalah service webOS yang memungkinkan app terdaftar sebagai "input device". TV akan auto-launch input device terakhir saat boot.

### Syarat di `appinfo.json`

```json
{
  "supportGIP": true,
  "noSplashOnLaunch": true,
  "supportQuickStart": true
}
```

### Kode Registrasi EIM

Ada di `webos/index.html` — fungsi `registerEim()`:

```javascript
webOS.service.request("luna://com.webos.service.eim", {
  method: "addDevice",
  parameters: {
    appId: "com.takeoff.launcher",
    pigImage: "icon.png",
    mvpdIcon: "",
    type: "MVPD_IP",
    showPopup: false,
    label: "TakeOff",
    description: "TakeOff Hotel Launcher"
  },
  onSuccess: function() {
    console.log("EIM register success");
    localStorage.setItem("eim_registered", "1");
  },
  onFailure: function(err) {
    console.error("EIM register failed:", err);
  }
});
```

### Catatan Penting

- Registrasi EIM dipanggil **setiap app jalan** (bukan sekali) agar persist meski TV update
- Jika gagal (permission `devices` tidak tersedia), app tetap jalan — hanya tidak auto-launch
- `showPopup: false` agar tidak muncul toast "input changed" setiap boot

---

## 6. Extend Developer Mode Remote

Developer Mode aktif selama **1000 jam**. Bisa di-extend via remote tanpa sentuh TV.

### Cara Kerja

1. SSH ke TV (port 9922)
2. Ambil **session token** dari file system
3. Kirim token ke API LG untuk reset timer

### Manual via SSH

```bash
# Ambil token
token=$(ssh -p 9922 prisoner@<IP_TV> cat /var/luna/preferences/devmode_enabled)

# Kirim ke API LG
curl "https://developer.lge.com/secure/ResetDevModeSession.dev?sessionToken=$token"
```

### Script untuk Semua TV (dari server)

```bash
#!/bin/bash
# extend-all-tv.sh

TV_LIST=("192.168.0.10" "192.168.0.11" "192.168.0.12")
for tv in "${TV_LIST[@]}"; do
  echo "Extending $tv..."
  token=$(ssh -p 9922 -o ConnectTimeout=5 "prisoner@$tv" cat /var/luna/preferences/devmode_enabled 2>/dev/null)
  if [ -n "$token" ]; then
    curl -s --max-time 5 "https://developer.lge.com/secure/ResetDevModeSession.dev?sessionToken=$token"
    echo " -> $tv done"
  else
    echo " -> $tv FAILED (unreachable?)"
  fi
done
```

### Cron Job Otomatis

```bash
# Setiap hari Minggu jam 3 pagi
0 3 * * 0 /path/to/extend-all-tv.sh >> /var/log/extend-tv.log 2>&1
```

### Tools Siap Pakai

| Tool | Link | Deskripsi |
|------|------|-----------|
| `webos-dev-mode` | [github.com/gabe565/webos-dev-mode](https://github.com/gabe565/webos-dev-mode) | CLI extend + cron |
| `lg-webos-devmode-timer-extender` | [github.com/Neur0toxine/lg-webos-devmode-timer-extender](https://github.com/Neur0toxine/lg-webos-devmode-timer-extender) | Go binary, lightweight |
| Script classic | [github.com/webosbrew/dev-goodies](https://github.com/webosbrew/dev-goodies) | Shell script sederhana |

### ⚠️ Peringatan

- TV harus **dalam keadaan on** saat token diambil
- Token berubah setiap TV **restart** — perlu ambil ulang
- Developer Mode harus **pernah di-enable** di TV (setup awal manual)

---

## 7. Mode Produksi — Alternatif untuk Hotel

Developer Mode tidak cocok untuk produksi karena expire. Opsi permanen:

| Metode | TV | Biaya | Auto-Launch | Stabil |
|--------|----|-------|-------------|--------|
| **Developer Mode** | Konsumen (semua) | Gratis | ✅ (via EIM) | ❌ Expire 1000 jam |
| **Commercial Mode + SI Mode** | Seri Signage/Komersial | Gratis | ✅ | ✅ Permanen |
| **Pro:Centric** | Seri Hotel (LG Hotel TV) | Gratis* | ✅ | ✅ Permanen |
| **Root (webosbrew)** | Konsumen (firmware tertentu) | Gratis | ✅ | ⚠️ Resiko brick |

*Pro:Centric server tambahan berbayar, fitur dasar gratis.

### Commercial / SI Mode (Signage TV)

1. Tekan tahan **Settings** di remote 5 detik
2. Masukkan kode `1105`
3. Masuk menu **SI Server Setting**
4. Set **Application Launch Mode** → `Local`
5. Set **Application Type** → `IPK`
6. Copy `.ipk` ke USB → folder `application/` → pilih **Local Application Upgrade** → `USB`
7. Reboot TV — app akan auto-launch setiap boot

### Root (webosbrew)

Cek exploit terbaru di [webosbrew.org](https://www.webosbrew.org). Resiko: void warranty, potensi brick.

---

## 8. Troubleshooting

### "Connection timeout" saat install

```bash
# Cek koneksi
ping <IP_TV>
telnet <IP_TV> 9922

# Pastikan Developer Mode aktif & Key Server ON
```

### "App not found" setelah boot

Developer Mode expire — buka app Developer Mode → **Enable** atau **Extend**.

### EIM registration gagal

```bash
# Cek log EIM service di TV via SSH
ssh -p 9922 prisoner@<IP_TV>
tail -f /var/log/luna.log | grep -i eim
```

### "Permission denied" SSH

```bash
# Setup ulang koneksi
ares-setup-device -d <nama_tv> --remove
ares-setup-device -a <nama_tv> -i <IP_TV> -p 9922
```

### IPK build error "rimraf is not a function"

Bug di ares-package v3.2.4 — IPK tetap berhasil dibuat, abaikan error.

---

## Referensi

- [webOS Developer Documentation](https://webostv.developer.lge.com)
- [webOS CLI Tools](https://webostv.developer.lge.com/develop/tools/cli-introduction)
- [webOS Developer Mode Guide](https://www.webosbrew.org/devmode/)
- [EIM Service API](https://www.webosbrew.org/pages/luna-service-comwebosserviceeim.html)
- [webOS Homebrew / Root](https://www.webosbrew.org)
