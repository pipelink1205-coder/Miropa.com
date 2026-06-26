<template>
    <span
        v-if="show"
        class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide"
        :class="compact ? 'bg-violet-100 text-violet-800' : 'bg-violet-50 text-violet-700 ring-1 ring-violet-200'"
    >
        {{ compact ? 'Trueque' : 'Acepta trueque' }}
    </span>
</template>

<script setup>
defineProps({
    show: { type: Boolean, default: true },
    compact: { type: Boolean, default: false },
});
</script>
