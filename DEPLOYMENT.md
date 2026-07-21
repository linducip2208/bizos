# BizOS — Deployment Guide

## Requirements

- PHP 8.2+
- MySQL 8.0+
- Node.js 20+
- Composer 2+
- Nginx or Apache
- Supervisor (for queue workers)
- SSL certificate (for production)

---

## Step 1 — Clone Repository

```bash
git clone <your-repo-url> /var/www/bizos
cd /var/www/bizos
```

---

## Step 2 — Install Dependencies

```bash
composer install --no-dev --optimize-autoloader
```

---

## Step 3 — Environment Configuration

```bash
cp .env.example .env
```

Edit `.env` and configure:

| Variable | Description |
|---|---|
| `APP_URL` | Your domain, e.g. `https://bizos.example.com` |
| `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | MySQL credentials |
| `MAIL_*` | SMTP settings for email |
| `AI_PROVIDER_*` | AI provider credentials (optional) |
| `WA_*` | WhatsApp gateway credentials (optional) |

Generate app key:

```bash
php artisan key:generate
```

---

## Step 4 — Database Migration

```bash
php artisan migrate --force
```

---

## Step 5 — Seed Data (Optional)

```bash
php artisan db:seed
```

---

## Step 6 — Build Frontend Assets

```bash
npm install
npm run build
```

---

## Step 7 — Filesystem Permissions

```bash
chown -R www-data:www-data /var/www/bizos
chmod -R 775 /var/www/bizos/storage
chmod -R 775 /var/www/bizos/bootstrap/cache
```

---

## Step 8 — Create Admin User

```bash
php artisan make:filament-user
```

Follow the prompts to set name, email, and password.

---

## Step 9 — Storage Symlink

```bash
php artisan storage:link
```

---

## Step 10 — Queue Worker (Supervisor)

Install Supervisor:

```bash
apt-get install supervisor
```

Copy the supervisor config:

```bash
cp deploy/supervisor.conf /etc/supervisor/conf.d/bizos.conf
supervisorctl reread
supervisorctl update
supervisorctl start bizos-worker:*
```

---

## Step 11 — Scheduler (Crontab)

Add to crontab (`crontab -e`):

```
* * * * * php /var/www/bizos/artisan schedule:run >> /dev/null 2>&1
```

---

## Step 12 — Nginx Configuration

Copy the Nginx config template:

```bash
cp deploy/nginx.conf /etc/nginx/sites-available/bizos
```

Edit `server_name` to match your domain, then enable:

```bash
ln -s /etc/nginx/sites-available/bizos /etc/nginx/sites-enabled/
nginx -t
systemctl reload nginx
```

For SSL, use Certbot:

```bash
certbot --nginx -d your-domain.com
```

---

## Step 13 — Cache Optimization

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## Post-Deployment Checklist

- [ ] `.env` configured with correct DB, mail, and APP_URL
- [ ] Database migrated successfully
- [ ] Frontend assets built (`npm run build`)
- [ ] Storage symlink created
- [ ] Queue worker running (`supervisorctl status bizos-worker:*`)
- [ ] Scheduler crontab active
- [ ] Nginx configured and SSL active
- [ ] Admin user created and can log in at `/admin`
- [ ] Mail sending tested
- [ ] Backup strategy in place (database + storage)
- [ ] Log rotation configured (`/var/www/bizos/storage/logs/`)
- [ ] Firewall allows ports 80, 443
