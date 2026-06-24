<template>
    <AuthLayout headline="Verifica tu correo">
        <p class="mb-4 text-center text-5xl">📧</p>
        <p class="mb-4 text-center text-sm text-ink-secondary">
            Te enviamos un enlace de verificación. En desarrollo está en
            <code class="rounded bg-surface-muted px-1 text-xs">storage/logs/laravel.log</code>
        </p>

        <div v-if="$page.props.flash?.error" class="mb-4 rounded-lg border border-red-100 bg-red-50 px-4 py-3 text-left text-sm text-red-700">
            {{ $page.props.flash.error }}
        </div>
        <div v-if="$page.props.flash?.success" class="mb-4 rounded-lg border border-trust/20 bg-trust-soft px-4 py-3 text-left text-sm text-trust">
            {{ $page.props.flash.success }}
        </div>

        <div v-if="devMailHint" class="mb-4 rounded-lg border border-amber-100 bg-amber-50 px-4 py-3 text-left text-xs text-amber-800">
            <strong>Modo desarrollo:</strong> pulsa "Reenviar correo", abre el log, copia la URL que termina en
            <code>/email/verify/...</code> y ábrela <strong>en este mismo navegador</strong> (debes seguir logueada).
        </div>

        <form @submit.prevent="resend">
            <button type="submit" :disabled="form.processing || sent" class="btn-primary w-full rounded-lg py-3 disabled:opacity-50">
                {{ sent ? '¡Enviado!' : 'Reenviar correo' }}
            </button>
        </form>

        <Link href="/logout" method="post" as="button" class="mt-4 block text-sm text-ink-muted hover:text-red-600">
            Cerrar sesión
        </Link>
    </AuthLayout>
</template>

<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    devMailHint: Boolean,
});

const form = useForm({});
const sent = ref(false);

function resend() {
    form.post('/email/verification-notification', {
        onSuccess: () => { sent.value = true; },
    });
}
</script>
