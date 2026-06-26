<template>
    <Link v-if="listing" :href="`/anuncios/${listing.slug}`" class="flex items-center gap-3 group">
        <div class="h-14 w-14 shrink-0 overflow-hidden rounded-lg bg-gray-100">
            <img
                v-if="listing.primary_image"
                :src="listing.primary_image"
                :alt="listing.title"
                class="h-full w-full object-cover"
            />
            <div v-else class="flex h-full w-full items-center justify-center text-gray-300">📷</div>
        </div>
        <div class="min-w-0">
            <p class="truncate text-sm font-medium text-gray-900 group-hover:text-accent">{{ listing.title }}</p>
            <p class="text-xs text-gray-500">{{ listing.price_formatted }}</p>
        </div>
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    listing: { type: Object, default: null },
});
</script>
