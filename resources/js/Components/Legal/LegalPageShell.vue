<template>
    <AppLayout :title="title">
        <div class="container-app max-w-3xl py-10 md:py-12">
            <nav class="mb-8 flex flex-wrap gap-x-4 gap-y-2 border-b border-zinc-200 pb-4 text-sm">
                <Link
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    class="transition"
                    :class="current === item.key ? 'font-semibold text-accent' : 'text-ink-secondary hover:text-accent'"
                >
                    {{ item.label }}
                </Link>
            </nav>

            <header>
                <h1 class="text-3xl font-bold text-ink">{{ title }}</h1>
                <p v-if="subtitle" class="mt-2 text-ink-secondary">{{ subtitle }}</p>
                <p v-if="updated" class="mt-2 text-sm text-ink-muted">Última actualización: {{ updated }}</p>
            </header>

            <div class="mt-8 space-y-6 text-sm leading-relaxed text-ink-secondary">
                <slot />
            </div>

            <footer class="mt-12 rounded-xl border border-zinc-200 bg-surface-muted p-5 text-xs text-ink-muted">
                <p class="font-medium text-ink-secondary">¿Necesitas ayuda?</p>
                <p class="mt-1">
                    Soporte: <a href="mailto:soporte@miropa.com" class="text-accent hover:underline">soporte@miropa.com</a>
                    · Privacidad: <a href="mailto:privacidad@miropa.com" class="text-accent hover:underline">privacidad@miropa.com</a>
                </p>
            </footer>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: '' },
    updated: { type: String, default: '' },
    current: { type: String, required: true },
});

const navItems = [
    { key: 'how', href: '/como-funciona', label: 'Cómo funciona' },
    { key: 'terms', href: '/terminos', label: 'Términos de uso' },
    { key: 'privacy', href: '/privacidad', label: 'Privacidad' },
];
</script>
