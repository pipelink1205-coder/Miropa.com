<template>
    <AppLayout title="Mi cuenta">
        <div class="max-w-2xl mx-auto px-4 py-10">
            <h1 class="text-2xl font-bold text-gray-900 mb-2">Mi cuenta</h1>
            <p class="text-sm text-gray-500 mb-8">
                Tu confianza se muestra con <strong>badges</strong> y tu reputación con <strong>reseñas</strong> tras cada venta.
            </p>

            <div v-if="$page.props.flash?.success" class="mb-6 rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                {{ $page.props.flash.success }}
            </div>

            <!-- Verificaciones (confianza) -->
            <section class="bg-white rounded-2xl border border-gray-100 p-6 mb-6">
                <h2 class="text-lg font-bold text-gray-800 mb-1">Verificaciones</h2>
                <p class="text-xs text-gray-500 mb-5">
                    El correo y el celular son necesarios para usar la app. El documento de identidad es opcional y refuerza tu perfil con el badge ✓ Identidad.
                </p>

                <ul class="space-y-4">
                    <li
                        v-for="item in verificationItems"
                        :key="item.key"
                        class="flex items-start gap-4 p-4 rounded-xl border"
                        :class="item.done ? 'border-green-100 bg-green-50/50' : 'border-gray-100'"
                    >
                        <span class="text-xl shrink-0">{{ item.done ? '✅' : item.pending ? '⏳' : '○' }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-sm text-gray-900">{{ item.title }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ item.description }}</p>
                        </div>
                        <Link
                            v-if="item.action"
                            :href="item.action"
                            class="shrink-0 text-xs font-semibold text-accent hover:text-accent-hover"
                        >
                            {{ item.actionLabel }}
                        </Link>
                    </li>
                </ul>

                <IdentityVerificationNotice
                    v-if="! trust.identity_verified"
                    compact
                    class="mt-5"
                />

                <div v-if="hasAnyBadge" class="mt-5 pt-5 border-t border-gray-100">
                    <p class="text-xs text-gray-500 mb-2">Así te ven otros usuarios:</p>
                    <VerificationBadges
                        :email="trust.email_verified"
                        :phone="trust.phone_verified"
                        :identity="trust.identity_verified"
                    />
                </div>
            </section>

            <!-- Reputación (reseñas) -->
            <section class="bg-white rounded-2xl border border-gray-100 p-6 mb-6">
                <h2 class="text-lg font-bold text-gray-800 mb-1">Reputación</h2>
                <p class="text-xs text-gray-500 mb-5">Promedio de estrellas que te dejan compradores y vendedores tras transacciones completadas.</p>

                <div class="flex items-center gap-4">
                    <div class="text-center px-4">
                        <p class="text-3xl font-bold text-yellow-500">
                            {{ trust.ratings_count > 0 ? trust.rating_avg.toFixed(1) : '—' }}
                        </p>
                        <RatingStars v-if="trust.ratings_count > 0" :rating="trust.rating_avg" class="justify-center mt-1" />
                        <p class="text-xs text-gray-400 mt-1">{{ trust.ratings_count }} reseñas</p>
                    </div>
                    <div class="flex-1 space-y-2 text-sm border-l border-gray-100 pl-4">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Ventas completadas</span>
                            <span class="font-medium">{{ trust.sales_count }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Compras completadas</span>
                            <span class="font-medium">{{ trust.purchases_count }}</span>
                        </div>
                    </div>
                </div>
            </section>

            <Link
                :href="`/u/${$page.props.auth.user.username}`"
                class="block text-center text-sm text-accent hover:underline"
            >
                Ver mi perfil público →
            </Link>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import IdentityVerificationNotice from '@/Components/IdentityVerificationNotice.vue';
import RatingStars from '@/Components/RatingStars.vue';
import VerificationBadges from '@/Components/VerificationBadges.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    trust: Object,
});

const hasAnyBadge = computed(() =>
    props.trust.email_verified
    || props.trust.phone_verified
    || props.trust.identity_verified
);

const verificationItems = computed(() => [
    {
        key: 'email',
        title: 'Correo electrónico',
        description: 'Confirma que controlas tu email.',
        done: props.trust.email_verified,
        pending: false,
        action: props.trust.email_verified ? null : '/email/verify',
        actionLabel: 'Verificar',
    },
    {
        key: 'phone',
        title: 'Número de celular',
        description: 'Necesario para publicar, comprar y chatear.',
        done: props.trust.phone_verified,
        pending: false,
        action: props.trust.phone_verified ? null : '/telefono/verificar',
        actionLabel: 'Verificar',
    },
    {
        key: 'identity',
        title: 'Documento de identidad',
        description: props.trust.identity_status === 'pending'
            ? 'Tu documento está en revisión por el equipo de Mi Ropa.'
            : 'Verificación opcional: sube frente y reverso de tu documento para obtener el badge ✓ Identidad.',
        done: props.trust.identity_verified,
        pending: props.trust.identity_status === 'pending',
        action: props.trust.identity_verified || props.trust.identity_status === 'pending'
            ? null
            : '/cuenta/verificar-identidad',
        actionLabel: 'Subir documento',
    },
]);
</script>
