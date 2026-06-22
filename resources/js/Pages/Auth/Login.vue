<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <AppLogo class="justify-center" />
                <h2 class="mt-4 text-2xl font-bold text-gray-900">Inicia sesión</h2>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <div v-if="$page.props.flash?.success" class="mb-4 text-sm text-green-700 bg-green-50 border border-green-100 rounded-lg px-4 py-3">
                    {{ $page.props.flash.success }}
                </div>

                <SocialLoginButtons />

                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
                    <div class="relative flex justify-center text-xs uppercase"><span class="bg-white px-2 text-gray-400">o con email</span></div>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                        <input
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm"
                            :class="{ 'border-red-400': form.errors.email }"
                        />
                        <p v-if="form.errors.email" class="text-red-500 text-xs mt-1">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                        <input
                            v-model="form.password"
                            type="password"
                            autocomplete="current-password"
                            class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm"
                            :class="{ 'border-red-400': form.errors.password }"
                        />
                        <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                        <div class="text-right mt-1">
                            <Link href="/forgot-password" class="text-xs text-indigo-600 hover:underline">¿Olvidaste tu contraseña?</Link>
                        </div>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition disabled:opacity-50"
                    >
                        {{ form.processing ? 'Ingresando...' : 'Iniciar sesión' }}
                    </button>
                </form>

                <p class="text-center text-sm text-gray-500 mt-6">
                    ¿No tienes cuenta?
                    <Link href="/register" class="text-indigo-600 font-medium hover:underline">Regístrate</Link>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLogo from '@/Components/AppLogo.vue';
import SocialLoginButtons from '@/Components/SocialLoginButtons.vue';
import { Link, useForm } from '@inertiajs/vue3';

const form = useForm({ email: '', password: '' });

function submit() {
    form.post('/login');
}
</script>
