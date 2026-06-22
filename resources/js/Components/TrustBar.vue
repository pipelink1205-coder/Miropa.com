<template>
    <section class="border-y border-zinc-200/80 bg-surface-raised" aria-label="Señales de confianza">
        <div class="container-app py-6">
            <ul class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <li
                    v-for="item in items"
                    :key="item.title"
                    class="flex items-start gap-4"
                >
                    <div
                        class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl"
                        :class="item.iconBg"
                        aria-hidden="true"
                    >
                        <component :is="item.icon" class="h-5 w-5" :class="item.iconColor" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-ink">{{ item.title }}</p>
                        <p class="mt-0.5 text-sm text-ink-secondary">{{ item.description }}</p>
                    </div>
                </li>
            </ul>
        </div>
    </section>
</template>

<script setup>
import { h } from 'vue';

const ShieldIcon = (_, { attrs }) => h('svg', { ...attrs, fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' }),
]);

const ChatIcon = (_, { attrs }) => h('svg', { ...attrs, fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z' }),
]);

const StarIcon = (_, { attrs }) => h('svg', { ...attrs, fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z' }),
]);

const TagIcon = (_, { attrs }) => h('svg', { ...attrs, fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
    h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z' }),
]);

const items = [
    {
        title: 'Vendedores verificados',
        description: 'Email, celular e identidad confirmados.',
        icon: ShieldIcon,
        iconBg: 'bg-trust-soft',
        iconColor: 'text-trust',
    },
    {
        title: 'Chat directo',
        description: 'Negocia con el vendedor sin salir de la app.',
        icon: ChatIcon,
        iconBg: 'bg-accent-soft',
        iconColor: 'text-accent',
    },
    {
        title: 'Reseñas reales',
        description: 'Reputación basada en compras completadas.',
        icon: StarIcon,
        iconBg: 'bg-amber-50',
        iconColor: 'text-amber-600',
    },
    {
        title: 'Precios claros',
        description: 'Publicaciones con precio visible y condición.',
        icon: TagIcon,
        iconBg: 'bg-zinc-100',
        iconColor: 'text-ink-secondary',
    },
];
</script>
