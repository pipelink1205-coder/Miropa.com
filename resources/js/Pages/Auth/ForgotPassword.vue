<template>
    <AuthLayout headline="Recuperar contraseña" subtitle="Te enviaremos un enlace a tu correo.">
        <div v-if="$page.props.flash?.success" class="mb-4 rounded-lg border border-trust/20 bg-trust-soft px-4 py-3 text-sm text-trust">
            {{ $page.props.flash.success }}
        </div>

        <form class="space-y-5" @submit.prevent="submit">
            <div>
                <label class="mb-1 block text-sm font-medium text-ink-secondary">Correo electrónico</label>
                <input v-model="form.email" type="email" autocomplete="email" class="input-field" />
                <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
            </div>

            <button type="submit" :disabled="form.processing" class="btn-primary w-full rounded-lg py-3 disabled:opacity-50">
                Enviar enlace
            </button>
        </form>

        <template #footer>
            <Link href="/login" class="link-accent">Volver al login</Link>
        </template>
    </AuthLayout>
</template>

<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const form = useForm({ email: '' });

function submit() {
    form.post('/forgot-password');
}
</script>
