#!/usr/bin/env bash
# =============================================================================
# Puretec Pure POS - Standalone VPS Client Provisioning Script
#
# Usage:
#   sudo bash scripts/provision-client.sh <TENANT_CODE> [SUBDOMAIN] [DB_PASSWORD]
#
# Examples:
#   sudo bash scripts/provision-client.sh TNT-0001
#   sudo bash scripts/provision-client.sh TNT-0001 shop-city
# =============================================================================

set -euo pipefail

# Check root privileges
if [ "$EUID" -ne 0 ]; then
  echo "Error: This script must be run as root (or with sudo)." >&2
  exit 1
fi

TENANT_CODE="${1:-}"
if [ -z "$TENANT_CODE" ]; then
  echo "Usage: sudo bash $0 <TENANT_CODE> [SUBDOMAIN] [DB_PASSWORD]"
  exit 1
fi

# Clean and normalize inputs
TENANT_CODE=$(echo "$TENANT_CODE" | tr '[:lower:]' '[:upper:]')
SLUG_INPUT="${2:-}"
if [ -z "$SLUG_INPUT" ]; then
  SLUG=$(echo "$TENANT_CODE" | tr '[:upper:]' '[:lower:]' | tr -cd 'a-z0-9_-')
else
  SLUG=$(echo "$SLUG_INPUT" | tr '[:upper:]' '[:lower:]' | tr -cd 'a-z0-9_-')
fi

DOMAIN="${SLUG}.puretec.lk"
DB_NAME="purepos_$(echo "$TENANT_CODE" | tr '[:upper:]' '[:lower:]' | tr -c 'a-z0-9' '_')"
DB_USER="purepos_${SLUG:0:16}"
DB_PASS="${3:-$(openssl rand -hex 12)}"

TARGET_DIR="/var/www/${SLUG}"
MASTER_DIR="/var/www/purepos-master"
PHP_FPM_SOCK="/var/run/php/php8.2-fpm.sock"
ADMIN_EMAIL="admin@puretec.lk"

echo "============================================================"
echo "Puretec Pure POS - Provisioning Client: ${TENANT_CODE}"
echo "Subdomain:    https://${DOMAIN}"
echo "Target Dir:   ${TARGET_DIR}"
echo "Database:     ${DB_NAME}"
echo "DB Username:  ${DB_USER}"
echo "============================================================"

# 1. Create MySQL Database and Grant User
echo "[1/6] Creating MySQL database and user..."
mysql -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"
echo "      Database and user configured successfully."

# 2. Deploy Codebase
echo "[2/6] Deploying codebase to ${TARGET_DIR}..."
mkdir -p "${TARGET_DIR}"
if [ -d "${MASTER_DIR}" ]; then
  rsync -av --exclude='.git' \
            --exclude='storage/logs/*' \
            --exclude='storage/framework/cache/data/*' \
            --exclude='storage/framework/sessions/*' \
            --exclude='storage/framework/views/*' \
            --exclude='.env' \
            "${MASTER_DIR}/" "${TARGET_DIR}/"
else
  echo "      Warning: Master directory '${MASTER_DIR}' not found. Current dir will be used as template."
  rsync -av --exclude='.git' \
            --exclude='storage/logs/*' \
            --exclude='storage/framework/cache/data/*' \
            --exclude='storage/framework/sessions/*' \
            --exclude='storage/framework/views/*' \
            --exclude='.env' \
            ./ "${TARGET_DIR}/"
fi

# 3. Create .env
echo "[3/6] Generating environment configuration (.env)..."
APP_KEY="base64:$(openssl rand -base64 32)"
cat << EOF > "${TARGET_DIR}/.env"
APP_NAME="${SLUG} POS"
APP_ENV=production
APP_KEY=${APP_KEY}
APP_DEBUG=false
APP_URL=https://${DOMAIN}

LOG_CHANNEL=stack
LOG_LEVEL=info

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=${DB_NAME}
DB_USERNAME=${DB_USER}
DB_PASSWORD=${DB_PASS}

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=public
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

SANCTUM_STATEFUL_DOMAINS=${DOMAIN}
CORS_ALLOWED_ORIGINS=https://${DOMAIN}

POS_CURRENCY=LKR
POS_CURRENCY_SYMBOL=Rs.
POS_TIMEZONE=Asia/Colombo
EOF

# 4. Run Laravel Setup Commands
echo "[4/6] Running migrations and storage symlinking..."
cd "${TARGET_DIR}"
if [ -f "composer.json" ] && [ ! -d "vendor" ]; then
  echo "      Installing composer dependencies..."
  composer install --no-dev --optimize-autoloader --no-interaction
fi

php artisan key:generate --force
php artisan migrate --force
php artisan storage:link || true
php artisan optimize:clear

# Linux file permissions
chown -R www-data:www-data "${TARGET_DIR}"
chmod -R 775 "${TARGET_DIR}/storage" "${TARGET_DIR}/bootstrap/cache"

# 5. Nginx Server Block Configuration
echo "[5/6] Creating Nginx server configuration..."
cat << EOF > "/etc/nginx/sites-available/${DOMAIN}"
server {
    listen 80;
    listen [::]:80;
    server_name ${DOMAIN};
    return 301 https://\$server_name\$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name ${DOMAIN};

    root ${TARGET_DIR}/public;
    index index.php;

    ssl_certificate /etc/letsencrypt/live/${DOMAIN}/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/${DOMAIN}/privkey.pem;

    client_max_body_size 64M;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:${PHP_FPM_SOCK};
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

ln -sf "/etc/nginx/sites-available/${DOMAIN}" "/etc/nginx/sites-enabled/${DOMAIN}"

if nginx -t > /dev/null 2>&1; then
  systemctl reload nginx
  echo "      Nginx reloaded successfully."
else
  echo "      Warning: Nginx configuration test failed. Check /etc/nginx/sites-available/${DOMAIN}"
fi

# 6. Certbot SSL Configuration
echo "[6/6] Requesting SSL Certificate from Let's Encrypt..."
if command -v certbot > /dev/null 2>&1; then
  certbot --nginx -d "${DOMAIN}" --non-interactive --agree-tos -m "${ADMIN_EMAIL}" --redirect || echo "      Note: Certbot will succeed once DNS A record points to this server IP."
else
  echo "      Certbot is not installed. Run 'sudo apt install certbot python3-certbot-nginx' to enable SSL."
fi

echo "============================================================"
echo "CLIENT PROVISIONING COMPLETED SUCCESSFULLY"
echo "Tenant:       ${TENANT_CODE}"
echo "URL:          https://${DOMAIN}"
echo "Target Dir:   ${TARGET_DIR}"
echo "Database:     ${DB_NAME}"
echo "DB User:      ${DB_USER}"
echo "DB Password:  ${DB_PASS}"
echo "============================================================"

