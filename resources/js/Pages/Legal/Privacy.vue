<template>
    <AppLayout title="Política de privacidad">
        <div class="container-app max-w-3xl py-12">
            <h1 class="text-3xl font-bold text-ink">Política de privacidad</h1>
            <p class="mt-2 text-sm text-ink-muted">Última actualización: {{ updated }}</p>

            <div class="mt-8 space-y-6 text-sm text-ink-secondary">
                <section>
                    <h2 class="text-base font-semibold text-ink">Datos que recopilamos</h2>
                    <p class="mt-2">Nombre, email, teléfono, datos de perfil, publicaciones, mensajes y documentos de verificación de identidad cuando los proporciones voluntariamente.</p>
                </section>
                <section>
                    <h2 class="text-base font-semibold text-ink">Uso de la información</h2>
                    <p class="mt-2">Operar el marketplace, verificar usuarios, prevenir fraude, mejorar el servicio y comunicarnos contigo sobre tu cuenta o transacciones.</p>
                </section>
                <section>
                    <h2 class="text-base font-semibold text-ink">Compartir datos</h2>
                    <p class="mt-2">No vendemos tus datos. Compartimos información solo con proveedores necesarios (hosting, SMS, pagos) bajo contrato, o cuando la ley lo exija.</p>
                </section>
                <section>
                    <h2 class="text-base font-semibold text-ink">Tus derechos</h2>
                    <p class="mt-2">Puedes solicitar acceso, corrección o eliminación de tus datos escribiendo a privacidad@miropa.com</p>
                </section>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';

const updated = 'junio 2026';
</script>
