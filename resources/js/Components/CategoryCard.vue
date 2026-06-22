<template>
    <Link
        :href="href"
        class="group relative block h-[220px] overflow-hidden rounded-[var(--radius-card)] shadow-[var(--shadow-soft)] transition duration-400 hover:-translate-y-2 hover:shadow-[var(--shadow-elevated)] focus-visible:outline-offset-4 sm:h-[260px]"
    >
        <img
            :src="image"
            :alt="`Categoría ${name}`"
            class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
            loading="lazy"
        />
        <div
            class="absolute inset-0 flex items-end bg-gradient-to-t from-ink/90 via-ink/30 to-transparent p-5 text-white"
            aria-hidden="true"
        >
            <div>
                <h3 class="text-lg font-bold sm:text-xl">{{ name }}</h3>
                <p class="mt-0.5 text-sm text-zinc-300 opacity-0 transition group-hover:opacity-100">
                    Explorar →
                </p>
            </div>
        </div>
        <span class="sr-only">Explorar categoría {{ name }}</span>
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    name: { type: String, required: true },
    image: { type: String, required: true },
    href: { type: String, required: true },
});
</script>
