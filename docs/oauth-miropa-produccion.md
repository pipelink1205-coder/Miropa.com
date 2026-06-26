# OAuth en producción — miropa.com.co

Guía para activar **Google**, **Facebook** y **Microsoft (Hotmail/Outlook)** en https://miropa.com.co

---

## Visto bueno técnico (código + sitio)

| Check | Estado |
|-------|--------|
| HTTPS | ✓ Certificado activo |
| `/login` responde 200 | ✓ |
| Redirect Google → `miropa.com.co/auth/google/callback` | ✓ |
| Redirect Facebook → `miropa.com.co/auth/facebook/callback` | ✓ |
| Redirect Microsoft → `miropa.com.co/auth/microsoft/callback` | ✓ |
| Rutas GET+POST callback (Apple/Microsoft) | ✓ |
| UI botones en Login/Register | ✓ |

Lo que falta es **registrar cada app** en Google/Facebook/Microsoft y poner las keys en el `.env` de la VPS.

---

## URLs de callback (copiar exacto)

```
https://miropa.com.co/auth/google/callback
https://miropa.com.co/auth/facebook/callback
https://miropa.com.co/auth/microsoft/callback
```

También válido con `www` si lo usas:

```
https://www.miropa.com.co/auth/google/callback
```

(Recomendación: registrar **ambos** dominios en cada proveedor si sirves con y sin www.)

---

## `.env` en la VPS (`/var/www/turopa/.env`)

```env
APP_URL=https://miropa.com.co

# Google
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=

# Facebook
FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=

# Microsoft (Hotmail, Outlook, Live)
AZURE_CLIENT_ID=
AZURE_CLIENT_SECRET=
AZURE_TENANT_ID=common
```

Tras editar:

```bash
php artisan config:clear
php artisan config:cache
```

---

## 1. Google (más fácil — empezar aquí)

1. https://console.cloud.google.com/
2. Crear proyecto **Mi Ropa** (o usar uno existente)
3. **APIs y servicios** → **Pantalla de consentimiento OAuth**
   - Tipo: **Externo**
   - Nombre: Mi Ropa
   - Dominio autorizado: `miropa.com.co`
   - Email de soporte: tu correo
4. **Credenciales** → **Crear credenciales** → **ID de cliente OAuth**
   - Tipo: **Aplicación web**
   - Orígenes autorizados:
     - `https://miropa.com.co`
     - `https://www.miropa.com.co`
   - URIs de redirección:
     - `https://miropa.com.co/auth/google/callback`
     - `https://www.miropa.com.co/auth/google/callback`
5. Copia **Client ID** y **Client secret** → `.env`
6. Prueba: https://miropa.com.co/login → Continuar con Google

**Publicación:** en modo "Prueba" solo cuentas Google que añadas como testers. Para público: **Publicar app** en pantalla de consentimiento.

---

## 2. Microsoft — Hotmail / Outlook / Live

1. https://portal.azure.com/ → **Microsoft Entra ID** → **App registrations** → **New registration**
2. Nombre: **Mi Ropa**
3. Supported account types: **Accounts in any organizational directory and personal Microsoft accounts**
4. Redirect URI: **Web** → `https://miropa.com.co/auth/microsoft/callback`
5. Tras crear → **Overview** → copia **Application (client) ID**
6. **Certificates & secrets** → **New client secret** → copia el valor
7. **API permissions** → Add → **Microsoft Graph** → **Delegated** → `User.Read`, `email`, `openid`, `profile` → Grant admin consent (si aplica)
8. `.env`:

```env
AZURE_CLIENT_ID=el-application-id
AZURE_CLIENT_SECRET=el-secret
AZURE_TENANT_ID=common
```

9. Prueba: https://miropa.com.co/login → Continuar con Microsoft

---

## 3. Facebook

1. https://developers.facebook.com/ → **My Apps** → **Create App**
2. Tipo: **Consumer** o **Authenticate and request data**
3. Añadir producto **Facebook Login** → **Settings**
4. **Valid OAuth Redirect URIs**:
   - `https://miropa.com.co/auth/facebook/callback`
   - `https://www.miropa.com.co/auth/facebook/callback`
5. **Settings** → **Basic** → copia **App ID** y **App Secret**
6. Modo **Live** (no Development) cuando esté listo para usuarios reales
7. `.env`:

```env
FACEBOOK_CLIENT_ID=app-id
FACEBOOK_CLIENT_SECRET=app-secret
```

**Nota:** Facebook puede pedir **Privacy Policy URL**: `https://miropa.com.co/privacidad`

---

## 4. Apple (opcional — requiere Apple Developer ~USD 99/año)

Dejar para después. Botón visible pero sin keys no funcionará.

---

## Orden recomendado de activación

1. **Google** (15 min, más simple)
2. **Microsoft** (Hotmail)
3. **Facebook** (puede pedir revisión de app)
4. Apple (cuando tengas cuenta developer)

---

## Flujo después del login social

1. Usuario autoriza en Google/Facebook/Microsoft
2. Callback crea o vincula cuenta (`social_accounts`)
3. Email verificado automáticamente
4. Redirige a **verificación de teléfono** si no tiene `phone_verified_at`
5. Luego → dashboard

---

## Errores frecuentes

| Error | Solución |
|-------|----------|
| `redirect_uri_mismatch` | URI en consola ≠ exactamente la de `.env` / `APP_URL` |
| Google "app en prueba" | Añadir testers o publicar app |
| Facebook "URL blocked" | App en modo Live + redirect URI correcta |
| Microsoft AADSTS50011 | Redirect URI mal en Azure |
| "No pudimos obtener tu correo" | Permiso `email` no concedido (Facebook) |
| Cambios no aplican | `php artisan config:cache` en VPS |

---

## Probar en producción

```bash
# En VPS — ver que APP_URL y keys están cargadas (no muestra secrets completos)
php artisan tinker --execute="echo config('app.url').PHP_EOL.config('services.google.client_id') ? 'google:ok' : 'google:missing';"
```

Desde navegador incógnito: https://miropa.com.co/login → cada botón.
