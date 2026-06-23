<template>
    <AppLayout :title="universe.name">
        <div class="container-app py-8">
            <nav class="mb-4 text-sm text-ink-secondary">
                <ol class="flex flex-wrap items-center gap-1.5">
                    <li v-for="(crumb, i) in breadcrumbs" :key="i" class="flex items-center gap-1.5">
                        <span v-if="i > 0">›</span>
                        <Link v-if="crumb.href" :href="crumb.href" class="hover:text-accent">{{ crumb.label }}</Link>
                        <span v-else class="font-medium text-ink">{{ crumb.label }}</span>
                    </li>
                </ol>
            </nav>

            <div class="mb-8">
                <span
                    class="inline-block rounded-full px-3 py-1 text-xs font-semibold text-white"
                    :style="{ backgroundColor: universe.accent_color }"
                >
                    Universo
                </span>
                <h1 class="mt-2 text-3xl font-bold text-ink">{{ universe.name }}</h1>
                <p v-if="universe.description" class="mt-2 max-w-2xl text-ink-secondary">{{ universe.description }}</p>
            </div>

            <div v-if="listings.data.length === 0" class="py-16 text-center text-ink-secondary">
                Aún no hay piezas en este universo.
            </div>
            <div v-else class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
                <ListingCard v-for="listing in listings.data" :key="listing.id" :listing="listing" />
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ListingCard from '@/Components/ListingCard.vue';
import { Link } from '@inertiajs/vue3';

defineProps({
    universe: Object,
    listings: Object,
    filters: Object,
    breadcrumbs: Array,
});
</script>
