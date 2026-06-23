<template>
    <span
        class="inline-flex items-center rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide"
        :class="badgeClass"
    >
        {{ label }}
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    saleMode: { type: String, default: 'marketplace' },
    compact: { type: Boolean, default: false },
});

const isClassified = computed(() => props.saleMode === 'classified');

const label = computed(() =>
    isClassified.value
        ? (props.compact ? 'Solo contacto' : 'Solo contacto')
        : (props.compact ? 'Compra Mi Ropa' : 'Compra protegida Mi Ropa'),
);

const badgeClass = computed(() =>
    isClassified.value
        ? 'bg-amber-100 text-amber-900'
        : 'bg-trust-soft text-trust',
);
</script>
