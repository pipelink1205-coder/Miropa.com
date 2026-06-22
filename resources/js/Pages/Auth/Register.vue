<template>
    <div class="min-h-screen bg-gray-50 flex items-center justify-center py-12 px-4">
        <div class="w-full max-w-md">
            <div class="text-center mb-8">
                <AppLogo class="justify-center" />
                <h2 class="mt-4 text-2xl font-bold text-gray-900">Crea tu cuenta</h2>
            </div>

            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                <SocialLoginButtons />

                <div class="relative my-6">
                    <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200"></div></div>
                    <div class="relative flex justify-center text-xs uppercase"><span class="bg-white px-2 text-gray-400">o con email</span></div>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo</label>
                        <input
                            v-model="form.name"
                            type="text"
                            autocomplete="name"
                            class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm"
                            :class="{ 'border-red-400': form.errors.name }"
                        />
                        <p v-if="form.errors.name" class="text-red-500 text-xs mt-1">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de usuario</label>
                        <div class="flex items-center border rounded-lg overflow-hidden" :class="{ 'border-red-400': form.errors.username }">
                            <span class="bg-gray-50 px-3 py-2.5 text-gray-500 text-sm border-r">@</span>
                            <input
                                v-model="form.username"
                                type="text"
                                class="flex-1 px-4 py-2.5 focus:ring-2 focus:ring-inset focus:ring-indigo-400 focus:outline-none text-sm"
                            />
                        </div>
                        <p v-if="form.errors.username" class="text-red-500 text-xs mt-1">{{ form.errors.username }}</p>
                    </div>

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
                            autocomplete="new-password"
                            class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm"
                            :class="{ 'border-red-400': form.errors.password }"
                        />
                        <p v-if="form.errors.password" class="text-red-500 text-xs mt-1">{{ form.errors.password }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar contraseña</label>
                        <input
                            v-model="form.password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            class="w-full px-4 py-2.5 border rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none text-sm"
                        />
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition disabled:opacity-50"
                    >
                        {{ form.processing ? 'Creando cuenta...' : 'Crear cuenta' }}
                    </button>
                </form>

                <p class="text-center text-sm text-gray-500 mt-6">
                    ¿Ya tienes cuenta?
                    <Link href="/login" class="text-indigo-600 font-medium hover:underline">Inicia sesión</Link>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import AppLogo from '@/Components/AppLogo.vue';
import SocialLoginButtons from '@/Components/SocialLoginButtons.vue';
import { Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/register');
}
</script>
