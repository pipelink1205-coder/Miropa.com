<template>
    <AppLayout title="Términos de uso">
        <div class="container-app max-w-3xl py-12 prose prose-sm prose-zinc max-w-none">
            <h1 class="text-3xl font-bold text-ink not-prose">Términos de uso</h1>
            <p class="mt-2 text-sm text-ink-muted not-prose">Última actualización: {{ updated }}</p>

            <div class="mt-8 space-y-6 text-sm text-ink-secondary">
                <section>
                    <h2 class="text-base font-semibold text-ink">1. El servicio</h2>
                    <p class="mt-2">Mi Ropa es un marketplace que conecta compradores y vendedores de artículos de segunda vida. Facilitamos publicación, descubrimiento y comunicación entre usuarios.</p>
                </section>
                <section>
                    <h2 class="text-base font-semibold text-ink">2. Publicaciones</h2>
                    <p class="mt-2">El vendedor es responsable de la veracidad de fotos, descripción, talla, estado y precio. Está prohibido publicar réplicas, artículos ilegales o contenido engañoso.</p>
                </section>
                <section>
                    <h2 class="text-base font-semibold text-ink">3. Transacciones</h2>
                    <p class="mt-2">En modo <strong>trato directo</strong>, la negociación y el pago ocurren fuera de la plataforma bajo responsabilidad de las partes. La <strong>compra protegida</strong> (cuando esté disponible) regirá por condiciones específicas publicadas en ese momento.</p>
                </section>
                <section>
                    <h2 class="text-base font-semibold text-ink">4. Cuenta y verificación</h2>
                    <p class="mt-2">Podemos solicitar verificación de teléfono e identidad para proteger a la comunidad. Nos reservamos el derecho de suspender cuentas que incumplan estas normas.</p>
                </section>
                <section>
                    <h2 class="text-base font-semibold text-ink">5. Contacto</h2>
                    <p class="mt-2">Para dudas legales o reportes: soporte@miropa.com</p>
                </section>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

const updated = 'junio 2026';
</script>
