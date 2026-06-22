<template>
    <div class="space-y-1.5">
        <VerificationBadges
            v-if="hasAnyBadge"
            :email="trust.email_verified"
            :phone="trust.phone_verified"
            :identity="trust.identity_verified"
            :compact="compact"
        />
        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500">
            <div v-if="trust.ratings_count > 0" class="flex items-center gap-1">
                <RatingStars :rating="trust.rating_avg" />
                <span>{{ trust.rating_avg.toFixed(1) }} ({{ trust.ratings_count }})</span>
            </div>
            <span v-else-if="showEmptyRating" class="text-gray-400">Sin reseñas aún</span>
            <span v-if="trust.sales_count > 0">{{ trust.sales_count }} {{ trust.sales_count === 1 ? 'venta' : 'ventas' }}</span>
        </div>
    </div>
</template>

<script setup>
import RatingStars from '@/Components/RatingStars.vue';
import VerificationBadges from '@/Components/VerificationBadges.vue';
import { computed } from 'vue';

const props = defineProps({
    trust: {
        type: Object,
        required: true,
    },
    compact: { type: Boolean, default: false },
    showEmptyRating: { type: Boolean, default: true },
});

const hasAnyBadge = computed(() =>
    props.trust.email_verified
    || props.trust.phone_verified
    || props.trust.identity_verified
);
</script>
