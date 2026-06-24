#!/usr/bin/env bash
# Bootstrap Ubuntu 24.04 para Mi Ropa (Laravel 12) — turopa.com
# Ejecutar como root en VPS Hostinger recién reinstalada.

set -euo pipefail

export DEBIAN_FRONTEND=noninteractive

echo "==> Actualizando paquetes..."
apt-get update
apt-get upgrade -y

echo "==> Instalando dependencias base..."
apt-get install -y \
    curl git unzip zip acl \
    nginx mysql-server redis-server \
    certbot python3-certbot-nginx \
    supervisor

echo "==> PHP 8.3..."
apt-get install -y \
    php8.3-fpm php8.3-cli php8.3-common \
    php8.3-mysql php8.3-xml php8.3-curl php8.3-mbstring \
    php8.3-zip php8.3-gd php8.3-bcmath php8.3-intl php8.3-readline \
    php8.3-redis

echo "==> Composer..."
if ! command -v composer >/dev/null 2>&1; then
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi

echo "==> Node.js 20..."
if ! command -v node >/dev/null 2>&1; then
    curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
    apt-get install -y nodejs
fi

echo "==> Firewall (UFW)..."
apt-get install -y ufw
ufw allow OpenSSH
ufw allow 'Nginx Full'
ufw --force enable

echo "==> MySQL hardening básico..."
systemctl enable mysql
systemctl start mysql

echo "==> Servicios..."
systemctl enable nginx php8.3-fpm redis-server supervisor
systemctl restart nginx php8.3-fpm redis-server supervisor

echo "==> Supervisor — cola Laravel (ajusta usuario/ruta tras clonar repo)..."
cat > /etc/supervisor/conf.d/turopa-worker.conf <<'EOF'
[program:turopa-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/turopa/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=false
autorestart=true
stopasgroup=true
killasgroup=true
user=deploy
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/turopa/storage/logs/worker.log
stopwaitsecs=3600
EOF

supervisorctl reread
supervisorctl update

echo ""
echo "Bootstrap completado."
echo "Siguiente: crear BD MySQL, clonar repo en /var/www/turopa, configurar .env, nginx y certbot."
echo "Ver docs/deploy-turopa-vps.md"
