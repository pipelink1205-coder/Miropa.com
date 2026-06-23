<template>
    <Link
        :href="`/moda/universos/${universe.slug}`"
        class="group/universe relative rounded-full border px-4 py-2 text-sm font-medium transition hover:-translate-y-0.5 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent/40"
        :style="{ borderColor: universe.accent_color + '40', color: universe.accent_color }"
        :title="universe.description || undefined"
        :aria-describedby="universe.description ? tooltipId : undefined"
    >
        {{ universe.name }}

        <span
            v-if="universe.description"
            :id="tooltipId"
            role="tooltip"
            class="pointer-events-none absolute bottom-[calc(100%+0.5rem)] left-1/2 z-30 w-56 -translate-x-1/2 rounded-lg bg-ink px-3 py-2 text-left text-xs font-normal leading-snug text-white opacity-0 shadow-lg transition duration-150 group-hover/universe:opacity-100 group-focus-visible/universe:opacity-100"
        >
            {{ universe.description }}
            <span
                class="absolute left-1/2 top-full -translate-x-1/2 border-4 border-transparent border-t-ink"
                aria-hidden="true"
            />
        </span>
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    universe: {
        type: Object,
        required: true,
    },
});

const tooltipId = computed(() => `universe-tip-${props.universe.slug}`);
</script>
