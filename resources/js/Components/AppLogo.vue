<template>
    <Link :href="href" class="inline-flex shrink-0 items-center gap-2 transition opacity-100 hover:opacity-90">
        <img
            v-if="brand.logo_url"
            :src="brand.logo_url"
            :alt="brand.name"
            class="h-8 w-auto md:h-9"
        />
        <span v-else class="leading-none">
            <span class="text-xl font-extrabold tracking-tight text-ink md:text-2xl">{{ brand.name }}</span><span class="text-sm font-semibold text-accent md:text-base">.com</span>
        </span>
    </Link>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps({
    href: {
        type: String,
        default: '/',
    },
});

const brand = computed(() => usePage().props.brand ?? {
    name: 'Mi Ropa',
    domain: 'miropa.com',
    logo_url: null,
});
</script>
