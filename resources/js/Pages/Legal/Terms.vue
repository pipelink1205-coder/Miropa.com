<template>
    <LegalPageShell title="Términos de uso" current="terms" updated="junio 2026">
        <section>
            <h2 class="text-base font-semibold text-ink">1. El servicio</h2>
            <p class="mt-2">
                {{ brandName }} es un marketplace colombiano que conecta compradores y vendedores de artículos de segunda vida,
                con foco en moda. Facilitamos publicación, descubrimiento, chat y — cuando esté disponible — pagos con compra protegida.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-ink">2. Tipos de anuncio</h2>
            <ul class="mt-2 list-inside list-disc space-y-1">
                <li><strong>Moda (marketplace):</strong> artículos de vestir con flujo pensado para compra en plataforma o contacto según el modo elegido.</li>
                <li><strong>Otras categorías (trato directo):</strong> vehículos, inmuebles, etc. Solo sirven para que te contacten; no hay checkout integrado.</li>
            </ul>
        </section>

        <section>
            <h2 class="text-base font-semibold text-ink">3. Publicaciones</h2>
            <p class="mt-2">
                El vendedor es responsable de fotos, descripción, talla, color, estado y precio. Está prohibido publicar réplicas,
                artículos robados, ilegales, engañosos o que infrinjan derechos de terceros. Podemos retirar anuncios y suspender cuentas.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-ink">4. Transacciones y pagos</h2>
            <p class="mt-2">
                En <strong>trato directo</strong>, la negociación, el pago y la entrega ocurren fuera de {{ brandName }}, bajo responsabilidad de las partes.
                La <strong>compra protegida</strong> (cuando esté activa) se regirá por condiciones específicas publicadas en ese momento, incluyendo comisiones aplicables.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-ink">5. Cuenta y verificación</h2>
            <p class="mt-2">
                Podemos exigir verificación de correo, teléfono e identidad para proteger a la comunidad. Los datos de verificación se tratan según nuestra
                <Link href="/privacidad" class="text-accent hover:underline">Política de privacidad</Link>.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-ink">6. Conducta y moderación</h2>
            <p class="mt-2">
                No toleramos acoso, spam, fraude ni uso fraudulento del chat. Los usuarios pueden reportar anuncios; revisamos reportes y aplicamos medidas
                que incluyen advertencias, suspensión o cierre de cuenta.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-ink">7. Limitación de responsabilidad</h2>
            <p class="mt-2">
                {{ brandName }} actúa como intermediario tecnológico. No garantizamos la calidad de los artículos ni la conducta de los usuarios,
                salvo lo expresamente previsto en compra protegida cuando esté operativa.
            </p>
        </section>

        <section>
            <h2 class="text-base font-semibold text-ink">8. Cambios y contacto</h2>
            <p class="mt-2">
                Podemos actualizar estos términos; la fecha de vigencia aparece arriba. Para dudas legales: <a href="mailto:soporte@miropa.com" class="text-accent hover:underline">soporte@miropa.com</a>.
            </p>
        </section>
    </LegalPageShell>
</template>

<script setup>
import LegalPageShell from '@/Components/Legal/LegalPageShell.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const brandName = computed(() => usePage().props.brand?.name ?? 'Mi Ropa');
</script>
