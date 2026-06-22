<template>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between" :class="className">
        <div class="max-w-2xl">
            <p v-if="eyebrow" class="mb-2 text-sm font-semibold uppercase tracking-wider text-accent">
                {{ eyebrow }}
            </p>
            <h2 class="text-section-title" :id="headingId">
                {{ title }}
            </h2>
            <p v-if="subtitle" class="mt-2 text-base text-ink-secondary md:text-lg">
                {{ subtitle }}
            </p>
        </div>
        <Link
            v-if="href"
            :href="href"
            class="inline-flex shrink-0 items-center gap-1.5 text-sm font-semibold text-accent transition hover:text-accent-hover"
        >
            {{ linkLabel }}
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
            </svg>
        </Link>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    title: { type: String, required: true },
    headingId: { type: String, default: '' },
    subtitle: { type: String, default: '' },
    eyebrow: { type: String, default: '' },
    href: { type: String, default: '' },
    linkLabel: { type: String, default: 'Ver todo' },
    className: { type: String, default: '' },
});
</script>
