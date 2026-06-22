<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
        <div class="w-full max-w-md text-center">
            <AppLogo class="justify-center" />
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mt-8">
                <p class="text-5xl mb-4">📧</p>
                <h2 class="text-xl font-bold text-gray-900 mb-2">Verifica tu correo</h2>
                <p class="text-gray-500 text-sm mb-4">
                    Te enviamos un enlace de verificación. En desarrollo está en
                    <code class="text-xs bg-gray-100 px-1 rounded">storage/logs/laravel.log</code>
                </p>

                <div v-if="$page.props.flash?.error" class="mb-4 text-sm text-red-700 bg-red-50 border border-red-100 rounded-lg px-4 py-3 text-left">
                    {{ $page.props.flash.error }}
                </div>
                <div v-if="$page.props.flash?.success" class="mb-4 text-sm text-green-700 bg-green-50 border border-green-100 rounded-lg px-4 py-3 text-left">
                    {{ $page.props.flash.success }}
                </div>

                <div v-if="devMailHint" class="mb-4 text-xs text-amber-800 bg-amber-50 border border-amber-100 rounded-lg px-4 py-3 text-left">
                    <strong>Modo desarrollo:</strong> pulsa "Reenviar correo", abre el log, copia la URL que termina en
                    <code>/email/verify/...</code> y ábrela <strong>en este mismo navegador</strong> (debes seguir logueada).
                </div>

                <form @submit.prevent="resend">
                    <button
                        type="submit"
                        :disabled="form.processing || sent"
                        class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition disabled:opacity-50"
                    >
                        {{ sent ? '¡Enviado!' : 'Reenviar correo' }}
                    </button>
                </form>
                <Link href="/logout" method="post" as="button" class="text-sm text-gray-400 mt-4 block hover:text-red-500">
                    Cerrar sesión
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLogo from '@/Components/AppLogo.vue';
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
