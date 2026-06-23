# CLAUDE.md

> Archivo de contexto del proyecto para Claude Code. Se lee al inicio de cada sesión. Mantenlo actualizado cuando cambien decisiones importantes.

## Qué es este proyecto

Marketplace de compraventa de **artículos de segunda mano**. El diferenciador del negocio es la **confianza**: reputación pública, verificación de usuarios y reseñas bidireccionales. El backend es una **API headless** que hoy sirve a la web y mañana a las apps de Android/iOS sin reescribirse.

La especificación completa y el roadmap por fases están en `PROMPT_MAESTRO_marketplace.md`. **Consúltalo antes de empezar una fase nueva.**

## Stack

- **Backend:** Laravel 11 · PHP 8.3
- **Auth:** Laravel Sanctum (tokens — sirve para web SPA y para móvil)
- **DB:** MySQL 8 (servida por Laragon en local)
- **Cache/colas/tiempo real:** Redis · Laravel Reverb (WebSockets)
- **Búsqueda:** Laravel Scout + Meilisearch
- **Frontend:** Vue 3 + Inertia.js + Vite + Tailwind CSS
- **Almacenamiento:** disco local en dev, S3 en producción
- **Entorno local:** Laragon → proyecto servido en `http://marketplace.test`

## Reglas de trabajo (importantes)

1. **No dejes nada a medias.** Si mencionas un archivo, créalo completo. Nada de `// TODO` ni pseudo-código en entregables finales.
2. **Backend antes que frontend.** El frontend solo consume API ya construida y probada.
3. **Una fase a la vez.** No avances de fase del roadmap hasta que yo lo confirme. Cada fase debe compilar y pasar sus pruebas.
4. **Pregunta antes de decisiones grandes.** Si hay alternativas válidas, elige la más estándar de la comunidad Laravel y dime por qué en una línea.
5. **Antes de instalar paquetes nuevos**, avísame qué vas a instalar y para qué.

## Convenciones de código

- **Idioma:** código en inglés (tablas, modelos, variables, métodos). Comentarios y textos de cara al usuario en **español**.
- **Estilo:** PSR-12. Corre `./vendor/bin/pint` antes de dar por terminado un cambio.
- **Controladores delgados:** la lógica vive en `app/Services` o `app/Actions`. Los controladores solo orquestan.
- **Validación:** siempre en `Form Requests`, nunca dentro del controlador.
- **Autorización:** siempre con `Policies`. Cada acción sobre `Listing`, `Transaction` y `Message` se autoriza.
- **Salida JSON:** siempre a través de `API Resources`. Estructura `{ data, meta }`. Nunca devuelvas modelos Eloquent crudos.
- **Migraciones:** reversibles (`down()` correcto). Toda tabla con migración + modelo + factory + seeder.
- **Nombres:** tablas en plural snake_case, modelos en singular PascalCase, rutas API en kebab-case bajo `/api/v1`.

## Estructura de carpetas esperada

```
app/
  Actions/         · operaciones de negocio puntuales
  Services/        · lógica de dominio reutilizable
  Http/
    Controllers/Api/V1/
    Requests/      · Form Requests (validación)
    Resources/     · API Resources (salida JSON)
    Middleware/
  Models/
  Policies/
  Notifications/
  Events/  Listeners/  Jobs/
database/
  migrations/  factories/  seeders/
resources/
  js/
    Pages/         · páginas Inertia (Vue)
    Components/     · componentes reutilizables
    Layouts/
routes/
  api.php  ·  web.php
tests/
  Feature/  Unit/
```

## Modelos principales (resumen)

`User · Profile · IdentityVerification · Review · Category · Condition · Listing · ListingImage · ListingAttribute · Transaction · Payment · ShippingAddress · Conversation · Message · Favorite · SavedSearch · Follow · Location · Report`

Detalle de campos y relaciones en `PROMPT_MAESTRO_marketplace.md`, Sección 4.

## Comandos frecuentes

