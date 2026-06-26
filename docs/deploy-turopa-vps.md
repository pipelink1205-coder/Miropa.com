# Deploy Mi Ropa en VPS Hostinger — miropa.com.co

Guía para desplegar el marketplace en la VPS. **Dominio principal:** `miropa.com.co`.

> Si también tienes `turopa.com.co`, ver sección [Qué hacer con turopa.com.co](#qué-hacer-con-turopacomco) al final.

---

## DNS de miropa.com.co (obligatorio)

En hPanel → **Dominios** → `miropa.com.co` → **DNS**:

| Tipo | Nombre | Valor |
|------|--------|-------|
| **A** | `@` | `72.61.1.78` |
| **CNAME** | `www` | `miropa.com.co` |

Comprobar: `nslookup miropa.com.co` → `72.61.1.78`.

### `.env` en VPS

```env
APP_NAME="Mi Ropa"
APP_URL=https://miropa.com.co
APP_DOMAIN=miropa.com.co
MAIL_FROM_ADDRESS=noreply@miropa.com.co
DB_PASSWORD="Colombia2026#"
```

### Nginx + SSL (root en VPS)

```bash
cp /var/www/turopa/deploy/nginx/miropa.com.co.conf /etc/nginx/sites-available/miropa.com.co
ln -sf /etc/nginx/sites-available/miropa.com.co /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/turopa.com.co /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx
certbot --nginx -d miropa.com.co -d www.miropa.com.co
```

Como `deploy`:

```bash
cd /var/www/turopa
php artisan config:clear && php artisan config:cache
```

Si hay **HTTP 500**, revisa: `tail -50 storage/logs/laravel.log`

---

## Guía original (VPS, bootstrap, MySQL)

Guía para **vaciar la VPS**, instalar stack y dejar el marketplace listo en producción.

---

## Fase 0 — Limpiar cuenta Hostinger (panel web)

### 1. Cancelar Business Web Hosting pendiente
1. hPanel → **Suscripciones** (Billing → Subscriptions).
2. **Business Web Hosting** → icono papelera → eliminar / cancelar pago pendiente.

### 2. Dominio gratuito `smartechmedellin.cloud`
1. Suscripciones → **smartechmedellin.cloud**.
2. Desactiva **renovación automática** (ya está OFF en tu cuenta).
3. No renueves cuando venza (nov 2026). Opcional: eliminar sitio/email asociado si ya no lo usas.
4. **Starter Business Email** de ese dominio: desactiva renovación si tampoco lo necesitas.

### 3. Comprar `turopa.com.co`
1. hPanel → **Dominios** → **Comprar dominio** → busca `turopa.com.co`.
2. **Añadir a la cesta** (~50.900 CO$ el primer año) y completa la compra.
3. Registrar en Hostinger simplifica DNS + VPS en un mismo panel.

### 4. Reinstalar la VPS (borra TODO el servidor)
1. hPanel → **VPS** → `srv1084402.hstgr.cloud`.
2. **Configuración del SO** / **Reinstall OS**.
3. Elige **Ubuntu 24.04 LTS** (64-bit).
4. Confirma — esto **elimina todos los archivos, sitios y bases de datos** anteriores.
5. Anota la **IP pública** de la VPS y la **contraseña root** (o configura llave SSH).

> Tras reinstalar, espera 2–5 minutos antes de conectar por SSH.

---

## Fase 1 — DNS de turopa.com.co

En hPanel → **Dominios** → `turopa.com.co` → **DNS / Zona DNS**:

| Tipo | Nombre | Valor | TTL |
|------|--------|-------|-----|
| A | `@` | IP de tu VPS | 3600 |
| A | `www` | IP de tu VPS | 3600 |

Opcional (correo más adelante):

| Tipo | Nombre | Valor |
|------|--------|-------|
| MX | `@` | (Hostinger te dará el servidor MX si contratas email) |
| TXT | `@` | SPF cuando configures buzón |

Propagación DNS: 15 min – 48 h (suele ser rápido en Hostinger).

---

## Fase 2 — Conectar por SSH (desde Windows)

PowerShell:

```powershell
ssh root@TU_IP_VPS
```

Acepta la huella del host. Cambia la contraseña root si Hostinger lo pide.

---

## Fase 3 — Bootstrap del servidor

En la VPS, como root:

```bash
# Actualizar sistema
apt update && apt upgrade -y

# Usuario deploy (no uses root para la app)
adduser deploy
usermod -aG sudo deploy
mkdir -p /home/deploy/.ssh
cp /root/.ssh/authorized_keys /home/deploy/.ssh/ 2>/dev/null || true
chown -R deploy:deploy /home/deploy/.ssh
chmod 700 /home/deploy/.ssh
chmod 600 /home/deploy/.ssh/authorized_keys 2>/dev/null || true
```

Copia el script del repo y ejecútalo, **o** instala manualmente:

```bash
# Desde tu PC (con el repo local), sube el script:
# scp deploy/scripts/server-bootstrap.sh root@TU_IP:/root/

chmod +x /root/server-bootstrap.sh
/root/server-bootstrap.sh
```

El script instala: Nginx, PHP 8.3, MySQL 8, Composer, Node 20, Certbot, Redis, Supervisor.

---

## Fase 4 — Base de datos

```bash
mysql -u root -p
```

```sql
CREATE DATABASE turopa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'turopa'@'localhost' IDENTIFIED BY 'CONTRASEÑA_FUERTE_AQUI';
GRANT ALL PRIVILEGES ON turopa.* TO 'turopa'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Guarda usuario, contraseña y nombre de la BD para el `.env`.

---

## Fase 5 — Subir el código

### Opción A — Git (recomendado)

El repo es **privado**. Crea un token en GitHub → **Settings** → **Developer settings** → **Personal access tokens (classic)** → scope **`repo`**.

En la VPS como root o `deploy`:

```bash
sudo mkdir -p /var/www/turopa
sudo chown deploy:deploy /var/www/turopa
cd /var/www/turopa
git clone https://github.com/pipelink1205-coder/Miropa.com.git .
# Si pide credenciales: usuario = tu GitHub, contraseña = el TOKEN (no tu password)
```

### Opción B — ZIP desde Laragon

En Windows, empaqueta sin `node_modules`, `vendor`, `.env`:

```powershell
# En c:\laragon\www\marketplace
# Sube con scp o FileZilla a /var/www/turopa
```

En la VPS:

```bash
cd /var/www/turopa
composer install --no-dev --optimize-autoloader
npm ci && npm run build
```

---

## Fase 6 — `.env` de producción

```bash
cp .env.example .env
nano .env
php artisan key:generate
```

Valores mínimos:

```env
APP_NAME="Mi Ropa"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://turopa.com.co

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=turopa
DB_USERNAME=turopa
DB_PASSWORD=...

SESSION_DRIVER=database
QUEUE_CONNECTION=database
CACHE_STORE=database
SCOUT_DRIVER=collection

MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_FROM_ADDRESS=noreply@turopa.com.co
MAIL_FROM_NAME="${APP_NAME}"

MARKETPLACE_CHECKOUT_ENABLED=false
```

Migraciones y enlace storage:

```bash
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan marketplace:prepare-demo   # solo si quieres datos demo en prod (omitir en prod real)
```

Permisos:

```bash
sudo chown -R deploy:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## Fase 7 — Nginx

```bash
sudo cp /var/www/turopa/deploy/nginx/turopa.com.co.conf /etc/nginx/sites-available/turopa.com.co
sudo ln -sf /etc/nginx/sites-available/turopa.com.co /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx
```

---

## Fase 8 — SSL (HTTPS)

Cuando el DNS de `turopa.com.co` ya apunte a la VPS:

```bash
sudo certbot --nginx -d turopa.com.co -d www.turopa.com.co
```

Renovación automática ya la instala Certbot.

---

## Fase 9 — Cron y colas

Cron (Laravel scheduler):

```bash
sudo crontab -u deploy -e
```

Añade:

```
* * * * * cd /var/www/turopa && php artisan schedule:run >> /dev/null 2>&1
```

Colas (opcional al inicio; con `QUEUE_CONNECTION=database`):

Supervisor ya configura `turopa-worker` si corriste el bootstrap script.

```bash
sudo supervisorctl status
```

---

## Fase 10 — OAuth y correo (cuando compres/configures)

Redirects OAuth (Google, Facebook, etc.):

- `https://turopa.com.co/auth/google/callback`
- `https://turopa.com.co/auth/facebook/callback`
- `https://turopa.com.co/auth/microsoft/callback`
- `https://turopa.com.co/auth/apple/callback`

Correo transaccional: Resend, Mailtrap, o SMTP de Hostinger con buzón `noreply@turopa.com.co`.

---

## Checklist final

- [ ] VPS reinstalada (Ubuntu 24.04)
- [ ] Business hosting pendiente eliminado
- [ ] `turopa.com.co` comprado y DNS A → IP VPS
- [ ] Nginx sirve `public/` con HTTPS
- [ ] MySQL + migrate + storage:link
- [ ] `APP_DEBUG=false`, `APP_ENV=production`
- [ ] Cron activo
- [ ] Login admin Filament: `/admin`
- [ ] OAuth keys en `.env` (opcional)

---

## Comandos útiles

```bash
# Logs Laravel
tail -f /var/www/turopa/storage/logs/laravel.log

# Logs Nginx
sudo tail -f /var/log/nginx/error.log

# Tras un deploy nuevo
cd /var/www/turopa && git pull && composer install --no-dev && npm ci && npm run build
php artisan migrate --force && php artisan optimize
sudo supervisorctl restart turopa-worker
```

---

## Notas KVM 1

- **4 GB RAM** aprox.: suficiente para Laravel + MySQL + Nginx al lanzar.
- Deja `SCOUT_DRIVER=collection` hasta que instales Meilisearch.
- Reverb/websockets: aplazar; usa `BROADCAST_CONNECTION=log` al inicio.

---

## Qué hacer con turopa.com.co

Compraste `turopa.com.co` por error; **no hace falta usarlo** como dominio principal.

| Opción | Qué hacer | Recomendación |
|--------|-----------|---------------|
| **A. No renovar** | hPanel → dominio → renovación automática **OFF** | Más simple; expira solo |
| **B. Redirigir a miropa.com.co** | DNS de turopa → A `@` y `www` → `72.61.1.78`; en VPS: `deploy/nginx/turopa-redirect.conf` + certbot | Quien escriba turopa llega a Mi Ropa |
| **C. Reembolso** | Chat Hostinger (solo primeros días) | Preguntar si aplica |

**Recomendación:** opción **A** ahora; opción **B** si quieres capturar visitas por error de tipeo.
