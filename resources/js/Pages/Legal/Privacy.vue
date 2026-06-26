<template>
    <LegalPageShell title="Política de privacidad" current="privacy" updated="junio 2026">
        <section>
            <h2 class="text-base font-semibold text-ink">1. Responsable del tratamiento</h2>
            <p class="mt-2">
                {{ brandName }} ({{ domain }}) trata tus datos para operar el marketplace. Contacto de privacidad:
                <a :href="`mailto:privacidad@${domain}`" class="text-accent hover:underline">privacidad@{{ domain }}</a>.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-ink">2. Datos que recopilamos</h2>
            <ul class="mt-2 list-inside list-disc space-y-1">
                <li>Identificación: nombre, usuario, email, teléfono, foto de perfil.</li>
                <li>Actividad: anuncios, mensajes, favoritos, búsquedas guardadas.</li>
                <li>Verificación: documentos de identidad cuando los envíes voluntariamente.</li>
                <li>Técnicos: IP, dispositivo, cookies de sesión y analítica básica.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-base font-semibold text-ink">3. Finalidades</h2>
            <p class="mt-2">
                Crear y gestionar tu cuenta, publicar y mostrar anuncios, habilitar chat entre usuarios, verificar identidad,
                prevenir fraude, cumplir obligaciones legales y mejorar el servicio. Con tu consentimiento, enviamos comunicaciones sobre tu cuenta.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-ink">4. Base legal</h2>
            <p class="mt-2">
                Ejecución del contrato (uso del marketplace), consentimiento (registro, verificaciones opcionales), interés legítimo (seguridad y mejora)
                y obligación legal cuando aplique la normativa colombiana de protección de datos.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-ink">5. Compartir datos</h2>
            <p class="mt-2">
                No vendemos tus datos. Compartimos información solo con proveedores necesarios (hosting, email, SMS, pagos) bajo contrato de confidencialidad,
                con otros usuarios cuando publicas un anuncio o chateas, o cuando una autoridad lo exija.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-ink">6. Conservación</h2>
            <p class="mt-2">
                Conservamos los datos mientras mantengas tu cuenta activa y el tiempo necesario para obligaciones legales, disputas o copias de seguridad.
                Puedes solicitar eliminación sujeta a retenciones legales.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-ink">7. Tus derechos</h2>
            <p class="mt-2">
                Puedes acceder, corregir, actualizar o solicitar eliminación escribiendo a
                <a :href="`mailto:privacidad@${domain}`" class="text-accent hover:underline">privacidad@{{ domain }}</a>.
                También puedes revocar consentimientos no esenciales cuando aplique.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-ink">8. Seguridad</h2>
            <p class="mt-2">
                Aplicamos medidas técnicas y organizativas razonables (cifrado en tránsito, control de acceso, verificación en dos pasos por SMS cuando está activa).
                Ningún sistema es 100 % infalible; te recomendamos usar contraseñas fuertes.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-ink">9. Más información</h2>
            <p class="mt-2">
                Consulta también nuestros <Link href="/terminos" class="text-accent hover:underline">Términos de uso</Link>
                y <Link href="/como-funciona" class="text-accent hover:underline">Cómo funciona</Link>.
            </p>
        </section>
    </LegalPageShell>
</template>

<script setup>
import LegalPageShell from '@/Components/Legal/LegalPageShell.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const brandName = computed(() => page.props.brand?.name ?? 'Mi Ropa');
const domain = computed(() => page.props.brand?.domain ?? 'miropa.com.co');
</script>
