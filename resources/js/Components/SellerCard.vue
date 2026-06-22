<template>
    <Link
        :href="seller.profile_url"
        class="group flex flex-col items-center rounded-[var(--radius-card)] bg-surface-raised p-6 text-center shadow-[var(--shadow-soft)] transition hover:-translate-y-1 hover:shadow-[var(--shadow-card)]"
    >
        <img
            v-if="seller.avatar_path"
            :src="seller.avatar_path"
            :alt="seller.name"
            class="h-20 w-20 rounded-full object-cover ring-2 ring-zinc-100 transition group-hover:ring-accent/30"
        />
        <div
            v-else
            class="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-accent to-accent-hover text-2xl font-bold text-white ring-2 ring-zinc-100"
            aria-hidden="true"
        >
            {{ seller.name.charAt(0).toUpperCase() }}
        </div>

        <p class="mt-4 text-base font-semibold text-ink group-hover:text-accent">{{ seller.name }}</p>
        <p class="mt-1 text-sm text-ink-muted">@{{ seller.username }}</p>

        <VerificationBadges
            v-if="hasBadges"
            class="mt-3 justify-center"
            :email="seller.trust?.email_verified"
            :phone="seller.trust?.phone_verified"
            :identity="seller.trust?.identity_verified"
            compact
        />

        <div class="mt-4 flex items-center gap-3 text-xs text-ink-secondary">
            <span>{{ seller.active_listings_count }} anuncio{{ seller.active_listings_count === 1 ? '' : 's' }}</span>
            <span v-if="seller.trust?.ratings_count > 0" class="flex items-center gap-1">
                <svg class="h-3.5 w-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                {{ seller.trust.rating_avg.toFixed(1) }}
            </span>
        </div>
    </Link>
</template>

<script setup>
import VerificationBadges from '@/Components/VerificationBadges.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    seller: { type: Object, required: true },
});

const hasBadges = computed(() =>
    props.seller.trust?.email_verified
    || props.seller.trust?.phone_verified
    || props.seller.trust?.identity_verified,
);
</script>
