<template>
    <div
        class="rounded-2xl border border-zinc-200/80 bg-surface-muted/60"
        :class="compact ? 'px-4 py-3' : 'px-5 py-5 md:px-6 md:py-6'"
    >
        <template v-if="compact">
            <p class="text-xs leading-relaxed text-ink-secondary">
                <strong class="font-semibold text-ink">Verificación opcional.</strong>
                Confirmamos que eres una persona real para mostrar el badge
                <strong class="text-ink">✓ Identidad</strong> en tu perfil.
                Tu documento no se publica ni se comparte con otros usuarios; solo lo revisa nuestro equipo de forma confidencial.
            </p>
        </template>

        <template v-else>
            <h2 class="text-base font-bold text-ink">¿Por qué pedimos tu documento?</h2>
            <p class="mt-2 text-sm leading-relaxed text-ink-secondary">
                En {{ brandName }} queremos que comprar y vender sea seguro y transparente.
                Por eso, de forma <strong class="font-medium text-ink">voluntaria</strong>, puedes enviar fotos del frente y reverso de tu documento.
            </p>

            <h3 class="mt-5 text-sm font-semibold text-ink">¿Con qué intención?</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-ink-secondary">
                Buscamos reducir perfiles falsos, estafas y suplantaciones, y dar a la comunidad una señal clara de que detrás de tu cuenta hay una persona verificable.
                Si un administrador aprueba tu solicitud, verás el badge <strong class="font-medium text-ink">✓ Identidad</strong> en tu perfil.
            </p>

            <h3 class="mt-5 text-sm font-semibold text-ink">¿En qué ayuda en la plataforma?</h3>
            <ul class="mt-2 space-y-1.5 text-sm text-ink-secondary">
                <li class="flex gap-2">
                    <span class="text-accent" aria-hidden="true">•</span>
                    <span><strong class="font-medium text-ink">A ti:</strong> refuerza tu credibilidad como comprador o vendedor.</span>
                </li>
                <li class="flex gap-2">
                    <span class="text-accent" aria-hidden="true">•</span>
                    <span><strong class="font-medium text-ink">A quien te contacta:</strong> más confianza antes de escribirte o concretar una venta.</span>
                </li>
                <li class="flex gap-2">
                    <span class="text-accent" aria-hidden="true">•</span>
                    <span><strong class="font-medium text-ink">A la comunidad:</strong> un marketplace más serio, junto con la verificación de correo y celular.</span>
                </li>
            </ul>

            <h3 class="mt-5 text-sm font-semibold text-ink">Privacidad y uso de tus datos</h3>
            <ul class="mt-2 space-y-1.5 text-sm text-ink-secondary">
                <li class="flex gap-2">
                    <span class="text-trust" aria-hidden="true">•</span>
                    <span>Solo usamos tu documento para <strong class="font-medium text-ink">revisar tu identidad</strong>; no lo mostramos en tu perfil público.</span>
                </li>
                <li class="flex gap-2">
                    <span class="text-trust" aria-hidden="true">•</span>
                    <span>Lo revisa manualmente nuestro equipo; el proceso puede tardar hasta <strong class="font-medium text-ink">48 horas hábiles</strong>.</span>
                </li>
                <li class="flex gap-2">
                    <span class="text-trust" aria-hidden="true">•</span>
                    <span>Si prefieres no verificarlo, puedes seguir usando la app con email y celular confirmados; el badge de identidad es opcional.</span>
                </li>
            </ul>
        </template>
    </div>
</template>

<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    compact: { type: Boolean, default: false },
});

const brandName = computed(() => usePage().props.brand?.name ?? 'Mi Ropa');
</script>
