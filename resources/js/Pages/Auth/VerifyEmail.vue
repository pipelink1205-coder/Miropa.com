<template>
    <AuthLayout
        headline="Verifica tu correo"
        :subtitle="`Enviamos un código de 6 dígitos a ${email}. Confírmalo para continuar.`"
    >
        <div v-if="$page.props.flash?.error" class="rounded-lg border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ $page.props.flash.error }}
        </div>
        <div v-if="$page.props.flash?.success" class="rounded-lg border border-trust/20 bg-trust-soft px-4 py-3 text-sm text-trust">
            {{ $page.props.flash.success }}
        </div>

        <div v-if="$page.props.flash?.dev_code" class="rounded-lg border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <strong>Modo desarrollo:</strong> tu código es
            <span class="font-mono font-bold">{{ $page.props.flash.dev_code }}</span>
        </div>

        <div v-else-if="devMailHint" class="rounded-lg border border-amber-100 bg-amber-50 px-4 py-3 text-xs text-amber-800">
            <strong>Modo desarrollo:</strong> el código también queda en
            <code class="rounded bg-white/70 px-1">storage/logs/laravel.log</code>
        </div>

        <form class="mb-6" @submit.prevent="sendCode">
            <button type="submit" :disabled="sendForm.processing" class="btn-secondary w-full rounded-lg py-3 disabled:opacity-50">
                {{ sendForm.processing ? 'Enviando...' : 'Enviar código al correo' }}
            </button>
        </form>

        <form class="space-y-4" @submit.prevent="verifyCode">
            <div>
                <label class="mb-1 block text-sm font-medium text-ink-secondary">Código de 6 dígitos</label>
                <input
                    v-model="codeForm.code"
                    type="text"
                    inputmode="numeric"
                    maxlength="6"
                    placeholder="000000"
                    class="input-field text-center font-mono tracking-widest"
                />
                <p v-if="codeForm.errors.code" class="mt-1 text-xs text-red-600">{{ codeForm.errors.code }}</p>
            </div>
            <button type="submit" :disabled="codeForm.processing" class="btn-primary w-full rounded-lg py-3 disabled:opacity-50">
                {{ codeForm.processing ? 'Verificando...' : 'Verificar correo' }}
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

defineProps({
    email: String,
    devMailHint: Boolean,
});

const sendForm = useForm({});
const codeForm = useForm({
    code: '',
});

function sendCode() {
    sendForm.post('/email/verification-notification', { preserveScroll: true });
}

function verifyCode() {
    codeForm.post('/email/verificar');
}
</script>
