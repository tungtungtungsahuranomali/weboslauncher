# Marketing Server — server4.hostdata.id

## Topologi

```
Local (Debian 13)
    ↓ SSH (key: ai_rsa, passphrase: 123qwe, user: bikinweb)
Target (82.25.62.13) — server4.hostdata.id
    ├── DirectAdmin + CloudLinux
    ├── PHP 8.2.29 (alt-php82)
    └── Web: Apache + PHP-FPM
```

## One-Time Setup

Jalankan ini sekali saja untuk setup SSH agent + key:

```bash
echo '#!/bin/sh
echo "123qwe"' > /tmp/askpass.sh && chmod +x /tmp/askpass.sh

cat >> ~/.bashrc <<'EOF'
# --- SSH agent untuk marketing server ---
if [ -z "$SSH_AUTH_SOCK" ]; then
  eval $(ssh-agent -s) > /dev/null
  SSH_ASKPASS=/tmp/askpass.sh SSH_ASKPASS_REQUIRE=force setsid ssh-add /root/ligatwebid/ai_rsa 2>/dev/null
fi
EOF

source ~/.bashrc
```

## Daily Usage

### SSH

```bash
ssh bikinweb@82.25.62.13
```

### Cek folder domain

```bash
ssh bikinweb@82.25.62.13 'ls -la /home/bikinweb/domains/'
```

### Cek isi domain tertentu

```bash
ssh bikinweb@82.25.62.13 'ls -la /home/bikinweb/domains/ligat.web.id/public_html/'
```

### Copy file

```bash
scp /path/to/file bikinweb@82.25.62.13:/home/bikinweb/domains/ligat.web.id/public_html/
```

### MySQL query

```bash
ssh bikinweb@82.25.62.13 "mysql -u bikinweb_ligatweb -p123qwe123qwe bikinweb_ligatweb -e 'SHOW TABLES;'"
```

### Cek PHP config via web

```bash
ssh bikinweb@82.25.62.13 "cat > /home/bikinweb/domains/DOMAINANDA/public_html/cek.php << 'EOF'
<?php header('Content-Type: text/plain');
echo 'upload_max_filesize: ' . ini_get('upload_max_filesize') . PHP_EOL;
echo 'post_max_size: ' . ini_get('post_max_size') . PHP_EOL;
echo 'memory_limit: ' . ini_get('memory_limit') . PHP_EOL;
EOF"
```

### Satu baris (tanpa setup sebelumnya)

```bash
echo '#!/bin/sh
echo "123qwe"' > /tmp/askpass.sh && chmod +x /tmp/askpass.sh && \
eval $(ssh-agent -s) && \
SSH_ASKPASS=/tmp/askpass.sh SSH_ASKPASS_REQUIRE=force setsid ssh-add /root/ligatwebid/ai_rsa && \
ssh bikinweb@82.25.62.13 'hostname && whoami && ls -la /home/bikinweb/domains/' && \
ssh-agent -k
```

## Credentials

| Level | Host | User | Password/Key |
|-------|------|------|-------------|
| SSH | `82.25.62.13` | `bikinweb` | Key: `ai_rsa` (passphrase: `123qwe`) |
| DB | localhost | `bikinweb_ligatweb` | `123qwe123qwe` |
| DB name | `bikinweb_ligatweb` | | |
| DirectAdmin | `https://82.25.62.13:2222` | (via hosting) | (via hosting) |

## Daftar Domain

Semua domain di `/home/bikinweb/domains/`:

- `ligat.web.id` — Laravel app (ISP management)
- `ligat.net`
- `ligatmedan.web.id`
- `takeoff.web.id`
- `bikinweb.web.id`
- `clientmmademo.web.id`
- `kamihaturkan.web.id`
- `sapuin.com`
- `ventipos.web.id`

## Struktur Folder Domain

```
/home/bikinweb/domains/
├── ligat.web.id/
│   ├── public_html/         ← web root
│   │   ├── public/          ← Laravel front controller
│   │   ├── app/
│   │   ├── storage/
│   │   └── ...
│   ├── logs/                ← access logs (Jun-2026.tar.gz, dll)
│   ├── private_html -> ./public_html
│   └── stats/
├── takeoff.web.id/
└── ...
```

## Notes

- **PHP config**: `/opt/alt/php82/etc/php.ini` (root only)
- **Upload limit via DirectAdmin**: PHP1/2 Selector → Settings
- **PHP handler**: PHP-FPM via CloudLinux PHP Selector
- **Server**: `server4.hostdata.id` (DirectAdmin + CloudLinux)
- **PHP**: 8.2.29
- **SSH port**: 22 (standard)
- **Key passphrase**: `123qwe`
