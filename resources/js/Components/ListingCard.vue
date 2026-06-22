<template>
    <Link
        :href="`/anuncios/${listing.slug}`"
        class="group block overflow-hidden rounded-[var(--radius-card)] bg-surface-raised shadow-[var(--shadow-soft)] transition hover:-translate-y-0.5 hover:shadow-[var(--shadow-card)]"
        :class="{ 'ring-1 ring-accent/20': featured }"
    >
        <!-- Imagen -->
        <div class="relative aspect-[4/3] overflow-hidden bg-surface-muted">
            <img
                v-if="imageUrl"
                :src="imageUrl"
                :alt="listing.title"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                loading="lazy"
            />
            <div v-else class="flex h-full w-full items-center justify-center text-ink-muted">
                <svg class="h-12 w-12 opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>

            <span
                v-if="featured"
                class="absolute left-3 top-3 rounded-full bg-accent px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide text-white"
            >
                Destacado
            </span>

            <span
                v-if="listing.status !== 'active'"
                class="absolute left-3 top-3 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide"
                :class="statusBadge"
            >
                {{ statusLabel }}
            </span>

            <button
                v-if="$page.props.auth?.user"
                type="button"
                :aria-label="listing.is_favorited ? 'Quitar de favoritos' : 'Agregar a favoritos'"
                @click.prevent="$emit('toggleFavorite', listing.id)"
                class="absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-full bg-white/95 shadow-md backdrop-blur-sm transition hover:scale-110"
            >
                <svg
                    class="h-5 w-5 transition"
                    :class="listing.is_favorited ? 'fill-red-500 text-red-500' : 'fill-none text-ink-secondary'"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                    aria-hidden="true"
                >
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
            </button>
        </div>

        <!-- Info -->
        <div class="p-4">
            <p class="truncate text-sm font-semibold text-ink group-hover:text-accent">{{ listing.title }}</p>
            <p class="mt-1 text-base font-bold text-accent">{{ listing.price_formatted }}</p>
            <div class="mt-2 flex items-center justify-between gap-2 text-xs text-ink-muted">
                <span class="truncate">{{ listing.location?.city ?? 'Colombia' }}</span>
                <time v-if="listing.published_at" :datetime="listing.published_at">{{ publishedLabel }}</time>
            </div>
        </div>
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    listing: { type: Object, required: true },
    featured: { type: Boolean, default: false },
});

defineEmits(['toggleFavorite']);

const imageUrl = computed(() => {
    if (typeof props.listing.primary_image === 'string') {
        return props.listing.primary_image;
    }

    return props.listing.primary_image?.url
        ?? props.listing.images?.[0]?.url
        ?? null;
});

const publishedLabel = computed(() => {
    const raw = props.listing.published_at;
    if (! raw) {
        return '';
    }

    const date = new Date(raw);
    const diffDays = Math.floor((Date.now() - date.getTime()) / 86400000);

    if (diffDays === 0) {
        return 'Hoy';
    }
    if (diffDays === 1) {
        return 'Ayer';
    }
    if (diffDays < 7) {
        return `Hace ${diffDays} días`;
    }

    return date.toLocaleDateString('es-CO', { day: 'numeric', month: 'short' });
});

const statusBadge = computed(() => ({
    'bg-amber-100 text-amber-800': props.listing.status === 'reserved',
    'bg-emerald-100 text-emerald-800': props.listing.status === 'sold',
    'bg-zinc-100 text-zinc-600': props.listing.status === 'paused',
}));

const statusLabel = computed(() => ({
    reserved: 'Reservado',
    sold: 'Vendido',
    paused: 'Pausado',
}[props.listing.status] ?? ''));
</script>
