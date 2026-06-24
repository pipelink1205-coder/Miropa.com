<template>
    <AuthLayout headline="Nueva contraseña">
        <form class="space-y-5" @submit.prevent="submit">
            <input v-model="form.token" type="hidden" />

            <div>
                <label class="mb-1 block text-sm font-medium text-ink-secondary">Correo electrónico</label>
                <input v-model="form.email" type="email" class="input-field" />
                <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-ink-secondary">Nueva contraseña</label>
                <input v-model="form.password" type="password" class="input-field" />
                <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-ink-secondary">Confirmar contraseña</label>
                <input v-model="form.password_confirmation" type="password" class="input-field" />
            </div>

            <button type="submit" :disabled="form.processing" class="btn-primary w-full rounded-lg py-3 disabled:opacity-50">
                Guardar contraseña
            </button>
        </form>
    </AuthLayout>
</template>

<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    token: String,
    email: String,
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/reset-password');
}
</script>
