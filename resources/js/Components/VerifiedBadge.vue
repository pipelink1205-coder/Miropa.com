<template>
    <span
        v-if="isVerified"
        :title="label"
        class="inline-flex items-center gap-1 text-xs font-medium text-blue-700 bg-blue-50 px-2 py-0.5 rounded-full"
    >
        ✓ {{ label }}
    </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    level: { type: String, default: 'none' },
});

const isVerified = computed(() => props.level !== 'none');

const label = computed(() => ({
    email:       'Email verificado',
    phone:       'Teléfono verificado',
    id_document: 'Identidad verificada',
}[props.level] ?? ''));
</script>