```bash
# Servir (Laragon ya expone http://marketplace.test, pero para dev con HMR):
php artisan serve
npm run dev

# Base de datos limpia con datos de demo:
php artisan migrate:fresh --seed

# Pruebas:
php artisan test

# Estilo de código:
./vendor/bin/pint

# Búsqueda (sincronizar índices):
php artisan scout:import "App\Models\Listing"

# Colas y tiempo real:
php artisan queue:work
php artisan reverb:start
```

## Estado del proyecto

> Actualiza esta sección al terminar cada fase.

- [x] Fase 1 — Cimientos
- [x] Fase 2 — Modelo de datos
- [ ] Fase 3 — Auth y perfiles
- [ ] Fase 4 — Anuncios (CRUD + imágenes)
- [ ] Fase 5 — Búsqueda y categorías
- [ ] Fase 6 — Mensajería
- [ ] Fase 7 — Transacciones y reputación
- [ ] Fase 8 — Confianza y moderación
- [ ] Fase 9 — Pulido y despliegue
- [ ] Fase 10 — Apps móviles (futuro)

## Notas y decisiones tomadas

> Registra aquí decisiones importantes para no repetir discusiones (ej. "se eligió MercadoPago como pasarela por el mercado colombiano").

- Se instaló Laravel 12 (PHP 8.2 disponible en el entorno no soporta Laravel 13 que requiere PHP 8.3). Laravel 12 es compatible con todo el stack definido.
- Entorno local: Laragon con MySQL 8, `marketplace.test`. Base de datos creada como `marketplace` (utf8mb4).
- Frontend: Vue 3 + Inertia.js v3 + Tailwind CSS v4 + Vite. Entry point en `resources/js/app.js`, blade raíz en `resources/views/app.blade.php`.
- Rutas API bajo `/api/v1` (esqueleto listo en `routes/api.php`). Sanctum configurado con dominio stateful `marketplace.test`.

### Marca y enfoque de negocio (Mi Ropa)

- **Marca:** moda de segunda mano (ropa, calzado, accesorios, deporte, ropa bebé). Dominio: miropa.com.
- **Una cuenta, una reputación** por usuario — no subcuentas por tipo de producto.
- **`sale_mode` en categorías** (`categories.sale_mode`):
  - `marketplace` — núcleo moda; compra con pasarela Mi Ropa (futuro) + chat + envío integrado (futuro).
  - `classified` — vehículos, hogar voluminoso, herramientas, arte; **solo chat**, trato y entrega fuera de la app.
- **UI:** badge “Compra protegida Mi Ropa” vs “Solo contacto”; home y publicar priorizan categorías moda (`config/marketplace.php` → slugs de `CategoryDefinitions::fashionSlugs()`).
- **Árbol moda (6 padres, `marketplace`):** Mujer, Hombre, Niños y Bebé, Calzado, Accesorios, Ropa deportiva — subcategorías granulares (tenis, gorras, vestidos, etc.). Definición única en `App\Support\CategoryDefinitions`.
- **Tiles home:** `config/category_images.php` con flag `primary` (moda arriba, resto en sección “Otros anuncios”).

### Roadmap pagos y envíos (no implementado aún)

**Fase A — Pasarela básica (moda, `marketplace` only)**

- Integrar pasarela (candidata: Mercado Pago por mercado colombiano — confirmar antes de instalar).
- Checkout: precio artículo + comisión Mi Ropa opcional.
- Retención de fondos hasta confirmación de entrega o ventana de disputa.
- Sin envío automático: comprador y vendedor acuerdan entrega por chat, o tarifa fija manual en checkout.

**Fase B — Envío integrado**

- Cotización al pagar: producto + **envío (lo paga el comprador**, salvo promos “envío gratis”).
- Integración transportadora (Servientrega, Coordinadora, 99minutos, etc.) o tarifas fijas por ciudad.
- Vendedor: etiqueta prepagada / punto de recogida; tracking visible en la app.
- La **pasarela no envía paquetes** — solo cobra; Mi Ropa orquesta con el operador logístico.

**Fase C — Opciones locales**

- “Entrega en persona” (misma ciudad): sin costo de envío en app; pago retenido hasta confirmación.
- `classified` **nunca** entra en pasarela ni envío Mi Ropa.

**Modelo de ingresos (referencia):** comisión sobre venta marketplace + margen opcional en envío negociado con transportadora.

