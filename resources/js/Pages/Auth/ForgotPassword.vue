<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <AppLogo class="justify-center" />
                <h2 class="mt-4 text-2xl font-bold text-gray-900">Recuperar contraseña</h2>
                <p class="text-sm text-gray-500 mt-2">Te enviaremos un enlace a tu correo.</p>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div v-if="$page.props.flash?.success" class="mb-4 text-sm text-green-700 bg-green-50 border border-green-100 rounded-lg px-4 py-3">
                    {{ $page.props.flash.success }}
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                        <input
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm"
                        />
                        <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition disabled:opacity-50"
                    >
                        Enviar enlace
                    </button>
                </form>

                <p class="text-center text-sm text-gray-500 mt-6">
                    <Link href="/login" class="text-indigo-600 font-medium hover:underline">Volver al login</Link>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLogo from '@/Components/AppLogo.vue';
import { Link, useForm } from '@inertiajs/vue3';

const form = useForm({ email: '' });

function submit() {
    form.post('/forgot-password');
}
</script>
