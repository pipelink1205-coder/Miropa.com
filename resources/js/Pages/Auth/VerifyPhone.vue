<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <AppLogo class="justify-center" />
                <h2 class="mt-4 text-2xl font-bold text-gray-900">Verifica tu celular</h2>
                <p class="text-sm text-gray-500 mt-2">
                    Por seguridad, confirma tu número antes de publicar o comprar en {{ $page.props.brand?.name ?? 'Mi Ropa' }}.
                </p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 space-y-6">
                <div v-if="$page.props.flash?.success" class="text-sm text-green-700 bg-green-50 border border-green-100 rounded-lg px-4 py-3">
                    {{ $page.props.flash.success }}
                </div>

                <div v-if="$page.props.flash?.dev_code" class="text-sm text-amber-800 bg-amber-50 border border-amber-100 rounded-lg px-4 py-3">
                    <strong>Modo desarrollo:</strong> tu código es
                    <span class="font-mono font-bold">{{ $page.props.flash.dev_code }}</span>
                </div>

                <form @submit.prevent="sendCode" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Número de celular</label>
                        <input
                            v-model="phoneForm.phone"
                            type="tel"
                            placeholder="300 123 4567"
                            class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm"
                        />
                        <p v-if="phoneForm.errors.phone" class="text-red-500 text-xs mt-1">{{ phoneForm.errors.phone }}</p>
                    </div>
                    <button
                        type="submit"
                        :disabled="phoneForm.processing"
                        class="w-full border border-indigo-200 text-indigo-700 py-3 rounded-lg font-semibold hover:bg-indigo-50 transition disabled:opacity-50"
                    >
                        {{ phoneForm.processing ? 'Enviando...' : 'Enviar código SMS' }}
                    </button>
                </form>

                <div class="border-t border-gray-100 pt-6">
                    <form @submit.prevent="verifyCode" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Código de 6 dígitos</label>
                            <input
                                v-model="codeForm.code"
                                type="text"
                                inputmode="numeric"
                                maxlength="6"
                                placeholder="000000"
                                class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm tracking-widest text-center font-mono"
                            />
                            <p v-if="codeForm.errors.code" class="text-red-500 text-xs mt-1">{{ codeForm.errors.code }}</p>
                        </div>
                        <button
                            type="submit"
                            :disabled="codeForm.processing"
                            class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition disabled:opacity-50"
                        >
                            {{ codeForm.processing ? 'Verificando...' : 'Verificar número' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLogo from '@/Components/AppLogo.vue';
import { useForm, usePage } from '@inertiajs/vue3';

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
