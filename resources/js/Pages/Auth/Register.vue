<template>
    <AuthLayout
        headline="Compra y vende moda de segunda vida con confianza."
        subtitle="Regístrate con un clic. Publica, chatea y descubre prendas únicas."
    >
        <template v-if="!showEmail">
            <SocialLoginButtons />

            <p class="mt-4 text-center text-xs leading-relaxed text-ink-muted">
                Al continuar, aceptas los
                <Link href="/terminos" class="text-accent hover:underline" target="_blank">Términos</Link>
                y la
                <Link href="/privacidad" class="text-accent hover:underline" target="_blank">Política de privacidad</Link>.
            </p>

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
                    <label class="mb-1 block text-sm font-medium text-ink-secondary">Nombre completo</label>
                    <input v-model="form.name" type="text" autocomplete="name" class="input-field" :class="{ 'border-red-400': form.errors.name }" />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-ink-secondary">Nombre de usuario</label>
                    <div class="flex items-center overflow-hidden rounded-lg border" :class="{ 'border-red-400': form.errors.username }">
                        <span class="border-r border-zinc-200 bg-surface-muted px-3 py-2.5 text-sm text-ink-muted">@</span>
                        <input v-model="form.username" type="text" class="input-field flex-1 border-0 focus:ring-0" />
                    </div>
                    <p v-if="form.errors.username" class="mt-1 text-xs text-red-600">{{ form.errors.username }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-ink-secondary">Correo electrónico</label>
                    <input v-model="form.email" type="email" autocomplete="email" class="input-field" :class="{ 'border-red-400': form.errors.email }" />
                    <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-ink-secondary">Contraseña</label>
                    <input v-model="form.password" type="password" autocomplete="new-password" class="input-field" :class="{ 'border-red-400': form.errors.password }" />
                    <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
                </div>

                <div>
                    <label class="mb-1 block text-sm font-medium text-ink-secondary">Confirmar contraseña</label>
                    <input v-model="form.password_confirmation" type="password" autocomplete="new-password" class="input-field" />
                </div>

                <label class="flex items-start gap-2 text-sm text-ink-secondary">
                    <input v-model="form.accepts_terms" type="checkbox" class="mt-0.5 rounded text-accent" />
                    <span>
                        Acepto los
                        <Link href="/terminos" class="text-accent hover:underline" target="_blank">Términos</Link>
                        y la
                        <Link href="/privacidad" class="text-accent hover:underline" target="_blank">Política de privacidad</Link>
                    </span>
                </label>
                <p v-if="form.errors.accepts_terms" class="text-xs text-red-600">{{ form.errors.accepts_terms }}</p>

                <button type="submit" :disabled="form.processing" class="btn-primary w-full rounded-full py-3.5 disabled:opacity-50">
                    {{ form.processing ? 'Creando cuenta...' : 'Crear cuenta' }}
                </button>
            </form>
        </template>

        <template #footer>
            ¿Ya tienes cuenta?
            <Link href="/login" class="link-accent">Inicia sesión</Link>
        </template>
    </AuthLayout>
</template>

<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import SocialLoginButtons from '@/Components/SocialLoginButtons.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const showEmail = ref(false);

const form = useForm({
    name: '',
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
    accepts_terms: false,
});

function submit() {
    form.post('/register');
}
</script>
