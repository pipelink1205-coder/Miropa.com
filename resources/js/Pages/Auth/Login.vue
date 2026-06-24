<template>
    <AuthLayout
        headline="Bienvenido de nuevo"
        subtitle="Inicia sesión con un clic o usa tu correo."
    >
        <div v-if="$page.props.flash?.success" class="mb-4 rounded-lg border border-trust/20 bg-trust-soft px-4 py-3 text-sm text-trust">
            {{ $page.props.flash.success }}
        </div>

        <template v-if="!showEmail">
            <SocialLoginButtons />

            <button
                type="button"
                class="mt-5 w-full text-center text-sm font-semibold text-accent hover:underline"
                @click="showEmail = true"
            >
                Usar correo en su lugar
            </button>
        </template>

        <template v-else>
            <button
                type="button"
                class="mb-5 text-sm text-ink-muted transition hover:text-ink"
                @click="showEmail = false"
            >
                ← Volver a redes sociales
            </button>

            <form class="space-y-5" @submit.prevent="submit">
                <div>
                    <label class="mb-1 block text-sm font-medium text-ink-secondary">Correo electrónico</label>
                    <input
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        class="input-field"
                        :class="{ 'border-red-400': form.errors.email }"
                    />
                    <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-ink-secondary">Contraseña</label>
                    <input
                        v-model="form.password"
                        type="password"
                        autocomplete="current-password"
                        class="input-field"
                        :class="{ 'border-red-400': form.errors.password }"
                    />
                    <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
                    <div class="mt-1 text-right">
                        <Link href="/forgot-password" class="text-xs text-accent hover:underline">¿Olvidaste tu contraseña?</Link>
                    </div>
                </div>

                <button type="submit" :disabled="form.processing" class="btn-primary w-full rounded-full py-3.5 disabled:opacity-50">
                    {{ form.processing ? 'Ingresando...' : 'Iniciar sesión' }}
                </button>
            </form>
        </template>

        <template #footer>
            ¿No tienes cuenta?
            <Link href="/register" class="link-accent">Regístrate</Link>
        </template>
    </AuthLayout>
</template>

<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import SocialLoginButtons from '@/Components/SocialLoginButtons.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const showEmail = ref(false);
const form = useForm({ email: '', password: '' });

function submit() {
    form.post('/login');
}
</script>
