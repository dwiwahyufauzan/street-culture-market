# 12 — Deployment ke Production

## Target Stack Production
- **Server**: VPS Ubuntu 22.04 (Niagahoster/DigitalOcean/Railway)
- **Web server**: Nginx
- **PHP**: PHP 8.2 FPM
- **Database**: MySQL 8.0
- **Storage**: Local atau MinIO (S3-compatible)

---

## Step 1: Persiapkan Server

```bash
# Update sistem
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2 + ekstensi
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring \
    php8.2-xml php8.2-curl php8.2-gd php8.2-zip php8.2-intl \
    php8.2-bcmath php8.2-fileinfo -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js 20 LTS
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install nodejs -y

# Install MySQL
sudo apt install mysql-server -y
sudo mysql_secure_installation

# Install Nginx
sudo apt install nginx -y
```

---

## Step 2: Setup Database MySQL

```sql
CREATE DATABASE street_culture_market CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'scm_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON street_culture_market.* TO 'scm_user'@'localhost';
FLUSH PRIVILEGES;
```

---

## Step 3: Deploy Kode

```bash
# Clone repo (jika pakai Git)
cd /var/www
git clone https://github.com/username/street-culture-market.git
cd street-culture-market

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Copy & edit .env
cp .env.example .env
nano .env
```

### .env Production
```env
APP_NAME="Street Culture Market"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://streetculturemarket.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=street_culture_market
DB_USERNAME=scm_user
DB_PASSWORD=strong_password_here

MIDTRANS_IS_PRODUCTION=true
MIDTRANS_SERVER_KEY=Mid-server-xxxxxxxxxx
MIDTRANS_CLIENT_KEY=Mid-client-xxxxxxxxxx
```

```bash
# Generate key
php artisan key:generate

# Migrasi & seed
php artisan migrate --force
php artisan db:seed --force  # hanya untuk kategori & banner awal

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Storage link
php artisan storage:link

# Permission
sudo chown -R www-data:www-data /var/www/street-culture-market
sudo chmod -R 755 /var/www/street-culture-market
sudo chmod -R 775 storage bootstrap/cache
```

---

## Step 4: Konfigurasi Nginx

```nginx
# /etc/nginx/sites-available/street-culture-market
server {
    listen 80;
    server_name streetculturemarket.com www.streetculturemarket.com;
    root /var/www/street-culture-market/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/street-culture-market /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## Step 5: SSL dengan Let's Encrypt

```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d streetculturemarket.com -d www.streetculturemarket.com
```

---

## Step 6: Setup Queue Worker (untuk email)

```bash
# Buat systemd service
sudo nano /etc/systemd/system/scm-queue.service
```

```ini
[Unit]
Description=Street Culture Market Queue Worker
After=network.target

[Service]
User=www-data
Restart=on-failure
ExecStart=/usr/bin/php /var/www/street-culture-market/artisan queue:work --sleep=3 --tries=3 --max-time=3600

[Install]
WantedBy=multi-user.target
```

```bash
sudo systemctl enable scm-queue
sudo systemctl start scm-queue
```

---

## Step 7: Cron Job (untuk scheduled tasks)

```bash
sudo crontab -e -u www-data
# Tambahkan:
* * * * * cd /var/www/street-culture-market && php artisan schedule:run >> /dev/null 2>&1
```

---

## Checklist Pre-Launch

- [ ] `.env APP_DEBUG=false`
- [ ] `.env APP_ENV=production`
- [ ] SSL certificate aktif (HTTPS)
- [ ] Test semua payment method (Midtrans production)
- [ ] Test checkout flow end-to-end
- [ ] Test admin panel `/admin`
- [ ] Test upload gambar produk
- [ ] Pastikan email notifikasi terkirim
- [ ] Setup Google Analytics
- [ ] Submit sitemap ke Google Search Console
- [ ] Test responsivitas di mobile

---

## Alternatif: Deploy ke Railway.app (Mudah)

```bash
# Install Railway CLI
npm install -g @railway/cli

# Login & deploy
railway login
railway init
railway up
```

Railway mendukung PHP + MySQL langsung, cocok untuk awal tanpa perlu setup server sendiri.
