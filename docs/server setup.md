# 🚀 Laravel VPS Deployment Guide (Single Server)

**Domain:** code-garage.co.za
**Provider:** Domains.co.za
**Stack:** Nginx + PHP 8.2 + PostgreSQL + GitHub CI/CD

---

# 🧭 1. Provision VPS

* Choose **Ubuntu 22.04 LTS**
* Minimum **2GB RAM**
* Ensure:

  * Root SSH access enabled
  * Public IP assigned

```bash
curl ifconfig.me
```
If it returns an IP → ✔ you have a public IP

---

# 🔐 2. Initial Server Setup

## Connect to server

```bash
ssh root@YOUR_SERVER_IP
```

## Update system

```bash
apt update && apt upgrade -y
```

## Create new user

```bash
adduser stephan
usermod -aG sudo stephan
```

---

# 🔑 3. SSH Key Setup (Secure Access)

## On your local machine:

```bash
ssh-keygen -t ed25519 -C "stephan@codegarage.co.za"
ssh-copy-id root@YOUR_SERVER_IP
```

## Copy SSH config to new user:

```bash
rsync --archive --chown=stephan:stephan ~/.ssh /home/stephan
```

## Disable root login & passwords:

```bash
nano /etc/ssh/sshd_config
```

Set:

```
PermitRootLogin no
PasswordAuthentication no
```

Restart SSH:

```bash
systemctl restart ssh
```

---

# 🔥 4. Firewall + Security Hardening

## Install firewall

```bash
apt install ufw fail2ban unattended-upgrades -y
```

## Configure firewall

```bash
ufw default deny incoming
ufw default allow outgoing

ufw allow OpenSSH
ufw allow 80
ufw allow 443
ufw enable
```

## Change SSH port (optional)

```bash
nano /etc/ssh/sshd_config
```

Change:

```
Port 2222
```

```bash
ufw allow 2222/tcp
systemctl restart ssh
```

---

## Fail2Ban setup

```bash
nano /etc/fail2ban/jail.local
```

```
[sshd]
enabled = true
port = 2222
maxretry = 5
```

```bash
systemctl restart fail2ban
```

---

## Enable automatic updates

```bash
dpkg-reconfigure unattended-upgrades
```

---

# 🐘 5. Install PostgreSQL

```bash
apt install postgresql postgresql-contrib -y
```

```bash
sudo -i -u postgres
```

```bash
psql
```

```sql
CREATE DATABASE codegarage;
CREATE USER codeuser WITH PASSWORD 'Star*S3cret';
GRANT ALL PRIVILEGES ON DATABASE codegarage TO codeuser;
```

Exit:

```bash
exit
```

---

# 🧪 6. Install PHP 8.2

```bash
apt install software-properties-common -y
add-apt-repository ppa:ondrej/php -y
apt update
```

```bash
apt install php8.2 php8.2-fpm php8.2-pgsql php8.2-cli \
php8.2-mbstring php8.2-xml php8.2-bcmath php8.2-curl \
php8.2-zip unzip git -y
```

---

# 🌐 7. Install Nginx

```bash
apt install nginx -y
```

---

# 📁 8. Deploy Laravel Project

```bash
cd /var/www
git clone https://github.com/YOUR_USERNAME/YOUR_REPO.git code-garage
cd code-garage
```

## Install Composer

```bash
apt install composer -y
composer install
```

---

# ⚙️ 9. Configure Laravel

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:

```
APP_ENV=production
APP_URL=http://code-garage.co.za

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=codegarage
DB_USERNAME=codeuser
DB_PASSWORD=strongpassword
```

Run:

```bash
php artisan migrate --force
```

---

# 🔐 10. Set Permissions

```bash
chown -R www-data:www-data /var/www/codegarage
chmod -R 775 storage bootstrap/cache
```

---

# 🌍 11. Configure Nginx

```bash
nano /etc/nginx/sites-available/codegarage
```

```nginx
server {
    listen 80;
    server_name code-garage.co.za www.code-garage.co.za;

    root /var/www/codegarage/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Enable site:

```bash
ln -s /etc/nginx/sites-available/codegarage /etc/nginx/sites-enabled/
nginx -t
systemctl restart nginx
```

---

# 🔒 12. Enable HTTPS (SSL)

```bash
apt install certbot python3-certbot-nginx -y
certbot --nginx -d code-garage.co.za -d www.code-garage.co.za
```

---

# 🌐 13. Configure DNS

At Domains.co.za:

* A Record:

```
code-garage.co.za → YOUR_SERVER_IP
```

---

# 🔄 14. CI/CD Auto Deploy (GitHub)

## On server:

```bash
ssh-keygen -t ed25519 -C "deploy@codegarage"
```

Add public key to GitHub (Deploy Keys)

---

## GitHub Action

```yaml
name: Deploy Laravel

on:
  push:
    branches:
      - main

jobs:
  deploy:
    runs-on: ubuntu-latest

    steps:
      - name: Deploy to VPS
        uses: appleboy/ssh-action@v1.0.0
        with:
          host: ${{ secrets.SERVER_IP }}
          username: ${{ secrets.SERVER_USER }}
          key: ${{ secrets.SSH_PRIVATE_KEY }}
          script: |
            cd /var/www/codegarage
            git pull origin main
            composer install --no-dev --optimize-autoloader
            php artisan migrate --force
            php artisan config:cache
            php artisan route:cache
```

---

# 📧 15. Email Setup (Recommended)

Use:

* Mailgun / SendGrid / Amazon SES

Example `.env`:

```
MAIL_MAILER=mailgun
MAIL_FROM_ADDRESS=noreply@code-garage.co.za
MAIL_FROM_NAME="Code Garage"
```

---

# ⚠️ Troubleshooting

## 502 Bad Gateway

* PHP-FPM not running:

```bash
systemctl status php8.2-fpm
```

## Permission issues

```bash
chown -R www-data:www-data /var/www/codegarage
```

## App key missing

```bash
php artisan key:generate
```

## Nginx config issues

```bash
nginx -t
```

---

# ✅ Done

You now have:

* Secure VPS
* Laravel running
* PostgreSQL database
* HTTPS enabled
* Auto-deploy from GitHub
* Firewall protection

---
# 🔧 Redis Setup

Install Redis
```bash
sudo apt install redis-server -y
```

Enable + start:
```bash
sudo systemctl enable redis
sudo systemctl start redis
```

Test:
```bash
redis-cli ping
```

Expected:

`PONG`

Install PHP Redis extension
```bash
sudo apt install php8.2-redis -y
```

Restart PHP:
```bash
sudo systemctl restart php8.2-fpm
```

Laravel `.env` config

```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=sync
```

👉 Note:

- QUEUE_CONNECTION=sync = no queues yet (safe)
- When ready → change to redis
---

# 🚀 Next Up (Later Improvements)

* Add Redis (cache/queues)
* Setup Supervisor (background workers)
* Add monitoring (Uptime checks)
* Separate DB server (scaling)

---
