<template>
    <AuthLayout
        headline="Verifica tu celular"
        :subtitle="`Por seguridad, confirma tu número antes de publicar o comprar en ${$page.props.brand?.name ?? 'Mi Ropa'}.`"
    >
        <div v-if="$page.props.flash?.success" class="rounded-lg border border-trust/20 bg-trust-soft px-4 py-3 text-sm text-trust">
            {{ $page.props.flash.success }}
        </div>

        <div v-if="$page.props.flash?.dev_code" class="rounded-lg border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            <strong>Modo desarrollo:</strong> tu código es
            <span class="font-mono font-bold">{{ $page.props.flash.dev_code }}</span>
        </div>

        <form class="space-y-4" @submit.prevent="sendCode">
            <div>
                <label class="mb-1 block text-sm font-medium text-ink-secondary">Número de celular</label>
                <input v-model="phoneForm.phone" type="tel" placeholder="300 123 4567" class="input-field" />
                <p v-if="phoneForm.errors.phone" class="mt-1 text-xs text-red-600">{{ phoneForm.errors.phone }}</p>
            </div>
            <button type="submit" :disabled="phoneForm.processing" class="btn-secondary w-full rounded-lg py-3 disabled:opacity-50">
                {{ phoneForm.processing ? 'Enviando...' : 'Enviar código SMS' }}
            </button>
        </form>

        <div class="mt-6 border-t border-zinc-100 pt-6">
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
                    {{ codeForm.processing ? 'Verificando...' : 'Verificar número' }}
                </button>
            </form>
        </div>
    </AuthLayout>
</template>

<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    phone: String,
});

const phoneForm = useForm({
    phone: props.phone ?? '',
});

const codeForm = useForm({
    code: '',
});

function sendCode() {
    phoneForm.post('/telefono/verificar/enviar', { preserveScroll: true });
}

function verifyCode() {
    codeForm.post('/telefono/verificar');
}
</script>
